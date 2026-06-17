<?php
// --- CONFIGURATION ET INITIALISATION DE LA SESSION ---

// Vérification et démarrage sécurisé de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe de connexion à la base de données (utilisation du pattern Singleton)
require_once 'Database.php';

// Configuration de l'affichage des erreurs pour faciliter le développement et le débogage
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Correctif technique pour éviter les erreurs d'allocation de mémoire JIT avec certaines versions de PCRE
ini_set('pcre.jit', 0); 

// Définition de la constante de l'URL racine du site
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');


// --- CONTRÔLE D'ACCÈS ET SÉCURITÉ ---

// 1. SÉCURITÉ : On s'assure que l'utilisateur est connecté, possède le rôle 'entreprise' (sans vérifier la casse) et un ID valide
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    strtolower(trim($_SESSION['user_role'])) !== 'entreprise' || 
    !isset($_SESSION['entreprise_id'])) {
    
    // Si l'une des conditions échoue, l'utilisateur est immédiatement renvoyé vers la page de connexion
    header("Location: SeConnecter.php");
    exit();
}

// Récupération de l'instance unique de la base de données PDO
$bdd = Database::getInstance();
$entreprise_id = $_SESSION['entreprise_id'];

// Initialisation des variables de contrôle pour les messages utilisateurs
$changement_reussi = false;
$message_erreur = "";


// --- TRAITEMENT DU FORMULAIRE : MODIFICATION DES INFORMATIONS ---

// 2. TRAITEMENT DE LA MODIFICATION APRÈS LE CLIC SUR "OUI" (Validation du formulaire de mise à jour)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_modification'])) {
    // Nettoyage et sécurisation des données reçues par le formulaire
    $nom = trim($_POST['user_nom_entreprise']);
    $email = filter_var(trim($_POST['user_email_entreprise']), FILTER_SANITIZE_EMAIL); // Supprime les caractères invalides d'un email
    $password = $_POST['user_mot_de_passe_entreprise'];

    // Vérification que les champs obligatoires ne sont pas vides
    if (!empty($nom) && !empty($email)) {
        try {
            // A. Récupération de l'ancien email de l'entreprise afin de pouvoir cibler correctement la table Utilisateur associée
            $stmt_old = $bdd->prepare("SELECT Email_Contact_Entreprise FROM Entreprise WHERE Id_Entreprise = ?");
            $stmt_old->execute([$entreprise_id]);
            $ancien_email = $stmt_old->fetchColumn();

            // B. Mise à jour des informations spécifiques dans la table 'Entreprise'
            $stmt_ent = $bdd->prepare("UPDATE Entreprise SET Nom_Entreprise = ?, Email_Contact_Entreprise = ? WHERE Id_Entreprise = ?");
            $stmt_ent->execute([$nom, $email, $entreprise_id]);

            // C. Mise à jour de la table transverse 'Utilisateur' (Gestion de l'authentification globale)
            if (!empty($password)) {
                // Si un nouveau mot de passe est saisi, on le hache de manière sécurisée (BCRYPT) et on met tout à jour
                $password_hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt_user = $bdd->prepare("UPDATE Utilisateur SET Email_Utilisateur = ?, Nom_Utilisateur = ?, Mot_De_Passe_Utilisateur = ? WHERE Email_Utilisateur = ?");
                $stmt_user->execute([$email, $nom, $password_hash, $ancien_email]);
            } else {
                // Si le mot de passe reste inchangé, on met uniquement à jour l'email et le nom
                $stmt_user = $bdd->prepare("UPDATE Utilisateur SET Email_Utilisateur = ?, Nom_Utilisateur = ? WHERE Email_Utilisateur = ?");
                $stmt_user->execute([$email, $nom, $ancien_email]);
            }

            // D. Synchronisation des informations de la session active de l'utilisateur
            $_SESSION['user_nom'] = $nom;
            $changement_reussi = true;

        } catch (PDOException $e) {
            // Capturation et affichage de l'erreur SQL en cas de problème
            $message_erreur = "Erreur lors de la modification : " . $e->getMessage();
        }
    } else {
        $message_erreur = "Veuillez remplir tous les champs obligatoires.";
    }
}


