<?php
// --- CONFIGURATION SÉCURISÉE DES COOKIES DE SESSION ---

// Définition des paramètres du cookie de session avant le démarrage pour limiter les risques de vol de session (XSS / CSRF)
session_set_cookie_params([
    'lifetime' => 0,          // Le cookie expire dès la fermeture du navigateur
    'path' => '/',            // Disponible sur tout le site
    'domain' => 'localhost',
    'secure' => false,        // Passer à 'true' en production avec un certificat SSL (HTTPS)
    'httponly' => true,       // Bloque l'accès au cookie via JavaScript (réduit le risque de vol par faille XSS)
    'samesite' => 'Strict'    // Empêche l'envoi du cookie lors de requêtes cross-site (protection CSRF forte)
]);

// Démarrage de la session système
session_start();

// Inclusion de la classe Singleton d'accès à la base de données
require_once 'Database.php';

// Vérification de l'authentification de l'utilisateur
if (!isset($_SESSION['user_id'])) {
    // Si la session n'existe pas, redirection immédiate vers la page de connexion
    header("Location: SeConnecter.php");
    exit();
}

// Initialisation des objets globaux et des variables de contrôle des messages d'erreur
$bdd = Database::getInstance();
$id_utilisateur = $_SESSION['user_id'];

$message_erreur_suppression = "";
$erreur_global = "";


// --- TRAITEMENT DU FORMULAIRE : MODIFICATION DU PROFIL (POST) ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
    try {
        // Nettoyage des espaces blancs en début et fin de chaîne
        $nom_saisi    = trim($_POST['user_name']);
        $prenom_saisi = trim($_POST['user_prenom']);
        $mdp_saisi    = trim($_POST['user_mot_de_passe_entreprise']);

        // 1. Mise à jour des informations spécifiques dans la table 'Employe'
        $stmt_up_emp = $bdd->prepare("UPDATE Employe SET Nom_Employe = ?, Prenom_Employe = ? WHERE Id_Employe = ?");
        $stmt_up_emp->execute([$nom_saisi, $prenom_saisi, $id_utilisateur]);

        // 2. Mise à jour des informations d'identification générales dans la table 'Utilisateur'
        if (!empty($mdp_saisi)) {
            // Si un nouveau mot de passe est saisi, on le hache via l'algorithme sécurisé BCRYPT
            $mdp_hache = password_hash($mdp_saisi, PASSWORD_BCRYPT);
            $stmt_up_user = $bdd->prepare("UPDATE Utilisateur SET Nom_Utilisateur = ?, Mot_De_Passe_Utilisateur = ? WHERE Id_Utilisateur = ?");
            $stmt_up_user->execute([$nom_saisi, $mdp_hache, $id_utilisateur]);
        } else {
            // Si le mot de passe est vide, on met à jour uniquement le nom
            $stmt_up_user = $bdd->prepare("UPDATE Utilisateur SET Nom_Utilisateur = ? WHERE Id_Utilisateur = ?");
            $stmt_up_user->execute([$nom_saisi, $id_utilisateur]);
        }

        // Redirection vers la page elle-même (méthode PRG) pour vider le tableau $_POST et éviter le rechargement intempestif
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $erreur_global = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}


// --- TRAITEMENT DU FORMULAIRE : SUPPRESSION DU COMPTE ---

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression'])) {
    try {
        // A. Suppression de la ligne associée dans la table dépendante 'Employe'
        $stmt_del_emp = $bdd->prepare("DELETE FROM Employe WHERE Id_Employe = ?");
        $stmt_del_emp->execute([$id_utilisateur]);

        // B. Suppression de l'entité d'authentification principale dans la table 'Utilisateur'
        $stmt_del_user = $bdd->prepare("DELETE FROM Utilisateur WHERE Id_Utilisateur = ?");
        $stmt_del_user->execute([$id_utilisateur]);

        // C. Nettoyage et destruction complète de la session de l'utilisateur
        session_unset();
        session_destroy();

        // Redirection finale vers l'accueil avec un paramètre de succès explicite
        header("Location: index.php?statut=compte_supprime");
        exit();

    } catch (PDOException $e) {
        $message_erreur_suppression = "Erreur lors de la suppression du compte : " . $e->getMessage();
    }
}


