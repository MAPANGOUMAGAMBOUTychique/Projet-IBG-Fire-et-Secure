<?php
// =========================================================================
// 1. INITIALISATION, CONFIGURATION ET DROITS D'ACCÈS
// =========================================================================

// Vérifie si une session est déjà ouverte sur le serveur.
// Si ce n'est pas le cas (PHP_SESSION_NONE), on démarre la session avec session_start().
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe Database pour permettre la communication avec la base de données (via PDO).
require_once 'Database.php';

// FORCE LE FUSEAU HORAIRE EN EUROPE/PARIS
// Indispensable pour s'assurer que le calcul de l'heure d'expiration du token (+1h) 
// correspond parfaitement à l'heure du serveur et de l'utilisateur en France.
date_default_timezone_set('Europe/Paris');

// Initialisation des variables de notification à blanc.
$message = ""; // Stockera les messages de succès
$erreur = "";  // Stockera les messages d'erreur


// =========================================================================
// 2. TRAITEMENT DU FORMULAIRE DE RÉCUPÉRATION (REQUÊTE POST)
// =========================================================================

// On déclenche le traitement uniquement si le formulaire a été soumis en méthode POST et que le champ email existe.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    
    // trim() nettoie la chaîne en enlevant les espaces inutiles au début et à la fin (ex: " user@test.fr " devient "user@test.fr").
    $email = trim($_POST['email']);
    
    // filter_var avec FILTER_VALIDATE_EMAIL vérifie de manière stricte si le format textuel respecte bien la structure d'une adresse email (présence du @, du domaine, etc.).
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Adresse e-mail invalide.";
    } else {
        try {
            // Récupération de la connexion PDO unique (Pattern Singleton).
            $bdd = Database::getInstance();
            
            // ÉTAPE 2.1 : On vérifie si l'e-mail saisi existe dans la table globale "Utilisateur".
            $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(); // Contient les données de l'utilisateur ou 'false' si rien n'est trouvé.
            
            // ÉTAPE 2.2 : ADAPTATION LOGIQUE MÉTIER (Cas particulier des entreprises)
            // Si l'e-mail n'a pas été trouvé dans la table "Utilisateur", il s'agit peut-être d'une entreprise qui utilise son adresse de contact.
            if (!$user) {
                $stmt_ent = $bdd->prepare("SELECT Email_Contact_Entreprise FROM Entreprise WHERE Email_Contact_Entreprise = ?");
                $stmt_ent->execute([$email]);
                $entreprise = $stmt_ent->fetch();
                
                // Si l'e-mail correspond bien à une entreprise enregistrée :
                if ($entreprise) {
                    // On fait une double vérification par sécurité pour voir s'il y a un compte Utilisateur lié à cet e-mail.
                    $stmt_check = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                    $stmt_check->execute([$email]);
                    $user = $stmt_check->fetch();
                    
                    // CAS DE SECOURS : Si l'entreprise existe mais qu'aucun compte "Utilisateur" ne lui avait été associé,
                    // on crée automatiquement la ligne manquante dans la table "Utilisateur" avec le rôle 'entreprise'.
                    if (!$user) {
                        $stmt_ins = $bdd->prepare("INSERT INTO Utilisateur (Email_Utilisateur, Nom_Utilisateur, Role, Mot_De_Passe_Utilisateur) VALUES (?, ?, 'entreprise', 'TEMPORAIRE')");
                        $stmt_ins->execute([$email, 'Entreprise']);
                        
                        // On récupère immédiatement les données de ce nouvel utilisateur fraîchement créé.
                        $stmt_refe = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                        $stmt_refe->execute([$email]);
                        $user = $stmt_refe->fetch();
                    }
                }
            }
            
            // ÉTAPE 2.3 : GÉNÉRATION DU TOKEN DE SÉCURITÉ
            // Si un utilisateur (classique ou entreprise) a été identifié :
            if ($user) {
                
                /*
                  SÉCURITÉ CRYPTOGRAPHIQUE :
                  random_bytes(32) génère 32 octets de données aléatoires hautement sécurisées (impossible à deviner).
                  bin2hex() convertit ces octets en une chaîne de caractères hexadécimale de 64 caractères (lettres et chiffres).
                  Ce "token" servira de clé d'accès unique temporaire.
                */
                $token = bin2hex(random_bytes(32));
                
                // DATE D'EXPIRATION : On prend la date/heure actuelle et on y ajoute 1 heure (+1 hour).
                // Format SQL standard : Année-Mois-Jour Heure:Minute:Seconde.
                $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // ENREGISTREMENT EN BASE : On stocke le token et sa date limite directement sur la ligne de l'utilisateur.
                $stmt = $bdd->prepare("UPDATE Utilisateur SET reset_token = ?, reset_expires = ? WHERE Email_Utilisateur = ?");
                $stmt->execute([$token, $expiration, $user['Email_Utilisateur']]);
                
                // CONSTITUTION DU LIEN UNIQUE : On passe le token généré dans l'URL en paramètre GET (?token=...).
                $lien = "http://localhost/StageTychique/SiteIbgFireEtSecure/ReinitialiserMotDePasse.php?token=" . $token;
                
                // ÉTAPE 2.4 : ENVOI DE L'E-MAIL DE RÉCUPÉRATION
                $sujet = "Réinitialisation de votre mot de passe - IBG FIRE ET SECURE";
                $message_mail = "Bonjour,\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous (valable 1h) :\n" . $lien;
                
                // En-têtes du mail pour spécifier l'expéditeur et forcer l'encodage en UTF-8 (gestion correcte des accents).
                $headers = "From: no-reply@ibgfire.com\r\nContent-Type: text/plain; charset=UTF-8";
                
                // La fonction native mail() tente d'envoyer le courriel.
                if (mail($user['Email_Utilisateur'], $sujet, $message_mail, $headers)) {
                    $message = "Un lien de réinitialisation vous a été envoyé par e-mail.";
                } else {
                    /*
                      ASTUCE / ASTUCE DE DÉVELOPPEMENT (Localhost) :
                      Sur un serveur local (WAMP, XAMPP, MAMP), la fonction mail() échoue souvent car aucun serveur SMTP n'est configuré.
                      Pour éviter d'être bloqué pendant le stage ou la démonstration, le code génère un lien cliquable directement à l'écran en cas d'échec d'envoi.
                    */
                    $message = "Lien généré ! <a href='$lien' style='color:#c90000; font-weight:bold;'>Cliquez ici pour réinitialiser votre mot de passe</a>";
                }
            } else {
                /*
                  RÈGLE DE SÉCURITÉ CRUCIALE (Contre l'énumération d'emails) :
                  Si l'e-mail n'existe pas en base de données, on affiche EXACTEMENT le même message de succès que s'il existait.
                  Pourquoi ? Pour empêcher un pirate de tester des e-mails au hasard pour savoir qui possède un compte sur notre site.
                */
                $message = "Un lien de réinitialisation vous a été envoyé si cet e-mail existe.";
            }
        } catch (PDOException $e) {
            // Capture des erreurs liées à la base de données pour ne pas faire planter la page.
            $erreur = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Mot de passe oublié | IBG FIRE ET SECURE</title>
</head>
<body>
    <header>
        <!-- Logo de l'entreprise pointant vers la page d'accueil principale -->
        <a href="index.php">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="NosServices.php">Nos services</a></li>
                <li><a href="NousContacter.php">Nous contacter</a></li> 
                <li><a href="SeConnecter.php">Se connecter</a></li> 
                <li><a href="CreerUnCompte.php">Créer un compte</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <!-- Section centrale contenant le formulaire, stylisée avec des ombres pour un rendu moderne (Card design) -->
        <section style="max-width: 500px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="text-align: center; margin-bottom: 20px;">Mot de passe oublié</h2>
            <p style="text-align: center; margin-bottom: 20px; font-size: 0.95em; color: #555;">Entrez votre e-mail pour récupérer votre accès.</p>
            
            <!-- 
              AFFICHAGE DYNAMIQUE DU MESSAGE DE SUCCÈS :
              Si la variable $message n'est pas vide, on affiche un bloc vert. 
              Note : On n'utilise pas htmlspecialchars($message) ici car le message de secours contient une balise de lien HTML <a> qu'il faut interpréter.
            -->
            <?php if (!empty($message)): ?>
                <div style="padding: 10px; background: #e6f4ea; color: #137333; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; border: 1px solid #c2e7cb;">
                    <strong>✔️ Succès :</strong> <?= $message ?>
                </div>
            <?php endif; ?>
            
            <!-- 
              AFFICHAGE DYNAMIQUE DU MESSAGE D'ERREUR :
              Si une erreur survient (format e-mail ou erreur PDO), on injecte un bloc rouge sécurisé par htmlspecialchars().
            -->
            <?php if (!empty($erreur)): ?>
                <div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; border: 1px solid #f5c6cb;">
                    <strong>❌ Erreur :</strong> <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <!-- Formulaire de saisie de l'adresse e-mail -->
            <!-- L'attribut action="" (vide) signifie que le formulaire soumet les données sur cette même page -->
            <form action="" method="POST" class="formulaire">
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="email_reset">Votre adresse e-mail</label>
                    <!-- type="email" force une pré-vérification native par le navigateur avant même l'envoi au serveur -->
                    <input type="email" name="email" id="email_reset" required>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="width: 100%;">
                        Envoyer le lien
                    </button>
                </div>
            </form>
        </section>
    </main>

    <footer>
        <ul>
            <li><a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
            <li>
                <article>
                    <h4>Siège social IBG FIRE ET SECURE</h4>
                    <p>24 allée de la mer d'iroise 44600 Saint-Nazaire</p>
                </article>
            </li>
            <li>
                <article>
                    <h4>Nos Services</h4>
                    <ul>
                        <li><a href="#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
                        <li><a href="SeConnecter.php">Se connecter</a></li>
                        <li><a href="CreerUnCompte.php">Créer un compte</a></li>
                    </ul>
                </nav> 
            </li>
        </ul>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>