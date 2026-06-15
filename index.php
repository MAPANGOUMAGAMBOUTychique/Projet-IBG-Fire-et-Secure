<?php
// ==========================================================================================
// 1. ARCHITECTURE ET COMPORTEMENT SERVEUR : GESTION DES SESSIONS
// ==========================================================================================

/**
 * session_status() vérifie l'état actuel de la session sur le serveur.
 * PHP_SESSION_NONE (valeur entière 1) indique qu'aucune session n'est active.
 * Cette condition stricte (===) évite l'erreur fatale "E_NOTICE: A session has already been started".
 * * Mécanisme sous le capot :
 * session_start() demande au serveur PHP d'envoyer un cookie d'en-tête HTTP (Set-Cookie: PHPSESSID=...)
 * au navigateur (JavaScript côté client peut y accéder via document.cookie sauf si l'option HttpOnly est active).
 * PHP ouvre ou récupère ensuite un fichier temporaire sur le serveur contenant la superglobale $_SESSION.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * require_once force l'inclusion et l'évaluation du fichier 'Database.php'.
 * Contrairement à 'include', si le fichier est introuvable, PHP lève une erreur fatale (Compile Error) 
 * et stoppe immédiatement l'exécution du script, ce qui est indispensable ici puisque le reste 
 * de la page dépend entièrement de la base de données. Le suffixe '_once' empêche les redéclarations 
 * accidentelles de classes si ce script est appelé à l'intérieur d'autres templates.
 */
require_once 'Database.php';

// ==========================================================================================
// 2. CONFIGURATION DES DIRECTIVES DU NOYAU PHP (PHP.INI OVERRIDE)
// ==========================================================================================

/**
 * ini_set() permet de modifier temporairement la configuration du fichier php.ini pour la durée du script.
 * 'display_errors' = 1 force PHP à envoyer les erreurs au flux de sortie (généré dans le HTML).
 * 'display_startup_errors' = 1 capture les erreurs qui surviennent lors de la séquence de démarrage de PHP.
 * error_reporting(E_ALL) demande la capture de TOUS les niveaux d'erreurs (Warnings, Notices, Deprecated, Errors).
 * * ⚠️ VÉRIFICATION DE SÉCURITÉ EN PRODUCTION :
 * En production, ces valeurs DOIVENT être à 0. Afficher les erreurs expose l'architecture de vos fichiers, 
 * vos noms de tables SQL ou vos variables à d'éventuels attaquants. Les erreurs devront alors être lues dans 'error_log'.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ==========================================================================================
// 3. DÉFINITION DU CONTEXTE GLOBAL (CONSTANTES)
// ==========================================================================================

/**
 * define() crée une constante globale non modifiable après déclaration.
 * BASE_URL centralise l'URI racine. Elle permet de construire des chemins absolus pour éviter 
 * les ruptures de liens lors de l'utilisation de la réécriture d'URL (URL Rewriting via .htaccess).
 */
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// ==========================================================================================
// 4. COUCHE D'ACCÈS AUX DONNÉES (DAL) ET TRAITEMENT DES REQUÊTES SQL
// ==========================================================================================

try {
    /**
     * Pattern Design : Singleton
     * On n'utilise pas le mot-clé 'new' pour instancier la base de données.
     * La méthode statique getInstance() vérifie si une connexion PDO existe déjà. 
     * Si oui, elle la renvoie ; si non, elle la crée. Cela évite d'ouvrir de multiples connexions 
     * simultanées vers MySQL, optimisant ainsi la mémoire du serveur.
     */
    $db = Database::getInstance(); 

    /**
     * Exécution de requêtes SQL de type agrégation (COUNT).
     * $db->query() envoie une requête synchrone brute au serveur SQL.
     * fetchColumn() est une méthode optimisée de PDOStatement qui extrait directement la valeur 
     * du premier champ de la première ligne retournée (ici, le résultat numérique du COUNT(*)).
     * Cela évite de charger un tableau associatif complet en mémoire PHP.
     */
    $queryEntreprises = $db->query("SELECT COUNT(*) FROM Entreprise");
    $nbEntreprises = $queryEntreprises->fetchColumn();

    $queryMissions = $db->query("SELECT COUNT(*) FROM Mission");
    $nbMissions = $queryMissions->fetchColumn();

    $queryEmployes = $db->query("SELECT COUNT(*) FROM Employe");
    $nbEmployes = $queryEmployes->fetchColumn();

    $queryServices = $db->query("SELECT COUNT(*) FROM Service");
    $nbServices = $queryServices->fetchColumn();

} catch (Exception $e) {
    /**
     * BLOC DE CAPTURE ET SECURISATION DES ERREURS (Fail-safe mechanism)
     * Si le serveur MySQL tombe (Timeout, identifiants invalides), l'exécution est déroutée ici.
     * Assigner la chaîne "N/A" empêche la génération d'erreurs PHP d'affichage (Undefined variable) 
     * plus bas dans le code HTML. Le rendu de la page reste propre pour le client.
     */
    $nbEntreprises = "N/A";
    $nbMissions = "N/A";
    $nbEmployes = "N/A";
    $nbServices = "N/A";
    
    /**
     * htmlspecialchars() convertit les caractères spéciaux du message d'erreur SQL en entités HTML 
     * (ex: '<' devient '&lt;'). Cela prévient les injections de code si le message d'erreur venait 
     * à contenir des données utilisateur malveillantes.
     */
    echo "<p style='color:red; text-align:center; background:#fff; padding:10px;'>Erreur SQL : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Accueil | IBG FIRE ET SECURE</title>
    <link class="styles" rel="stylesheet" href="assets/style.css">
    <link class="styles" rel="stylesheet" href="assets/index.css">
    <link class="styles" rel="stylesheet" href="assets/Statistiques.css">
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
            <p>Bienvenue chez IBG FIRE ET SECURE, votre partenaire de confiance pour la sécurité globale de vos infrastructures. Installés à Saint-Nazaire, nous mettons notre expertise et notre réactivité au service des entreprises, des collectivités and des particuliers pour garantir une protection sur-mesure face aux risques du quotidien.</p>
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