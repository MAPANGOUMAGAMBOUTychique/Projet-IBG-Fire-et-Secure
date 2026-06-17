<?php
// (Vos blocs PHP de gestion de sessions, d'erreurs et de requêtes restent inchangés)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

try {
    $db = Database::getInstance(); 
    $queryEntreprises = $db->query("SELECT COUNT(*) FROM Entreprise");
    $nbEntreprises = $queryEntreprises->fetchColumn();
    $queryMissions = $db->query("SELECT COUNT(*) FROM Mission");
    $nbMissions = $queryMissions->fetchColumn();
    $queryEmployes = $db->query("SELECT COUNT(*) FROM Employe");
    $nbEmployes = $queryEmployes->fetchColumn();
    $queryServices = $db->query("SELECT COUNT(*) FROM Service");
    $nbServices = $queryServices->fetchColumn();
} catch (Exception $e) {
    $nbEntreprises = "N/A"; $nbMissions = "N/A"; $nbEmployes = "N/A"; $nbServices = "N/A";
    echo "<p style='color:red; text-align:center; background:#fff; padding:10px;'>Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Accueil | IBG FIRE ET SECURE</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">

    <link class="styles" rel="stylesheet" href="assets/style.css?v=<?= filemtime('assets/style.css') ?>">
    <link class="styles" rel="stylesheet" href="assets/index.css?v=<?= filemtime('assets/index.css') ?>">
    <link class="styles" rel="stylesheet" href="assets/Statistiques.css?v=<?= filemtime('assets/Statistiques.css') ?>">
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                <li><a href="<?= BASE_URL ?>/NosServices.php?page=nos_services">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php?page=nous_contacter">Nous contacter</a></li>
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/SeConnecter.php?page=login">Se connecter</a></li>
                    <li><a href="<?= BASE_URL ?>/CreerUnCompte.php?page=creer_compte">Créer un compte</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h1>IBG FIRE ET SECURE</h1>
            <p>Bienvenue chez IBG FIRE ET SECURE, votre partenaire de confiance pour la sécurité globale de vos infrastructures. Installés à Saint-Nazaire, nous mettons notre expertise et notre réactivité au service des entreprises, des collectivités et des particuliers pour garantir une protection sur-mesure face aux risques du quotidien.</p>
        </section>

        <form action="recherche.php" method="GET" class="search_bar">
            <label for="site-search">Rechercher sur le site :</label>
            <input type="search" id="site-search" name="q" placeholder="Rechercher un service, une prestation...">
            <button type="submit">Rechercher</button>
        </form>

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
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
                        <?php if (isset($_SESSION['user'])): ?>
                            <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/index.php?page=login">Se connecter</a></li>
                            <li><a href="<?= BASE_URL ?>/index.php?page=creer_compte">Créer un compte</a></li>
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