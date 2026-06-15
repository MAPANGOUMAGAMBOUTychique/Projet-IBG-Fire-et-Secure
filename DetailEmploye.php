<?php
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

$employe = null;
$message_erreur = "";

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_employe = intval($_GET['id']);
    
    try {
        $bdd = Database::getInstance();
        
        // Extraction de toutes les colonnes de l'employé ciblé
        $stmt = $bdd->prepare("SELECT * FROM Employe WHERE Id_Employe = ?");
        $stmt->execute([$id_employe]);
        $employe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$employe) {
            $message_erreur = "Aucun employé ne correspond à cet identifiant.";
        }
    } catch (PDOException $e) {
        $message_erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
    }
} else {
    $message_erreur = "Identifiant de l'employé manquant ou incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
    <title>Détails Employé | Espace Admin</title>
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
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="Administrateur.php">Accueil Admin</a></li> 
                <li><a href="Statistique.php">Statistiques</a></li> 
                <li><a href="Entreprises.php">Entreprises</a></li> 
                <li><a href="Employers.php">Employés</a></li> 
                <li><a href="Services.php">Services</a></li>
                <li><a href="Missions.php">Missions</a></li>   
            </ul>
        </nav>
    </header>

    <main style="padding: 20px;">
        <h1 style="text-align:center;">Fiche Individuelle de l'Employé</h1>

        <?php if (!empty($message_erreur)): ?>
            <p style="color:red; text-align:center; background:#f8d7da; padding:15px; border:1px solid #f5c6cb; max-width:600px; margin:20px auto; border-radius:4px;">
                ⚠️ <?= htmlspecialchars($message_erreur) ?>
            </p>
            <div style="text-align:center;">
                <a href="Employers.php" class="btn-back">Retour à la liste</a>
            </div>
        <?php endif; ?>

        <?php if ($employe): ?>
            <div class="detail-box">
                <h1 style="color: #333; margin-bottom: 20px;">👤 <?= htmlspecialchars($employe['Nom_Employe'] . ' ' . $employe['Prenom_Employe']) ?></h1>
                
                <div class="detail-section">
                    <h2>Identité & Profil</h2>
                    <div class="grid-info">
                        <div class="info-label">ID Employé :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Id_Employe']) ?></div>

                        <div class="info-label">Nom :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Nom_Employe']) ?></div>
                        
                        <div class="info-label">Prénom :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Prenom_Employe']) ?></div>

                        <div class="info-label">Date de naissance :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Date_Naissance_Employe'] ?? 'Non renseignée') ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Coordonnées de Contact</h2>
                    <div class="grid-info">
                        <div class="info-label">Téléphone Personnel :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Telephone_Employe'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Adresse E-mail :</div>
                        <div class="info-value">
                            <a href="mailto:<?= htmlspecialchars($employe['Email_Employe'] ?? '') ?>">
                                <?= htmlspecialchars($employe['Email_Employe'] ?? 'Non renseigné') ?>
                            </a>
                        </div>

                        <div class="info-label">Adresse Postale :</div>
                        <div class="info-value">
                            <?= htmlspecialchars($employe['Numero_Voie_Employe'] ?? '') ?> 
                            <?= htmlspecialchars($employe['Nom_Voie_Employe'] ?? '') ?><br>
                            <?= htmlspecialchars($employe['Code_Postal_Employe'] ?? '') ?> 
                            <?= htmlspecialchars($employe['Ville_Employe'] ?? '') ?>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Données Contractuelles</h2>
                    <div class="grid-info">
                        <div class="info-label">Diplôme / Qualification :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Diplome_Employe'] ?? 'Non spécifié') ?></div>

                        <div class="info-label">Date d'embauche / Inscription :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Date_Inscription_Employe'] ?? 'Non renseignée') ?></div>
                    </div>
                </div>

                <div style="text-align: space-between; display: flex; justify-content: space-between;">
                    <a href="Employers.php" class="btn-back">⬅️ Retour à la liste</a>
                    <a href="Deconnexion.php" class="btn-back" style="background-color: #c90000;">Se déconnecter</a>
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