// --- LECTURE ET RÉCUPÉRATION DES INFORMATIONS DE L'EMPLOYÉ ---

$nom_complet = "Employé";
$nom = "";
$prenom = "";
$email = "";

try {
    // Récupération des informations d'authentification et de contact
    $stmt = $bdd->prepare("SELECT Nom_Utilisateur, Email_Utilisateur FROM Utilisateur WHERE Id_Utilisateur = ?");
    $stmt->execute([$id_utilisateur]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['Email_Utilisateur'];

        // Récupération des informations d'identité civile dans la table Employé
        $stmt_emp = $bdd->prepare("SELECT Nom_Employe, Prenom_Employe FROM Employe WHERE Id_Employe = ?");
        $stmt_emp->execute([$id_utilisateur]);
        $employe = $stmt_emp->fetch(PDO::FETCH_ASSOC);

        if ($employe) {
            $nom = $employe['Nom_Employe'];
            $prenom = $employe['Prenom_Employe'];
            $nom_complet = $prenom . " " . $nom; // Formatage de l'affichage
        } else {
            $nom = $user['Nom_Utilisateur'];
            $nom_complet = $nom;
        }
    }
} catch (PDOException $e) {
    $erreur_global = "Erreur : " . $e->getMessage();
}


// --- RECUPÉRATION DES MISSIONS DISPONIBLES ET COMPATIBLES ---

