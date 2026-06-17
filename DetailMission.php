<?php
// 1. GESTION DE LA SESSION ET SÉCURITÉ D'ACCÈS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Sécurité Admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

$mission = null;
$message_erreur = "";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_mission = intval($_GET['id']);
    
    try {
        $bdd = Database::getInstance();
        
        // Extraction basée sur les vraies colonnes de votre table
        $stmt = $bdd->prepare("SELECT * FROM Mission WHERE Id_Mission = ?");
        $stmt->execute([$id_mission]);
        $mission = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$mission) {
            $message_erreur = "Aucune mission ne correspond à cet identifiant.";
        }
    } catch (PDOException $e) {
        $message_erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
    }
} else {
    $message_erreur = "Identifiant de mission manquant ou incorrect.";
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
    <title>Détails Mission | Espace Admin</title>
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
            color: #c90000;
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
        .description-text {
            line-height: 1.6;
            color: #333;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #c90000;
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
        <h1 style="text-align:center;">Fiche de la Mission</h1>

        <?php if (!empty($message_erreur)): ?>
            <p style="color:red; text-align:center; background:#f8d7da; padding:15px; border:1px solid #f5c6cb; max-width:600px; margin:20px auto; border-radius:4px;">
                ⚠️ <?= htmlspecialchars($message_erreur) ?>
            </p>
            <div style="text-align:center;">
                <a href="<?= BASE_URL ?>/Missions.php" class="btn-back">Retour à la liste</a>
            </div>
        <?php endif; ?>

        <?php if ($mission): ?>
            <div class="detail-box">
                <h1 style="color: #333; margin-bottom: 20px;">📋 <?= htmlspecialchars($mission['Titre_Mission']) ?></h1>
                
                <div class="detail-section">
                    <h2>Informations Générales</h2>
                    <div class="grid-info">
                        <div class="info-label">ID Mission :</div>
                        <div class="info-value"><?= htmlspecialchars($mission['Id_Mission']) ?></div>

                        <div class="info-label">Statut actuel :</div>
                        <div class="info-value"><strong><?= htmlspecialchars($mission['Statut_Mission'] ?? 'En attente') ?></strong></div>
                        
                        <div class="info-label">Date de création :</div>
                        <div class="info-value"><?= htmlspecialchars($mission['Date_Creation_Mission'] ?? 'Non spécifiée') ?></div>

                        <div class="info-label">ID du Service lié :</div>
                        <div class="info-value"><?= htmlspecialchars($mission['Id_Service']) ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Description & Détails</h2>
                    <p class="description-text">
                        <?= nl2br(htmlspecialchars($mission['Description_Mission'] ?? 'Aucune description fournie.')) ?>
                    </p>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <a href="<?= BASE_URL ?>/Missions.php" class="btn-back">⬅️ Retour à la liste</a>
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