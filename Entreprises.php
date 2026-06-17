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

// Vérification stricte : l'utilisateur doit être connecté et avoir le rôle 'admin'
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 2. RÉCUPÉRATION DES ENTREPRISES ET DU NOMBRE DE SERVICES VIA LA TABLE SOLLICITER
$entreprises = [];

try {
    $bdd = Database::getInstance();
    
    // Requête SQL utilisant la table pivot 'Solliciter'
    $sql = "SELECT e.Id_Entreprise, e.Nom_Entreprise, COUNT(s.Id_Entreprise) AS nb_services 
            FROM Entreprise e
            LEFT JOIN Solliciter s ON e.Id_Entreprise = s.Id_Entreprise
            GROUP BY e.Id_Entreprise, e.Nom_Entreprise
            ORDER BY e.Nom_Entreprise ASC";
            
    $stmt = $bdd->query($sql);
    $entreprises = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
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
    <title>Entreprises | Espace Administrateur</title>
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
        <h1 class="main-title">Comptes Entreprises</h1>

        <?php if (isset($erreur_sql)): ?>
            <p style="color:red; text-align:center; background:#fff; padding:10px; border:1px solid red; max-width:600px; margin:20px auto;">
                ⚠️ <?= htmlspecialchars($erreur_sql) ?>
            </p>
        <?php endif; ?>

        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Nom de l'Entreprise (Cliquer pour voir les détails)</th> 
                        <th class="header-count">Nombre de services<br>sollicités</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($entreprises)): ?>
                        <?php foreach ($entreprises as $entreprise): ?>
                            <tr>
                                <td class="company-name">
                                    <a class="link-detail" href="<?= BASE_URL ?>/DetailEntreprise.php?id=<?= $entreprise['Id_Entreprise'] ?>">
                                        🏢 <?= htmlspecialchars($entreprise['Nom_Entreprise']) ?>
                                    </a>
                                </td>
                                <td class="count"><?= htmlspecialchars($entreprise['nb_services']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: #666;">
                                Aucune entreprise enregistrée dans la base de données.
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
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php"><img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="<?= BASE_URL ?>/NosServices.php#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
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
                        <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                        <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
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