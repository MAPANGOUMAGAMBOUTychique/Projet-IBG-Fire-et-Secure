<?php
// =========================================================================
// 1. GESTION DE LA SESSION, ERREURS ET SÉCURITÉ D'ACCÈS (ADMIN UNIQUEMENT)
// =========================================================================

// session_status() vérifie l'état de la session sur le serveur.
// Si aucune session n'est active (PHP_SESSION_NONE), session_start() l'initialise.
// C'est obligatoire pour pouvoir lire ou modifier les données dans la superglobale $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion du fichier de configuration de la base de données. 
// require_once bloque l'exécution du script si le fichier est introuvable.
require_once 'Database.php';

// Directives de configuration pour remonter toutes les erreurs PHP à l'écran.
// Essentiel en environnement de développement pour le débogage (à désactiver en production).
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définition de l'URL racine du projet dans une constante pour centraliser les redirections.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

/* 
  CONTRÔLE D'ACCÈS STRICT :
  On vérifie deux conditions indispensables pour protéger cette page :
  1. !isset($_SESSION['user_id']) : L'utilisateur n'est pas authentifié.
  2. strtolower($_SESSION['user_role']) !== 'admin' : Le rôle de l'utilisateur n'est pas "admin".
  strtolower() convertit le rôle en minuscules pour éviter les erreurs de casse (ex: "Admin" ou "ADMIN").
  Si l'une des conditions est vraie, l'utilisateur est redirigé vers la page de connexion et le script s'arrête (exit).
*/
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}


// =========================================================================
// 2. RÉCUPÉRATION DES MISSIONS ET COMPTAGE DES CANDIDATS (SQL via PDO)
// =========================================================================

// Initialisation d'un tableau vide pour stocker les futures lignes de données de la base.
$missions = [];

try {
    // Récupération de l'instance unique de connexion à la base de données (Pattern Singleton).
    $bdd = Database::getInstance();
    
    /*
      EXPLICATION DE LA REQUÊTE SQL COMPLEXE :
      - SELECT ... COUNT(p.Id_Mission) AS nb_candidats : On sélectionne les infos de la mission et on compte combien de fois son identifiant apparaît dans la table "Postuler". Le résultat du comptage est renommé "nb_candidats".
      - FROM Mission m : La table principale est "Mission", qu'on surnomme "m" (alias).
      - LEFT JOIN Postuler p ON m.Id_Mission = p.Id_Mission : On joint la table "Postuler" (alias "p") sans supprimer les missions qui n'ont pas encore reçu de candidature (le LEFT JOIN garde toutes les lignes de la table de gauche).
      - GROUP BY m.Id_Mission, m.Titre_Mission : Indispensable quand on utilise une fonction d'agrégation comme COUNT(). On regroupe les résultats par mission unique.
      - ORDER BY m.Id_Mission DESC : Trie les missions de la plus récente à la plus ancienne.
    */
    $sql = "SELECT m.Id_Mission, m.Titre_Mission, COUNT(p.Id_Mission) AS nb_candidats 
            FROM Mission m
            LEFT JOIN Postuler p ON m.Id_Mission = p.Id_Mission
            GROUP BY m.Id_Mission, m.Titre_Mission
            ORDER BY m.Id_Mission DESC";
            
    // Exécution directe de la requête SQL (puisqu'elle ne contient aucune variable utilisateur, pas besoin de prepare).
    $stmt = $bdd->query($sql);
    
    // fetchAll(PDO::FETCH_ASSOC) extrait toutes les lignes sous forme de tableau associatif clé => valeur.
    $missions = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Si la requête échoue (erreur de syntaxe, table manquante), le bloc catch capture l'erreur SQL.
    // On crée une variable $erreur_sql contenant le message pour l'afficher proprement plus bas.
    $erreur_sql = "Erreur de base de données : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Liens vers les feuilles de style CSS de l'application -->
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
    <link rel="stylesheet" href="assets/Missions.css">
    
    <title>Missions | Espace Administrateur</title>
    
    <!-- Styles CSS internes spécifiques aux liens de navigation interne de cette page -->
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
        <!-- Logo cliquable ramenant vers la page d'accueil globale -->
        <a href="<?= BASE_URL ?>/index.php">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        
        <!-- Menu de navigation réservé aux administrateurs pour gérer le back-office -->
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
        <h1 class="main-title">Liste des missions</h1>

        <!-- 
          AFFICHAGE DE L'ERREUR SQL :
          Si la variable $erreur_sql a été créée dans le catch, on l'affiche dans un bloc rouge sécurisé.
          htmlspecialchars() empêche l'exécution de code malveillant si le message d'erreur contient des caractères spéciaux.
        -->
        <?php if (isset($erreur_sql)): ?>
            <p style="color:red; text-align:center; background:#fff; padding:10px; border:1px solid red; max-width:600px; margin:20px auto;">
                ⚠️ <?= htmlspecialchars($erreur_sql) ?>
            </p>
        <?php endif; ?>

        <!-- Tableau d'affichage des missions -->
        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Titre de la Mission (Cliquer pour voir les détails)</th> 
                        <th class="header-count">Nombre d'employés<br>ayant postulé</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- 
                      BOUCLE D'AFFICHAGE DYNAMIQUE :
                      1. if (!empty($missions)) : On vérifie que le tableau de données contient au moins une mission.
                    -->
                    <?php if (!empty($missions)): ?>
                        <!-- 2. foreach ($missions as $mission) : On parcourt chaque ligne du tableau, ligne par ligne. -->
                        <?php foreach ($missions as $mission): ?>
                            <tr>
                                <td class="mission-name">
                                    <!-- 
                                      Le lien transmet l'identifiant unique de la mission dans l'URL via une variable GET (?id=...).
                                      La page DetailMission.php pourra ainsi récupérer cet identifiant avec $_GET['id'].
                                    -->
                                    <a class="link-detail" href="DetailMission.php?id=<?= $mission['Id_Mission'] ?>">
                                        📋 <?= htmlspecialchars($mission['Titre_Mission']) ?>
                                    </a>
                                </td>
                                <!-- Affichage du alias "nb_candidats" calculé en SQL -->
                                <td class="count"><?= htmlspecialchars($mission['nb_candidats']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- 3. else : Si le tableau $missions est vide (aucune ligne en base), on affiche un message d'information à l'admin. -->
                        <tr>
                            <!-- colspan="2" fusionne les deux colonnes du tableau pour faire une seule ligne propre -->
                            <td colspan="2" style="text-align: center; padding: 20px; color: #666;">
                                Aucune mission enregistrée dans la base de données.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Lien d'action rapide permettant à l'administrateur de fermer sa session en toute sécurité -->
        <div class="footer-action">
            <a href="Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <ul>
            <li><a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
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
                        <li><a href="SeConnecter.php">Se connecter</a></li>
                        <li><a href="CreerUnCompte.php">Créer un compte</a></li>
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