// --- TRAITEMENT DU FORMULAIRE : SUPPRESSION DU COMPTE ---

// 3. TRAITEMENT DE LA SUPPRESSION RÉELLE DU COMPTE APRÈS LE CLIC SUR "OUI" (Confirmation finale)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression'])) {
    try {
        // A. Récupération de l'email actuel pour nettoyer proprement la table Utilisateur correspondante
        $stmt_email = $bdd->prepare("SELECT Email_Contact_Entreprise FROM Entreprise WHERE Id_Entreprise = ?");
        $stmt_email->execute([$entreprise_id]);
        $email_entreprise = $stmt_email->fetchColumn();

        if ($email_entreprise) {
            // B. Suppression du compte dans la table générique 'Utilisateur'
            $stmt_del_user = $bdd->prepare("DELETE FROM Utilisateur WHERE Email_Utilisateur = ?");
            $stmt_del_user->execute([$email_entreprise]);
        }

        // C. Suppression définitive de l'entité dans la table 'Entreprise'
        $stmt_del_ent = $bdd->prepare("DELETE FROM Entreprise WHERE Id_Entreprise = ?");
        $stmt_del_ent->execute([$entreprise_id]);

        // D. Sécurisation : Destruction complète des variables de session et fermeture de la session
        session_unset();
        session_destroy();
        
        // Redirection vers l'accueil avec un paramètre de succès dans l'URL
        header("Location: index.php?statut=compte_supprime");
        exit();

    } catch (PDOException $e) {
        $message_erreur = "Erreur lors de la suppression du compte : " . $e->getMessage();
    }
}


// --- LECTURE DES DONNÉES DE L'ENTREPRISE POUR L'AFFICHAGE ---

