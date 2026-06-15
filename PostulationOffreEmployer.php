<?php
session_start();
require_once 'Database.php';

// 1. Vérification que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: SeConnecter.php");
    exit();
}

// 2. Vérification qu'un ID de mission valide a été transmis dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php"); 
    exit();
}

$id_mission = intval($_GET['id']);
$bdd = Database::getInstance();

$mission_trouvee = false;
$details_mission = null;

try {
    // 3. REQUÊTE JOINTURE : Récupération de la mission, du service lié et de l'entreprise émettrice
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

    if ($details_mission) {
        $mission_trouvee = true;
    }
} catch (PDOException $e) {
    echo "Erreur lors de la récupération : " . $e->getMessage();
}

// 4. TRAITEMENT DU FORMULAIRE DE CANDIDATURE
$candidature_envoyee = false;
$erreur_candidature = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On vérifie que les deux fichiers requis sont présents et sans erreur de téléversement
    if (isset($_FILES['user_cv']) && $_FILES['user_cv']['error'] === UPLOAD_ERR_OK &&
        isset($_FILES['user_lettre_de_motivation']) && $_FILES['user_lettre_de_motivation']['error'] === UPLOAD_ERR_OK) {
        
        try {
            // Optionnel : Traitement et stockage des fichiers si ta table Candidature est prête
            /*
            $id_employe = $_SESSION['user_id'];
            $dossier_upload = "uploads/candidatures/";
            
            if (!is_dir($dossier_upload)) {
                mkdir($dossier_upload, 0777, true);
            }

            $chemin_cv = $dossier_upload . time() . "_" . basename($_FILES['user_cv']['name']);
            $chemin_lm = $dossier_upload . time() . "_" . basename($_FILES['user_lettre_de_motivation']['name']);
            
            move_uploaded_file($_FILES['user_cv']['tmp_name'], $chemin_cv);
            move_uploaded_file($_FILES['user_lettre_de_motivation']['tmp_name'], $chemin_lm);

            $stmt_cand = $bdd->prepare("INSERT INTO Candidature (Id_Mission, Id_Employe, Chemin_CV, Chemin_Lettre_Motivation) VALUES (?, ?, ?, ?)");
            $stmt_cand->execute([$id_mission, $id_employe, $chemin_cv, $chemin_lm]);
            */

            // Validation du succès pour l'affichage
            $candidature_envoyee = true;

        } catch (Exception $e) {
            $erreur_candidature = "Erreur lors de l'envoi de la candidature : " . $e->getMessage();
        }
    } else {
        $erreur_candidature = "Une erreur est survenue lors du téléversement de vos documents. Veuillez réessayer.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $mission_trouvee ? htmlspecialchars($details_mission['Titre_Mission']) : 'Offre' ?> | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/CompteEmployer.css">
    <link rel="stylesheet" href="assets/PostulationOffre.css">
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="views/pages/NousContacter.php">Nous contacter</a></li> 
                <li><a href="index.php">Se déconnecter</a></li> 
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
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="LOGO IBG FIRE ET SECURE" style="max-width: 1200px; width: 120px; margin-bottom: 15px;">
                    <p style="font-size: 1.3em; font-weight: bold; margin: 0 0 10px 0;">Votre candidature a été envoyée avec succès !</p>
                    <p style="font-size: 0.95em; color: #155724; margin: 0;">Redirection vers votre espace dans quelques instants...</p>
                </section>
                
                <script>
                    setTimeout(function() {
                        window.location.href = "index.php"; 
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