$missions = [];
try {
    // Sélection des détails des missions couplée à une sous-requête corrélée 
    // permettant de savoir en temps réel (0 ou 1) si l'employé connecté a déjà postulé à chaque offre.
    $stmt_missions = $bdd->prepare("
        SELECT m.*, s.Nom_Service, e.Nom_Entreprise,
               (SELECT COUNT(*) FROM Postuler p WHERE p.Id_Mission = m.Id_Mission AND p.Id_Employe = ?) as deja_postule
        FROM Mission m
        LEFT JOIN Service s ON m.Id_Service = s.Id_Service
        JOIN Entreprise e ON m.Id_Entreprise = e.Id_Entreprise
        WHERE m.Statut_Mission = 'disponible'
        ORDER BY m.Date_Creation_Mission DESC
    ");
    $stmt_missions->execute([$id_utilisateur]);
    $missions = $stmt_missions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erreur_global = "Erreur missions : " . $e->getMessage();
}


// --- RÉCUPÉRATION DES POSTULATIONS DE L'EMPLOYÉ ---

$mes_postulations = [];
try {
    // Récupère l'historique des candidatures de l'employé avec le libellé de la mission et son état d'avancement
    $stmt_mes_postulations = $bdd->prepare("
        SELECT p.*, m.Titre_Mission
        FROM Postuler p
        JOIN Mission m ON p.Id_Mission = m.Id_Mission
        WHERE p.Id_Employe = ?
        ORDER BY p.Date_Postuler DESC
    ");
    $stmt_mes_postulations->execute([$id_utilisateur]);
    $mes_postulations = $stmt_mes_postulations->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mes_postulations = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Employé | Site IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/CompteEmployer.css">
    <style>
        /* Masquage initial des éléments interactifs gérés par JavaScript (Confirmation et Succès) */
        #Modification_compte,
        #Message_Succes,
        #Zone_Confirmation_Suppression {
            display: none;
        }

        .reponse, .oui-non {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="NousContacter.php">Nous contacter</a></li>
                <li><a href="Deconnexion.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1><?= htmlspecialchars($nom_complet) ?></h1>

        <?php if (!empty($erreur_global)): ?>
            <p style="color: red; text-align:center;"><?= htmlspecialchars($erreur_global) ?></p>
        <?php endif; ?>

        <section id="Compte_entreprise">
            <form action="" method="post" class="formulaire" id="formulaire_employe">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" name="user_name" id="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Votre nom" required disabled>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="user_prenom" id="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Votre prénom" required disabled>
                </div>
                <div class="form-group">
                    <label for="Email_entreprise">E-mail :</label>
                    <input type="email" name="user_email_entreprise" id="Email_entreprise" value="<?= htmlspecialchars($email) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de passe (laisser vide si inchangé) :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" placeholder="••••••••" disabled>
                </div>

                <div class="conteneur_bouton">
                    <button type="button" class="btn-submit" id="boutton_modifier">Modifier</button>
                    <button type="button" class="btn-submit" id="boutton_enregistrer" style="display: none;">Enregistrer</button>
                </div>
            </form>
        </section>

        <section id="Modification_compte">
            <div class="reponse">
                <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE">
                <p id="confirmation_modification">Voulez-vous vraiment modifier vos informations ?</p>
            </div>
            <div class="oui-non">
                <button type="button" class="btn-solliciter" id="btn_oui">Oui</button>
                <button type="button" class="btn-solliciter" id="btn_non">Non</button>
            </div>
        </section>

        <div id="Message_Succes" class="reponse">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo IBG FIRE ET SECURE">
            <p class="logo_text">Vos informations ont été modifiées avec succès !</p>
        </div>

        <?php if (!empty($mes_postulations)): ?>
        <section id="MesPostulations">
            <h2>Mes candidatures aux offres</h2>
            <?php foreach ($mes_postulations as $mp): ?>
                <?php
                    // Attribution dynamique des teintes de messages (code couleur Bootstrap alertes standard) en fonction du statut
                    $statut = $mp['Statut_Postuler'];
                    if ($statut === 'Accepté') {
                        $couleur = '#d4edda'; $texte_couleur = '#155724';
                        $message = "✅ Félicitations ! Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" a été acceptée.";
                    } elseif ($statut === 'Refusé') {
                        $couleur = '#f8d7da'; $texte_couleur = '#721c24';
                        $message = "❌ Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" a été refusée.";
                    } else {
                        $couleur = '#fff3cd'; $texte_couleur = '#856404';
                        $message = "⏳ Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" est en attente de réponse.";
                    }
                ?>
                <div style="background:<?= $couleur ?>; color:<?= $texte_couleur ?>; padding:12px 18px; margin-bottom:10px; border-radius:5px; font-weight:bold;">
                    <?= $message ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <section id="services">
            <h2>Missions disponibles</h2>
         
            <?php if (empty($missions)): ?>
                <p style="text-align:center; color:#888; margin-top:20px;">
                    Aucune mission disponible pour le moment. Revenez bientôt !
                </p>
            <?php else: ?>
                <ul class="service-grid">
                    <?php foreach ($missions as $mission): ?>
                        <li>
                            <article style="position:relative;">
                                
                                <?php if ($mission['deja_postule'] > 0): ?>
                                    <span style="
                                        position:absolute; top:10px; right:10px;
                                        background:#28a745; color:white;
                                        padding:3px 10px; border-radius:20px;
                                        font-size:0.78em; font-weight:bold;
                                    ">✓ Déjà postulé</span>
                                <?php endif; ?>
         
                                <h4><?= htmlspecialchars($mission['Titre_Mission']) ?></h4>
         
                                <p style="font-size:0.85em; color:#e67e22; font-weight:bold; margin-bottom:6px;">
                                    <?= htmlspecialchars($mission['Nom_Service'] ?? 'Service Général') ?> · <?= htmlspecialchars($mission['Nom_Entreprise']) ?>
                                </p>
         
                                <p><?= nl2br(htmlspecialchars($mission['Description_Mission'] ?? '')) ?></p>
         
                                <p style="font-size:0.8em; color:#999; margin-top:8px;">
                                    Publiée le <?= htmlspecialchars(date('d/m/Y', strtotime($mission['Date_Creation_Mission']))) ?>
                                </p>
         
                                <?php if ($mission['deja_postule'] > 0): ?>
                                    <span style="
                                        display:inline-block; padding:10px 20px;
                                        background:#ccc; color:#555;
                                        border-radius:4px; font-size:0.9em;
                                        margin-top:10px;
                                    ">Candidature envoyée</span>
                                <?php else: ?>
                                    <a href="PostulationOffreEmployer.php?id=<?= $mission['Id_Mission'] ?>"
                                       style="
                                        display:inline-block; padding:10px 20px;
                                        background:#1a1a2e; color:white;
                                        text-decoration:none; border-radius:4px;
                                        font-weight:bold; margin-top:10px;
                                        transition:background 0.2s;
                                       "
                                       onmouseover="this.style.background='#e67e22'"
                                       onmouseout="this.style.background='#1a1a2e'">
                                        Postuler
                                    </a>
                                <?php endif; ?>
         
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </section>

        <section id="Supression_compte_employer">
            <button type="button" class="btn-suprimer" id="btn_supprimer_compte">Supprimer le compte</button>

            <?php if (!empty($message_erreur_suppression)): ?>
                <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($message_erreur_suppression) ?></p>
            <?php endif; ?>

            <div id="Zone_Confirmation_Suppression">
                <div class="reponse" style="display: block; margin-bottom: 15px;">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo IBG FIRE ET SECURE">
                    <p class="logo_text" style="font-weight: bold; color: #d9534f;">Voulez-vous vraiment supprimer définitivement votre compte employé ?</p>
                </div>

                <form action="" method="post">
                    <div class="oui-non">
                        <button type="submit" name="confirmer_suppression" class="btn-solliciter" style="background: #d9534f; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Oui</button>
                        <button type="button" class="btn-solliciter" id="btn_annuler_suppression" style="background: #666; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Non</button>
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
                        <li><a href="Postuler.php">Je postule</a></li>
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ciblage des différents nœuds et sélecteurs du DOM requis
        const btnModifier = document.getElementById("boutton_modifier");
        const btnEnregistrer = document.getElementById("boutton_enregistrer");
        const sectionConfirmation = document.getElementById("Modification_compte");
        const messageSucces = document.getElementById("Message_Succes");
        const btnOui = document.getElementById("btn_oui");
        const btnNon = document.getElementById("btn_non");
        const form = document.getElementById("formulaire_employe");
        const champs = form.querySelectorAll("input");

        // Action de passage en mode édition : Débloque les champs de saisie
        btnModifier.addEventListener("click", function() {
            champs.forEach(champ => champ.removeAttribute("disabled"));
            btnModifier.style.display = "none";
            btnEnregistrer.style.display = "inline-block";
        });

        // Action au clic sur Enregistrer : Fait apparaître le choix modal Oui/Non
        btnEnregistrer.addEventListener("click", function() {
            sectionConfirmation.style.display = "block";
            messageSucces.style.display = "none";
        });

        // Validation finale côté client : Simule le succès visuel, puis soumet le formulaire après 1,5s
        btnOui.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            messageSucces.style.display = "block";

            setTimeout(() => {
                form.submit(); // Envoi des données au serveur en méthode POST
            }, 1500);
        });

        // Annulation de l'édition : reverrouille les champs et réinitialise la page
        btnNon.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            champs.forEach(champ => champ.setAttribute("disabled", "true"));
            btnModifier.style.display = "inline-block";
            btnEnregistrer.style.display = "none";
            window.location.reload(); // Rechargement propre de la page pour écraser les modifications non sauvées
        });

        // Gestion de la boîte de dialogue de suppression de compte
        const btnSupprimer = document.getElementById("btn_supprimer_compte");
        const zoneSuppression = document.getElementById("Zone_Confirmation_Suppression");
        const btnAnnulerSuppression = document.getElementById("btn_annuler_suppression");

        btnSupprimer.addEventListener("click", function() {
            zoneSuppression.style.display = "block";
        });

        btnAnnulerSuppression.addEventListener("click", function() {
            zoneSuppression.style.display = "none";
        });
    });
    </script>
</body>
</html>