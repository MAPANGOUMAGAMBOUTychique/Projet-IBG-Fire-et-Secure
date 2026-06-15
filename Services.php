<?php
// 1. GESTION DE LA SESSION ET SÉCURITÉ D'ACCÈS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

// Affichage des erreurs pour le développement local
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Vérification stricte du rôle Admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 2. RÉCUPÉRATION DES SERVICES ET DU NOMBRE DE SOLLICITATIONS
$services = [];

try {
    $bdd = Database::getInstance();
    
    // Jointure entre Service et la table pivot Solliciter
    $sql = "SELECT s.Id_Service, s.Nom_Service, COUNT(so.Id_Service) AS nb_sollicitations 
            FROM Service s
            LEFT JOIN Solliciter so ON s.Id_Service = so.Id_Service
            GROUP BY s.Id_Service, s.Nom_Service
            ORDER BY s.Nom_Service ASC";
            
    $stmt = $bdd->query($sql);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erreur_sql = "Erreur de base de données : " . $e->getMessage();
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
    <link rel="stylesheet" href="assets/Entreprises.css">
    <title>Services | Espace Administrateur</title>
    <style>
        .link-detail {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .link-detail:hover {
            text-decoration: underline;
            color: #0056b3;
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

    <main>
        <h1 class="main-title">Liste des Services</h1>

        <?php if (isset($erreur_sql)): ?>
            <p style="color:red; text-align:center; background:#fff; padding:10px; border:1px solid red; max-width:600px; margin:20px auto;">
                ⚠️ <?= htmlspecialchars($erreur_sql) ?>
            </p>
        <?php endif; ?>

        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Nom du Service (Cliquer pour voir les détails)</th> 
                        <th class="header-count">Nombre de sollicitations<br>entreprises</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $srv): ?>
                            <tr>
                                <td class="company-name">
                                    <a class="link-detail" href="DetailService.php?id=<?= $srv['Id_Service'] ?>">
                                        🛠️ <?= htmlspecialchars($srv['Nom_Service']) ?>
                                    </a>
                                </td>
                                <td class="count"><?= htmlspecialchars($srv['nb_sollicitations']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: #666;">
                                Aucun service enregistré dans la base de données.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-action">
            <a href="Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>