<?php
// 1. DÉMARRAGE DE LA SESSION ET SÉCURITÉ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

// Active l'affichage des erreurs pour le développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// 2. CONTRÔLE D'ACCÈS : Strictement réservé à l'administrateur connecté
// On vérifie le rôle enregistré lors de la connexion ($_SESSION['user_role'] définie dans SeConnecter.php)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    // Si l'utilisateur n'est pas admin, on le redirige immédiatement vers la page de connexion
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 3. RÉCUPÉRATION DYNAMIQUE DES STATISTIQUES
try {
    $db = Database::getInstance(); 

    // Compte des entreprises
    $queryEntreprises = $db->query("SELECT COUNT(*) FROM Entreprise");
    $nbEntreprises = $queryEntreprises->fetchColumn();

    // Compte des missions
    $queryMissions = $db->query("SELECT COUNT(*) FROM Mission");
    $nbMissions = $queryMissions->fetchColumn();

    // Compte des employés
    $queryEmployes = $db->query("SELECT COUNT(*) FROM Employe");
    $nbEmployes = $queryEmployes->fetchColumn();

    // Compte des services
    $queryServices = $db->query("SELECT COUNT(*) FROM Service");
    $nbServices = $queryServices->fetchColumn();

} catch (Exception $e) {
    // Valeurs de secours si la base de données rencontre un problème
    $nbEntreprises = "N/A";
    $nbMissions = "N/A";
    $nbEmployes = "N/A";
    $nbServices = "N/A";
    $erreur_sql = $e->getMessage();
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
    <link rel="stylesheet" href="assets/Statistiques.css">
    <title>Statistiques | Espace Administrateur</title>
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
        <h1 class="title">Statistiques Globales</h1>

        <?php if (isset($erreur_sql)): ?>
            <p style="color:red; text-align:center; background:#fff; padding:10px; border:1px solid red; max-width:600px; margin:20px auto;">
                ⚠️ Erreur SQL : <?= htmlspecialchars($erreur_sql) ?>
            </p>
        <?php endif; ?>

        <div class="stats-grid">
            
            <div class="stat-card">
                <h3>Nombre d'entreprises</h3>
                <p class="value"><?= htmlspecialchars($nbEntreprises) ?></p>
            </div>

            <div class="stat-card">
                <h3>Nombre de missions</h3>
                <p class="value"><?= htmlspecialchars($nbMissions) ?></p>
            </div>

            <div class="stat-card">
                <h3>Nombre d'employés</h3>
                <p class="value"><?= htmlspecialchars($nbEmployes) ?></p>
            </div>

            <div class="stat-card">
                <h3>Nombre de Services</h3>
                <p class="value"><?= htmlspecialchars($nbServices) ?></p>
            </div>

        </div>

        <div class="logout-wrapper">
            <a href="Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <ul>
            <li>
                <a href="<?= BASE_URL ?>/index.php">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
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
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Retour au site public</a></li>
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