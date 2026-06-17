<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Vérification de sécurité Admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

$entreprise = null;
$message_erreur = "";

// Vérification de la présence d'un ID valide dans l'URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_entreprise = intval($_GET['id']);
    
    try {
        $bdd = Database::getInstance();
        
        // Sélection de l'ensemble des colonnes de votre table Entreprise
        $stmt = $bdd->prepare("SELECT * FROM Entreprise WHERE Id_Entreprise = ?");
        $stmt->execute([$id_entreprise]);
        $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$entreprise) {
            $message_erreur = "Aucune entreprise ne correspond à cet identifiant.";
        }
    } catch (PDOException $e) {
        $message_erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
    }
} else {
    $message_erreur = "Identifiant d'entreprise manquant ou incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Administrateur.css">
    <title>Détails Entreprise | Espace Admin</title>
    <style>
        .detail-box {
            background: #fff;
            max-width: 700px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .detail-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .detail-section h2 {
            color: #c90000; /* Rouge charte sécurité */
            font-size: 18px;
            margin-bottom: 10px;
        }
        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #111;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-back:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/Administrateur.php">Accueil Admin</a></li> 
                <li><a href="<?= BASE_URL ?>/Statistique.php">Statistiques</a></li> 
                <li><a href="<?= BASE_URL ?>/Entreprises.php">Entreprises</a></li> 
                <li><a href="<?= BASE_URL ?>/Employers.php">Employés</a></li> 
                <li><a href="<?= BASE_URL ?>/Services.php">Services</a></li>
                <li><a href="<?= BASE_URL ?>/Missions.php">Missions</a></li>   
            </ul>
        </nav>
    </header>

    <main style="padding: 20px;">
        <h1 style="text-align:center;">Fiche Détail de l'Entreprise</h1>

        <?php if (!empty($message_erreur)): ?>
            <p style="color:red; text-align:center; background:#f8d7da; padding:15px; border:1px solid #f5c6cb; max-width:600px; margin:20px auto; border-radius:4px;">
                ⚠️ <?= htmlspecialchars($message_erreur) ?>
            </p>
            <div style="text-align:center;">
                <a href="<?= BASE_URL ?>/Entreprises.php" class="btn-back">Retour à la liste</a>
            </div>
        <?php endif; ?>

        <?php if ($entreprise): ?>
            <div class="detail-box">
                <h1 style="color: #333; margin-bottom: 20px;">🏢 <?= htmlspecialchars($entreprise['Nom_Entreprise']) ?></h1>
                
                <div class="detail-section">
                    <h2>Informations Légales</h2>
                    <div class="grid-info">
                        <div class="info-label">Numéro SIRET :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Siret_Entreprise'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Code NAF / APE :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Code_NAF_Entreprise'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Numéro de TVA Intracommunautaire :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Numero_TVA_Entreprise'] ?? 'Non renseigné') ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Coordonnées de l'Établissement</h2>
                    <div class="grid-info">
                        <div class="info-label">Téléphone :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Telephone_Entreprise'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Adresse Complète :</div>
                        <div class="info-value">
                            <?= htmlspecialchars($entreprise['Numero_voie_Entreprise'] ?? '') ?> 
                            <?= htmlspecialchars($entreprise['Nom_Voie_Entreprise'] ?? '') ?><br>
                            <?= htmlspecialchars($entreprise['Complement_'] ?? '') ?> 
                            <?= htmlspecialchars($entreprise['Ville_Entreprise'] ?? '') ?><br>
                            <strong><?= htmlspecialchars($entreprise['Pays_Entreprise'] ?? '') ?></strong>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Référent & Contact Principal</h2>
                    <div class="grid-info">
                        <div class="info-label">Nom du Référent :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Nom_Referent_Entreprise'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Fonction du Référent :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Fonction_Referent_Entreprise'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">E-mail de contact direct :</div>
                        <div class="info-value">
                            <a href="mailto:<?= htmlspecialchars($entreprise['Email_Contact_Entreprise']) ?>">
                                <?= htmlspecialchars($entreprise['Email_Contact_Entreprise'] ?? 'Non renseigné') ?>
                            </a>
                        </div>
                        
                        <div class="info-label">Date d'inscription :</div>
                        <div class="info-value"><?= htmlspecialchars($entreprise['Date_Creation_Inscription_Entreprise'] ?? 'Non renseigné') ?></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <a href="<?= BASE_URL ?>/Entreprises.php" class="btn-back">⬅️ Retour à la liste</a>
                    <a href="<?= BASE_URL ?>/Deconnexion.php" class="btn-back" style="background-color: #c90000;">Se déconnecter</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>