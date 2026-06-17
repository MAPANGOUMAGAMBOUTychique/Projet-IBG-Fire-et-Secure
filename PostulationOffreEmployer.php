<?php
// =========================================================================
// 1. INITIALISATION DE LA SESSION ET VÉRIFICATION D'ACCÈS
// =========================================================================

// On démarre le moteur de session s'il n'est pas déjà actif.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe de gestion de base de données.
require_once 'Database.php';

// Redirection immédiate si le visiteur n'est pas authentifié.
// Sécurise la page contre les accès anonymes directs.
if (!isset($_SESSION['user_id'])) {
    header("Location: SeConnecter.php");
    exit();
}

// =========================================================================
// 2. CONFIGURATION ET PARAMÉTRAGE GLOBALE
// =========================================================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Centralisation de l'adresse racine du site web.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Récupération sécurisée du paramètre ID de la mission depuis l'URL (requête GET).
// Si l'identifiant est absent ou vide, le script coupe court et renvoie à l'accueil.
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php"); 
    exit();
}

// Force la conversion en entier de l'ID récupéré pour éviter toute injection de scripts par l'URL.
$id_mission = intval($_GET['id']);
$bdd = Database::getInstance();

$mission_trouvee = false;
$details_mission = null;

// =========================================================================
// 3. EXTRACTION DES DONNÉES DE LA MISSION (REQUÊTE AVEC JOINTURES MULTIPLES)
// =========================================================================
try {
    /* Cette requête lie 3 tables (Mission, Service, Entreprise) afin de reconstituer 
       la fiche complète de l'offre d'emploi avec le nom de l'entreprise cliente et le pôle concerné.
    */
    $stmt = $bdd->prepare("
        SELECT 
            m.Titre_Mission, 
            m.Description_Mission, 
            s.Nom_Service, 
            s.Description_Service,
            e.Nom_Entreprise
        FROM Mission m
        INNER JOIN Service s ON m.Id_Service = s.Id_Service
        INNER JOIN Entreprise e ON m.Id_Entreprise = e.Id_Entreprise
        WHERE m.Id_Mission = ?
    ");
    $stmt->execute([$id_mission]);
    $details_mission = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si la base renvoie une ligne valide, on confirme l'affichage de la mission.
    if ($details_mission) {
        $mission_trouvee = true;
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération des détails : " . $e->getMessage();
}

// =========================================================================
// 4. TRAITEMENT DU FORMULAIRE DE CANDIDATURE (RÉCEPTION DE FICHIERS POST)
// =========================================================================
$candidature_envoyee = false;
$erreur_candidature = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification stricte que le CV et la Lettre de Motivation ont bien été transmis sans erreur système.
    if (isset($_FILES['user_cv']) && $_FILES['user_cv']['error'] === UPLOAD_ERR_OK &&
        isset($_FILES['user_lettre_de_motivation']) && $_FILES['user_lettre_de_motivation']['error'] === UPLOAD_ERR_OK) {
        
        try {
            /* ARCHITECTURE DE LIEN : 
               Dans votre base, un compte utilisateur n'est pas directement une fiche employé.
               On interroge la table pivot 'Incarner' pour identifier à quel 'Id_Employe' correspond le compte connecté.
            */
            $stmt_incarner = $bdd->prepare("SELECT Id_Employe FROM Incarner WHERE Id_Utilisateur = ? LIMIT 1");
            $stmt_incarner->execute([$_SESSION['user_id']]);
            $lien = $stmt_incarner->fetch(PDO::FETCH_ASSOC);

            if (!$lien) {
                throw new Exception("Votre compte utilisateur n'est pas encore relié à une fiche employé. Contactez l'administrateur.");
            }

            $id_employe = $lien['Id_Employe'];

            // Définition des chemins de stockage pour les fichiers PDF envoyés.
            $dossier_upload_physique = __DIR__ . "/uploads/candidatures/"; // Chemin absolu serveur pour l'écriture disque.
            $dossier_upload_bdd = "uploads/candidatures/";                 // Chemin relatif propre pour enregistrement en BDD.

            // Création automatique du dossier sur le serveur si celui-ci n'existe pas encore.
            if (!is_dir($dossier_upload_physique)) {
                mkdir($dossier_upload_physique, 0777, true);
            }

            /* SÉCURISATION DES NOMS DE FICHIERS : 
               On ajoute le timestamp actuel (time()) devant le nom d'origine du fichier. 
               Cela évite qu'un utilisateur n'écrase le CV d'un autre candidat s'ils renomment leurs documents de la même manière (ex: "cv.pdf").
            */
            $nom_cv = time() . "_" . basename($_FILES['user_cv']['name']);
            $nom_lm = time() . "_" . basename($_FILES['user_lettre_de_motivation']['name']);

            $chemin_physique_cv = $dossier_upload_physique . $nom_cv;
            $chemin_physique_lm = $dossier_upload_physique . $nom_lm;

            // Déplacement des fichiers depuis la zone temporaire du serveur vers le dossier final de stockage.
            if (move_uploaded_file($_FILES['user_cv']['tmp_name'], $chemin_physique_cv) && 
                move_uploaded_file($_FILES['user_lettre_de_motivation']['tmp_name'], $chemin_physique_lm)) {

                $chemin_bdd_cv = $dossier_upload_bdd . $nom_cv;
                $chemin_bdd_lm = $dossier_upload_bdd . $nom_lm;

                // Insertion de la candidature dans la table d'association 'Postuler'.
                $stmt_cand = $bdd->prepare("
                    INSERT INTO Postuler (Id_Employe, Id_Mission, Statut_Postuler, Chemin_CV, Chemin_Lettre_Motivation) 
                    VALUES (?, ?, 'en attente', ?, ?)
                ");
                $stmt_cand->execute([$id_employe, $id_mission, $chemin_bdd_cv, $chemin_bdd_lm]);

                $candidature_envoyee = true;
            } else {
                $erreur_candidature = "Erreur lors du déplacement de vos fichiers sur le serveur.";
            }

        } catch (Exception $e) {
            $erreur_candidature = "Erreur lors de l'envoi de la candidature : " . $e->getMessage();
        }
    } else {
        $erreur_candidature = "Une erreur est survenue lors du téléversement de vos documents (Fichiers trop lourds ou corrompus).";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mission_trouvee ? htmlspecialchars($details_mission['Titre_Mission']) : 'Offre' ?> | IBG FIRE ET SECURE</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/CompteEmployer.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/PostulationOffre.css">
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li> 
                <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li> 
                <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <?php if (!$mission_trouvee): ?>
            <p style="text-align:center; margin-top:50px; color:red; font-weight:bold;">L'offre demandée n'existe pas ou a été supprimée.</p>
        <?php else: ?>

            <h1><?= htmlspecialchars($details_mission['Titre_Mission']) ?></h1>

            <?php if (!empty($erreur_candidature)): ?>
                <div style="background-color: #f8d7da; color: #721c24; padding: 15px; margin: 20px auto; max-width: 1200px; border-radius: 5px; text-align: center; font-weight: bold; border: 1px solid #f5c6cb;">
                    <?= htmlspecialchars($erreur_candidature) ?>
                </div>
            <?php endif; ?>

            <?php if ($candidature_envoyee): ?>
                <section class="reponse" style="display: block; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 30px; border-radius: 8px; margin: 30px auto; max-width: 600px; text-align: center;">
                    <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="LOGO IBG FIRE ET SECURE" style="max-width: 1200px; width: 120px; margin-bottom: 15px;">
                    <p style="font-size: 1.3em; font-weight: bold; margin: 0 0 10px 0;">Votre candidature a été envoyée avec succès !</p>
                    <p style="font-size: 0.95em; color: #155724; margin: 0;">Redirection vers votre espace dans quelques instants...</p>
                </section>
                
                <script>
                    setTimeout(function() {
                        window.location.href = "<?= BASE_URL ?>/index.php"; 
                    }, 3000);
                </script>
            <?php endif; ?>

            <?php if (!$candidature_envoyee): ?>
                <section class="container">
                    <section class="colonne-gauche">
                        <h2>Émetteur : <?= htmlspecialchars($details_mission['Nom_Entreprise']) ?></h2>
                        
                        <h3>Description de la mission</h3>
                        <p><?= nl2br(htmlspecialchars($details_mission['Description_Mission'])) ?></p>

                        <h3 style="margin-top:20px; color:#d32f2f;">Cadre de l'intervention (<?= htmlspecialchars($details_mission['Nom_Service']) ?>)</h3>
                        <p style="font-style: italic; background: #f9f9f9; padding: 15px; border-left: 4px solid #d32f2f; line-height: 1.6;">
                            <?= htmlspecialchars($details_mission['Description_Service']) ?>
                        </p>
                    </section>

                    <section class="colonne-droite">
                        <div class="box-competences">
                            <h2>Compétences Requises</h2>
                            <ul>
                                <li>Sérieux & Vigilance</li>
                                <li>Respect des protocoles</li>
                                <li>Savoir-être et diplomatie</li>
                            </ul>
                        </div>

                        <section id="Document_candidature">
                            <h2>Documents importants</h2>
                            <form action="" method="post" enctype="multipart/form-data">
                                <div>
                                    <label for="cv">CV (Format PDF) :</label>
                                    <input type="file" name="user_cv" id="cv" accept=".pdf" required>
                                </div>
                                <div>
                                    <label for="Lettre_de_motivation">Lettre de motivation (Format PDF) :</label>
                                    <input type="file" name="user_lettre_de_motivation" id="Lettre_de_motivation" accept=".pdf" required>
                                </div>
                                <button type="submit" class="btn-submit">Envoyer ma candidature</button>
                            </form>
                        </section>
                    </section>
                </section>
            <?php endif; ?>

        <?php endif; ?>
    </main>

    <footer>
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/index.php">
                    <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
                </a>
            </li>
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
                        <li><a href="<?= BASE_URL ?>/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="<?= BASE_URL ?>/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php">Nos Services</a></li>
                        <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                            <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
                        <?php endif; ?>
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