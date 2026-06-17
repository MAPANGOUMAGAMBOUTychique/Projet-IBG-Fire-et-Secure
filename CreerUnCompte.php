<?php
// ==========================================
// 1. CONFIGURATION SÉCURISÉE DES COOKIES DE SESSION
// ==========================================

// Définition des paramètres du cookie de session avant le démarrage de celle-ci
session_set_cookie_params([
    'lifetime' => 0,                      // Le cookie de session expire dès que l'utilisateur ferme son navigateur
    'path' => '/',                        // Le cookie est accessible sur l'ensemble des répertoires du site
    'domain' => 'localhost',              // Domaine local utilisé pour la phase de développement
    'secure' => false,                    // /!\ À passer à true en production dès l'activation du certificat SSL (HTTPS)
    'httponly' => true,                   // Bloque l'accès au cookie via des scripts JavaScript (Protection majeure contre les failles XSS)
    'samesite' => 'Strict'                // Interdit l'envoi du cookie lors de requêtes tierces (Protection robuste contre les failles CSRF)
]);

// Démarrage officiel du système de session PHP
session_start();

// Centralisation de l'URL racine du projet pour garantir l'intégrité de tous les liens (évite les chemins relatifs cassés)
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    
    <title>Création de compte | Site IBG FIRE ET SECURE</title>
</head>
<body>

    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li> 
                <li><a href="<?= BASE_URL ?>/NosServices.php">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li> 
                <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li> 
                <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <h1>Création du Compte</h1>

        <section class="compte">
            
            <section class="Particulier">
                <a href="<?= BASE_URL ?>/CreationCompteEmploye.php"><h2>Je suis un Particulier</h2></a>
                <a href="<?= BASE_URL ?>/CreationCompteEmploye.php">
                    <img src="<?= BASE_URL ?>/assets/image/Un particulier-1557862921-37829c790f19.avif" alt="Image particulier">
                </a>
            </section>
            
            <section class="Entreprise">
                <a href="<?= BASE_URL ?>/CreationCompteEntreprise.php"><h2>Je suis une Entreprise</h2></a>
                <a href="<?= BASE_URL ?>/CreationCompteEntreprise.php">
                    <img src="<?= BASE_URL ?>/assets/image/Une entreprise-1676357175001-dec2a4bf4241.avif" alt="Une entreprise">
                </a>
            </section>
            
        </section>
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
            
            <li></li>
            
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="<?= BASE_URL ?>/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php">Nos Services</a></li>
                        <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li>
                        <li><a href="<?= BASE_URL ?>/Postuler.php">Je postule</a></li>
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