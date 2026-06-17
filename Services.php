<?php
// =========================================================================
// 1. GESTION DE LA SESSION ET SÉCURITÉ D'ACCÈS
// =========================================================================

// Initialisation du mécanisme de session si aucun canal n'est encore ouvert.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion du gestionnaire d'accès à la base de données.
require_once 'Database.php';

// Affichage explicite des erreurs PHP pour simplifier le débuggage en développement.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définition de la racine absolue du site internet.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

/* CONTROLE D'ACCÈS RESTREINT (Rôle Admin) :
   Cette barrière de sécurité empêche un utilisateur classique (employé, entreprise) 
   ou un visiteur anonyme de consulter cette page de gestion interne.
*/
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// =========================================================================
// 2. EXTRACTION AGREGÉE DES SERVICES ET STATISTIQUES DE SOLLICITATION
// =========================================================================
$services = [];

try {
    $bdd = Database::getInstance();
    
    /* CONSTRUCTION DE LA REQUÊTE SQL (Jointure Gauche et Agrégation) :
       - On utilise un 'LEFT JOIN' pour lister *tous* les services, même ceux qui n'ont jamais été demandés par une entreprise.
       - La fonction d'agrégation 'COUNT(so.Id_Service)' calcule le volume de demandes par pôle d'activité.
       - Le 'GROUP BY' structure le résultat de comptage de manière unique par identifiant de service.
    */
    $sql = "SELECT s.Id_Service, s.Nom_Service, COUNT(so.Id_Service) AS nb_sollicitations 
            FROM Service s
            LEFT JOIN Solliciter so ON s.Id_Service = so.Id_Service
            GROUP BY s.Id_Service, s.Nom_Service
            ORDER BY s.Nom_Service ASC";
            
    $stmt = $bdd->query($sql);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Capturation de l'anomalie SQL pour éviter le crash de l'interface et préserver l'expérience utilisateur.
    $erreur_sql = "Erreur de base de données : " . $e->getMessage();
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Entreprises.css">
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
                                    <a class="link-detail" href="<?= BASE_URL ?>/DetailService.php?id=<?= $srv['Id_Service'] ?>">
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
            <a href="<?= BASE_URL ?>/Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>