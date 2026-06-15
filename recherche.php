<?php
session_start();
require_once 'Database.php';

// Activer l'affichage des erreurs pour le développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

$recherche = "";
$services_trouves = [];

// On récupère le mot-clé saisi
if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $recherche = trim($_GET['q']);

    try {
        $bdd = Database::getInstance();

        // Requête SQL corrigée : on utilise deux marqueurs distincts (:nom et :desc)
        $stmt = $bdd->prepare("SELECT * FROM Service WHERE Nom_Service LIKE :nom OR Description_Service LIKE :desc");
        
        // On lie la valeur recherchée aux deux marqueurs
        $stmt->execute([
            'nom'  => '%' . $recherche . '%',
            'desc' => '%' . $recherche . '%'
        ]);
        
        $services_trouves = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur lors de la recherche : " . $e->getMessage();
    }
}

// CORRESPONDANCE DES IMAGES : On lie le nom en BDD à ton image HTML correspondante
$images_services = [
    "Surveillance Statique et contrôle d'Accès" => "assets/image/Jour4/Image gardinnage et sécurité-1682125948844-e2dc8996b0f0.avif",
    "Rondes de Surveillance et Sécurité Mobile" => "assets/image/Jour4/Image rpnde de surveillance et sécurité mobile-1661499169247-81649e8667d8.avif",
    "Protection Événementielle" => "assets/image/Jour4/Image protection evenementielle-1760228604788-db8a36d5c1a3.avif",
    "Prévention et Intervention Incendie (SSIAP)" => "assets/image/Jour4/Image prévention et intervention incendie-1482173989-612x612.webp",
    "Maintenance des Systèmes de Sécurité Incendie" => "assets/image/Jour4/Image maintenance des systèmes incendies-1482775856-612x612.webp",
    "Formation et Exercices d'Évacuation" => "assets/image/Evacuation-1663075966038-6b37cc036924.avif",
    "Audit et Conseil en Ingénierie et Sûreté" => "assets/image/Jour4/Image conseil et expertise-1661695279211-dfc3866380d1.avif",
    "Formation à la Gestion des Risques" => "assets/image/Jour4/Image gestion des risques-1663089690804-1c6d97412b7a.avif",
    "Sécurisation des Sites Sensibles" => "assets/image/Jour4/Image sécurisation des sites sensibles-2248999048-612x612.webp"
];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Résultats de recherche | IBG FIRE ET SECURE</title>
    <style>
        .search-results-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .info-recherche {
            margin-bottom: 20px;
            color: #555;
        }
        .aucun-resultat {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li> 
                <li><a href="<?= BASE_URL ?>/NosServices.php?page=nos_services">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php?page=nous_contacter">Nous contacter</a></li> 
                <li><a href="<?= BASE_URL ?>/Postuler.php?page=postuler">Postuler</a></li> 
                <li><a href="<?= BASE_URL ?>/SeConnecter.php?page=login">Se connecter</a></li> 
                <li><a href="<?= BASE_URL ?>/CreerUnCompte.php?page=creer_compte">Créer un compte</a></li> 
            </ul>
        </nav>
    </header>

    <main class="search-results-container">
        <h1>Résultats de la recherche</h1>
        
        <p class="info-recherche">
            Vous avez recherché : <strong><?= htmlspecialchars($recherche) ?></strong>
        </p>

        <form action="recherche.php" method="GET" class="search_bar" style="margin-bottom: 40px;">
            <input type="search" name="q" value="<?= htmlspecialchars($recherche) ?>" placeholder="Rechercher un autre service...">
            <button type="submit">Rechercher</button>
        </form>

        <section id="services">
            <?php if (!empty($services_trouves)): ?>
                <ul class="service-grid">
                    <?php foreach ($services_trouves as $service): ?>
                        <?php 
                            // On récupère la bonne image associée ou une image par défaut si le nom change
                            $nom_du_service = $service['Nom_Service'];
                            $image_url = isset($images_services[$nom_du_service]) ? $images_services[$nom_du_service] : 'assets/image/Logo_IBG_FS-removebg-preview.png';
                        ?>
                        <li class="reveal-items">
                            <article>
                                <h4><?= htmlspecialchars($service['Nom_Service']) ?></h4>
                                <img src="<?= $image_url ?>" alt="Image <?= htmlspecialchars($service['Nom_Service']) ?>">
                                <p><?= htmlspecialchars($service['Description_Service']) ?></p>
                                <a href="Identification.php" class="btn-solliciter">Solliciter</a>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="aucun-resultat">
                    ❌ Aucun service ne correspond à votre recherche. Essayez avec des mots plus simples comme "gardien", "incendie", "rondes" ou "SSIAP".
                </div>
            <?php endif; ?>
        </section>
    </main>

    <footer>
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="<?= BASE_URL ?>/index.php?page=mentions_legales">Mentions légales</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?page=politique_confidentialite">Politique de Confidentialité</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php?page=nos_services">Nos Services</a></li>