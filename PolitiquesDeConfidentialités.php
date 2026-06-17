<?php
// =========================================================================
// 1. INITIALISATION ET CONFIGURATION DE LA PAGE
// =========================================================================

// Initialise la session si elle n'est pas déjà active. 
// Permet de maintenir l'état connecté/déconnecté de l'utilisateur sur cette page.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition de la constante d'URL absolue pour centraliser et sécuriser l'ensemble des liens du site.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <title>Politique de Confidentialité | IBG FIRE ET SECURE</title>
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
                <li><a href="<?= BASE_URL ?>/Postuler.php">Postuler</a></li> 
                
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li> 
                    <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li> 
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Politique de confidentialité</h1>
        
        <article id="Mentions_contenu">
            
            <p style="margin-bottom: 20px; font-style: italic; color: #555;">
                La présente politique de confidentialité définit et vous informe de la manière dont IBG FIRE ET SECURE utilise et protège les informations que vous nous transmettez, conformément au Règlement Général sur la Protection des Données (RGPD).
            </p>

            <div class="group-mention_legale">
                <h2>1. Collecte des données personnelles</h2>
                <p>Dans le cadre de l'utilisation de nos services et de nos formulaires (Contact, Recrutement, Inscription), nous collectons des données à caractère personnel vous concernant. Ces données incluent notamment votre nom, prénom, adresse e-mail, numéro de téléphone et, le cas échéant, vos données professionnelles (pour les entreprises) ainsi que votre CV (pour les postulants).</p>
            </div>
            
            <div class="group-mention_legale">
                <h2>2. Finalités du traitement des données</h2>
                <p>Les informations collectées nous permettent de traiter vos demandes de contact, d'assurer la gestion de vos comptes utilisateurs, d'étudier vos candidatures professionnelles, de traiter les demandes de devis et d'améliorer la qualité de nos services de sécurité et d'incendie.</p>
            </div>
 
            <div class="group-mention_legale">
                <h2>3. Durée de conservation des données</h2>
                <p>Vos données personnelles sont conservées par IBG FIRE ET SECURE uniquement pour le temps correspondant à la finalité de la collecte : 3 ans maximum pour les données de prospection/contact, et pendant toute la durée d'activation de votre compte client ou entreprise.</p>
            </div>
 
            <div class="group-mention_legale">
                <h2>4. Vos droits (Accès, Rectification, Suppression)</h2>
                <p>Conformément à la réglementation européenne, vous disposez d'un droit d'accès, de rectification, de portabilité et d'effacement de vos données personnelles. Vous pouvez exercer ces droits à tout moment en nous écrivant via notre formulaire de contact ou par courrier postal à l'adresse de notre siège social.</p>
            </div>
 
            <div class="group-mention_legale">
                <h2>5. Utilisation des Cookies</h2>
                <p>Notre site internet utilise des cookies techniques nécessaires au bon fonctionnement de la plateforme (notamment pour la gestion des sessions utilisateurs connectés). Aucun cookie de traçage publicitaire tiers n'est déposé sans votre consentement préalable.</p>
            </div>
        </article>
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
                        <li><a href="<?= BASE_URL ?>/Postuler.php">Je postule</a></li>
                        
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                            <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
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