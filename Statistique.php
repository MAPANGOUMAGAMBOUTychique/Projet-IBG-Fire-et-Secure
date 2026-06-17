<?php
// =========================================================================
// 1. DÉMARRAGE DE LA SESSION ET SÉCURITÉ
// =========================================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

// Active l'affichage des erreurs pour le développement local
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

/* CONTRÔLE D'ACCÈS STRICT : 
   Seul l'administrateur authentifié est autorisé à consulter les métriques globales.
*/
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// =========================================================================
// 2. RÉCUPÉRATION DYNAMIQUE DES STATISTIQUES
// =========================================================================
try {
    $db = Database::getInstance(); 

    // Compte du volume total d'entreprises partenaires
    $queryEntreprises = $db->query("SELECT COUNT(*) FROM Entreprise");
    $nbEntreprises = $queryEntreprises->fetchColumn();

    // Compte du volume total de missions de surveillance/incendie enregistrées
    $queryMissions = $db->query("SELECT COUNT(*) FROM Mission");
    $nbMissions = $queryMissions->fetchColumn();

    // Compte de l'effectif global des employés (agents de sécurité, équipiers)
    $queryEmployes = $db->query("SELECT COUNT(*) FROM Employe");
    $nbEmployes = $queryEmployes->fetchColumn();

    // Compte des typologies de services ou pôles d'activité configurés
    $queryServices = $db->query("SELECT COUNT(*) FROM Service");
    $nbServices = $queryServices->fetchColumn();

} catch (Exception $e) {
    // Mesure de contournement (Fallback) en cas de panne de liaison SQL
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Administrateur.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Statistiques.css">
    <title>Statistiques | Espace Administrateur</title>
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
            <a href="<?= BASE_URL ?>/Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
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