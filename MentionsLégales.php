<?php
// ==========================================
// 1. GESTION DES SESSIONS ET CONFIGURATION PHP
// ==========================================

// session_status() vérifie si une session est déjà active.
// Si ce n'est pas le cas (PHP_SESSION_NONE), session_start() initialise la session.
// Indispensable pour maintenir l'état de connexion de l'utilisateur d'une page à l'autre via $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition de la constante globale pour centraliser l'URL racine de votre projet.
// Cela sécurise vos liens et évite les erreurs de chemins relatifs si vous changez de dossier.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 
?>
<!DOCTYPE html>
<!-- La balise <html> englobe tout le site. L'attribut lang="fr" indique la langue pour les navigateurs et le référencement (SEO). -->
<html lang="fr">
<head>
    <!-- Définit le codage des caractères en UTF-8 pour afficher correctement tous les accents français. -->
    <meta charset="UTF-8">
    
    <!-- Paramètre crucial pour le Responsive Design. Il ordonne au navigateur d'adapter la largeur du site à celle de l'écran (mobile, tablette, PC). -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- 
      Inclusion des feuilles de style CSS.
      On utilise la fonction PHP filemtime() qui calcule la date de dernière modification de votre fichier CSS.
      L'astuce `?v=...` force le navigateur de vos visiteurs à vider son cache et charger le nouveau design dès que vous modifiez votre CSS.
    -->
    <link rel="stylesheet" href="assets/index.css?v=<?= filemtime('assets/index.css') ?>">
    <link rel="stylesheet" href="assets/style.css?v=<?= filemtime('assets/style.css') ?>">
    
    <!-- Le titre textuel de la page, visible tout en haut dans l'onglet du navigateur internet. -->
    <title>Mentions légales | Site IBG FIRE ET SECURE</title>
</head>
<body>

    <!-- ==========================================
         HEADER : L'en-tête commun de votre site
         ========================================== -->
    <header>
        <!-- Logo de l'entreprise encapsulé dans un lien pointant dynamiquement vers l'accueil grâce à BASE_URL -->
        <a href="<?= BASE_URL ?>/index.php">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        
        <!-- Balise sémantique <nav> indiquant aux moteurs de recherche qu'il s'agit du menu de navigation principal -->
        <nav class="navbar">
            <ul>
                <!-- Utilisation systématique de la constante BASE_URL pour sécuriser la navigation -->
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li> 
                <li><a href="<?= BASE_URL ?>/NosServices.php">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li> 
                <li><a href="<?= BASE_URL ?>/Postuler.php">Postuler</a></li> 
                
                <!-- 
                  STRUCTURE CONDITIONNELLE PHP (Affichage dynamique) :
                  Si l'index 'user' existe dans la superglobale $_SESSION, cela signifie que l'utilisateur est connecté.
                  On affiche alors uniquement le bouton "Se déconnecter".
                  Sinon (else), l'utilisateur est un visiteur anonyme, on lui affiche "Se connecter" et "Créer un compte".
                -->
                <?php if (isset($_SESSION['user'])): ?>
                    <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li> 
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li> 
                    <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li> 
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <!-- ==========================================
         MAIN : Zone de contenu spécifique à cette page
         ========================================== -->
    <main>
        <!-- Titre de niveau 1 (H1). Il ne doit y en avoir qu'un seul par page pour un bon référencement Google. -->
        <h1>Mentions légales</h1>
        
        <!-- Balise <article> utilisée ici pour encapsuler le texte juridique de manière sémantique, avec l'ID unique "Mentions_contenu" -->
        <article id="Mentions_contenu">
            
            <!-- 
              Chaque bloc de mention légale utilise la même classe (class="group-mention_legale").
              Cela permet en CSS d'appliquer une marge, une bordure ou une couleur identique à tous les blocs en une seule fois.
            -->
            <div class="group-mention_legale">
                <h2>Editeur du site Internet</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </div>
            
            <div class="group-mention_legale">
                <h2>Responsable de la publication</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </div>
 
            <div class="group-mention_legale">
                <h2>Hébergeur du site</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </div>
 
            <div class="group-mention_legale">
                <!-- Correction orthographique du mot "intellectuelle" -->
                <h2>Propriété intellectuelle</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </div>
 
            <div class="group-mention_legale">
                <h2>Activité réglementée</h2>
                <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
            </div>
        </article>
    </main>

    <!-- ==========================================
         FOOTER : Pied de page global du site
         ========================================== -->
    <footer>
        <ul>
            <!-- Colonne 1 : Rappel visuel du logo d'entreprise -->
            <li>
                <a href="<?= BASE_URL ?>/index.php">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
                </a>
            </li>
            
            <!-- Colonne 2 : Coordonnées postales de l'entreprise -->
            <li>
                <article>
                    <h4>Siège social IBG FIRE ET SECURE</h4>
                    <p>24 allée de la mer d'iroise 44600 Saint-Nazaire</p>
                </article>
            </li>
            
            <!-- Colonne 3 : Liens d'ancrage (liens internes pointant vers des identifiants (ID) précis présents dans la page NosServices.php) -->
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
            
            <!-- Colonne 4 : Plan du site et espace utilisateur -->
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
                        
                        <!-- Même logique d'affichage dynamique que dans le Header pour garder une cohérence visuelle -->
                        <?php if (isset($_SESSION['user'])): ?>
                            <li><a href="<?= BASE_URL ?>/Deconnexion.php">Se déconnecter</a></li>
                        <?php else: ?>
                            <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                            <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
                        <?php endif; ?>
                    </ul>
                </nav> 
            </li>
        </ul>

        <!-- Ligne finale contenant les crédits de l'application -->
        <div class="footer-bottom">
            <!-- &copy; est une entité HTML qui génère proprement le symbole de copyright © à l'écran -->
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>