// 4. RÉCUPÉRATION DES DONNÉES ACTUELLES DE L'ENTREPRISE POUR PRÉ-REMPLIR LES CHAMPS DU FORMULAIRE
try {
    $stmt = $bdd->prepare("SELECT * FROM Entreprise WHERE Id_Entreprise = ?");
    $stmt->execute([$entreprise_id]);
    $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Si l'entreprise n'existe plus en base (cas rare ou suppression externe), on déconnecte de force par sécurité
    if (!$entreprise) {
        session_unset();
        session_destroy();
        header("Location: SeConnecter.php");
        exit();
    }
} catch (PDOException $e) {
    $message_erreur = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/CompteEmployer.css">
    <title>Compte entreprise | <?= htmlspecialchars($entreprise['Nom_Entreprise'] ?? 'Mon Espace') ?> | IBG FIRE ET SECURE</title>
    
    <style>
        /* On cache la section de confirmation de modification et de suppression par défaut */
        #Modification_compte, #Zone_Confirmation_Suppression {
            display: none;
            margin-top: 20px;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            text-align: center;
        }

        /* MÉCANISME CSS : Quand l'URL contient l'ancre correspondante (ex: #Modification_compte), la section s'affiche dynamiquement sans JavaScript */
        #Modification_compte:target {
            display: block !important;
        }

        /* Quand on clique sur "Supprimer le compte", l'ancre s'active et affiche la boîte de confirmation */
        #Zone_Confirmation_Suppression:target {
            display: block !important;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="views/pages/NousContacter.php">Nous contacter</a></li> 
                <li><a href="SeConnecter.php">Se déconnecter</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <h1><?= htmlspecialchars($entreprise['Nom_Entreprise'] ?? 'Nom de l\'entreprise') ?></h1>
        
        <?php if (!empty($message_erreur)): ?>
            <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($message_erreur) ?></p>
        <?php endif; ?>

        <form action="" method="post" class="formulaire">

            <section id="Compte_entreprise">
                <div class="form-group">
                    <label for="Nom_entreprise">Nom de l'entreprise</label>
                    <input type="text" name="user_nom_entreprise" id="Nom_entreprise" value="<?= htmlspecialchars($entreprise['Nom_Entreprise'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="Email_entreprise">E-mail de l'entreprise :</label>
                    <input type="email" name="user_email_entreprise" id="Email_entreprise" value="<?= htmlspecialchars($entreprise['Email_Contact_Entreprise'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de passe :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" placeholder="Laisser vide si inchangé">
                </div>

                <a href="#Modification_compte" class="btn-submit" style="display: inline-block; text-align: center; text-decoration: none; line-height: 40px;">Modifier</a>             
            </section>

            <section id="Modification_compte">
                <p>Voulez-vous vraiment modifier vos informations ?</p>
                <button type="submit" name="confirmer_modification" class="btn-submit">Oui</button>
                <a href="#Compte_entreprise" class="btn-submit" style="display: inline-block; text-align: center; text-decoration: none; line-height: 40px; background: #666;">Non</a>
            </section>

        </form>

        <?php if ($changement_reussi): ?>
            <p id="Compte_modifier" style="color: green; text-align: center; font-weight: bold; font-size: 18px; margin-top: 25px; padding: 15px; background: #e6f4ea; border: 1px solid #34a853; border-radius: 4px;">
                ✅ Informations modifiées !
            </p>
            <script>
                window.location.hash = "Compte_modifier";
            </script>
        <?php endif; ?>

        <section id="services">
            <h2>Liste des offres</h2>

            <section id="GardiennageEtSurveillance">
                <h3>Pôle Gardiennage et Surveillance</h3>
                <ul class="service-grid">
                    <li>
                        <article>
                            <h4>Surveillance Statique et contrôle d'Accès</h4>
                            <img src="assets/image/Jour4/Image gardinnage et sécurité-1682125948844-e2dc8996b0f0.avif" alt="Image Surveillace statique et controle">
                            <p>Nos agents de sécurité qualifiés assurent un contrôle rigoureux des flux de personnes, de véhicules et de marchandises. En combinant vigilance humaine et protocoles de vérification stricts, nous garantissons l'étanchéité de vos périmètres.</p>
                            <a href="SolicitationEntreprise.php?service=Surveillance%20Statique%20et%20contrôle%20d'Accès" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                        <article>
                            <h4>Rondes de Surveillance et Sécurité Mobile</h4>
                            <img src="assets/image/Jour4/Image rpnde de surveillance et sécurité mobile-1661499169247-81649e8667d8.avif" alt="Image Surveillance sécurité mobile">
                            <p>Patrouilles mobiles, aléatoires ou programmées, effectuées en véhicule pour inspecter les points sensibles de vos sites. Nos agents assurent une présence dynamique et une surveillance étendue.</p>
                            <a href="SolicitationEntreprise.php?service=Rondes%20de%20Surveillance%20et%20Sécurité%20Mobile" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                       <article>
                            <h4>Protection Événementielle</h4>
                            <img src="assets/image/Jour4/Image protection evenementielle-1760228604788-db8a36d5c1a3.avif" alt="Image Protection Événement">
                            <p>Garantissez le succès et la sérénité de vos événements de haut standing. De la sécurisation des salons internationaux aux rassemblements privés exclusifs.</p>
                            <a href="SolicitationEntreprise.php?service=Protection%20Événementielle" class="btn-solliciter">Solliciter</a>
                       </article>
                    </li>
                </ul>
            </section>
                
            <section id="SecuriteEtIncendie">
                <h3>Pôle Sécurité Incendie</h3>
                <ul class="service-grid">
                    <li>
                        <article>
                            <h4>Prévention et Intervention Incendie (SSIAP)</h4>
                            <img src="assets/image/Jour4/Image prévention et intervention incendie-1482173989-612x612.webp" alt="Image Prévention et intervention incendie">
                            <p>Nos agents certifiés SSIAP (niveaux 1, 2 et 3) assurent une veille constante contre les risques d'incendie et garantissent la conformité de vos installations.</p>
                            <a href="SolicitationEntreprise.php?service=Prévention%20et%20Intervention%20Incendie%20(SSIAP)" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                        <article>
                            <h4>Maintenance des Systèmes de Sécurité Incendie</h4>
                            <img src="assets/image/Jour4/Image maintenance des systèmes incendies-1482775856-612x612.webp" alt="Image Maintenance de sécurité Incendie">
                            <p>Garantissez l'opérationnalité de vos dispositifs de secours. We réalisisons des audits approfondis et la maintenance technique de vos systèmes (SSI).</p>
                            <a href="SolicitationEntreprise.php?service=Maintenance%20des%20Systèmes%20de%20Sécurité%20Incendie" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                        <article>
                            <h4>Formation et Exercices d'Évacuation</h4>
                            <img src="assets/image/Evacuation-1663075966038-6b37cc036924.avif" alt="Image Formation d'ecercice d'evacuation">
                            <p>La préparation est la clé d'une gestion efficace des situations d'urgence. Nous formons vos équipes à adopter les bons réflexes.</p>
                            <a href="SolicitationEntreprise.php?service=Formation%20et%20Exercices%20d'Évacuation" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                </ul>
            </section>

            <section id="ConseilEtExpertise">
                <h3>Pôle Conseil et Expertise</h3>
                <ul class="service-grid">
                    <li>
                        <article>
                            <h4>Audit et Conseil en Ingénierie et Sûreté</h4>
                            <img src="assets/image/Jour4/Image conseil et expertise-1661695279211-dfc3866380d1.avif" alt="Image Audit et conseil ingénieur en sureté">
                            <p>Anticiisez les menaces par une approche analytique de votre sûreté. Nos experts réalisent un audit complet des vulnérabilités de vos infrastructures.</p>
                            <a href="SolicitationEntreprise.php?service=Audit%20et%20Conseil%20en%20Ingénierie%20et%20Sûreté" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                        <article>
                            <h4>Formation à la Gestion des Risques</h4>
                            <img src="assets/image/Jour4/Image gestion des risques-1663089690804-1c6d97412b7a.avif" alt="Image gestion des risques">
                            <p>Développez une véritable culture de la prévention au sein de vos équipes. Nos experts certifiés vous forment aux gestes qui sauvent.</p>
                            <a href="SolicitationEntreprise.php?service=Formation%20à%20la%20Gestion%20des%20Risques" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                    <li>
                        <article>
                            <h4>Sécurisation des Sites Sensibles</h4>
                            <img src="assets/image/Jour4/Image sécurisation des sites sensibles-2248999048-612x612.webp" alt="Image Sécurisation des sites sensibles">
                            <p>Sécurisation des environnements critiques et sites à haut risque : entrepôts de grande valeur, chantiers d'envergure.</p>
                            <a href="SolicitationEntreprise.php?service=Sécurisation%20des%20Sites%20Sensibles" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                </ul>
            </section>
        </section>

        <section id="Supression_compte_entreprise">
            <a href="#Zone_Confirmation_Suppression" class="btn-suprimer" style="display: block; text-align: center; text-decoration: none; line-height: 40px;">Supprimer le compte</a>
            
            <div id="Zone_Confirmation_Suppression">
                <div class="reponse" style="display: block; margin-bottom: 15px;">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo IBG FIRE ET SECURE">
                    <p class="logo_text" style="font-weight: bold; color: black;">Voulez-vous vraiment supprimer définitivement votre compte entreprise ?</p>
                </div>

                <form action="" method="post">
                    <div class="oui-non">
                        <button type="submit" name="confirmer_suppression" class="btn-solliciter" style="background: black; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Oui</button>
                        <a href="#Compte_entreprise" class="btn-solliciter" style="display: inline-block; text-decoration: none; line-height: 20px; background: #666; color: white; padding: 10px 20px; border-radius: 4px;">Non</a>
                    </div>
                </form>
            </div>
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
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
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