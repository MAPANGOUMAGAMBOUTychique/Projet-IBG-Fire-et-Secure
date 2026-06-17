<?php
// =========================================================================
// 1. CONFIGURATION SÉCURISÉE DES COOKIES DE SESSION (OPTIMISÉE POUR LOCALHOST)
// =========================================================================
session_set_cookie_params([
    'lifetime' => 0,          // La session s'éteint dès la fermeture du navigateur de l'utilisateur.
    'path' => '/',            // La session est active sur l'intégralité des répertoires du site.
    'secure' => false,        // Reste à 'false' en développement local non-HTTPS (passer à true en production SSL).
    'httponly' => true,       // Bloque l'accessibilité aux cookies de session via scripts JavaScript (Contre failles XSS).
    'samesite' => 'Lax'       // Protection contre les attaques CSRF tout en maintenant l'état de connexion lors des redirections.
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de l'accès centralisé à la base de données.
require_once 'Database.php';

// Affichage explicite des erreurs PHP pour faciliter le débogage technique en cours de stage.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Centralisation de l'adresse racine racine du projet.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Initialisation des variables de retour d'erreurs d'authentification.
$erreur_employe = "";
$erreur_entreprise = "";

// =========================================================================
// 2. LOGIQUE DU CONTRÔLEUR UNIQUE : RECEPTION ET DISPATCH DES FORMULAIRES POST
// =========================================================================
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action_type'])) {
    
    $bdd = Database::getInstance();

    // ---------------------------------------------------------------------
    // CAS 1 : BLOC DE CONNEXION DES EMPLOYÉS & DE L'ADMINISTRATEUR
    // ---------------------------------------------------------------------
    if ($_POST['action_type'] === 'login_employe') {
        // Nettoyage et validation du format de l'adresse email soumise.
        $email = filter_var(trim($_POST['user_email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['user_mot_de_passe'];

        if (!empty($email) && !empty($password)) {
            try {
                // Recherche de l'utilisateur sur son adresse email unique.
                $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    /* SYSTEME DE VERIFICATION HYBRIDE :
                       Le code accepte à la fois les mots de passe hachés via BCRYPT (password_verify) 
                       et les mots de passe en texte brut (pour vos comptes de tests créés à la main en base).
                    */
                    if (password_verify($password, $user['Mot_De_Passe_Utilisateur']) || $password === $user['Mot_De_Passe_Utilisateur']) {
                        
                        // Hydratation des variables de session globales de l'utilisateur.
                        $_SESSION['user_id'] = $user['Id_Utilisateur'];
                        $_SESSION['user_nom'] = $user['Nom_Utilisateur'];
                        
                        // Sécurisation de la casse du rôle utilisateur pour éviter les conflits ('Admin' vs 'admin').
                        $_SESSION['user_role'] = strtolower(trim($user['Role'])); 

                        // Dispatching vers l'espace applicatif dédié selon le niveau de privilège.
                        if ($_SESSION['user_role'] === 'admin') {
                            header("Location: " . BASE_URL . "/Administrateur.php");
                            exit();
                        } else {
                            // Vérification complémentaire si l'ID existe spécifiquement dans la table Employe.
                            $stmt_emp = $bdd->prepare("SELECT Id_Employe FROM Employe WHERE Id_Employe = ?");
                            $stmt_emp->execute([$user['Id_Utilisateur']]);
                            $employe = $stmt_emp->fetch(PDO::FETCH_ASSOC);
                            
                            if ($employe) {
                                $_SESSION['employe_id'] = $employe['Id_Employe'];
                            } else {
                                $_SESSION['employe_id'] = $user['Id_Utilisateur'];
                            }

                            header("Location: " . BASE_URL . "/CompteEmploye.php");
                            exit();
                        }
                    } else {
                        $erreur_employe = "Le mot de passe inséré est incorrect.";
                    }
                } else {
                    $erreur_employe = "Aucun compte n'est associé à cette adresse email.";
                }
            } catch (PDOException $e) {
                $erreur_employe = "Erreur technique : " . $e->getMessage();
            }
        } else {
            $erreur_employe = "Veuillez remplir tous les champs.";
        }
    }

    // ---------------------------------------------------------------------
    // CAS 2 : BLOC DE CONNEXION DE L'ENTREPRISE PARTENAIRE
    // ---------------------------------------------------------------------
    if ($_POST['action_type'] === 'login_entreprise') {
        // Suppression de tous les espaces saisis par l'utilisateur pour standardiser la chaîne SIRET (14 caractères).
        $siret = str_replace(' ', '', trim($_POST['user_siret']));
        $password = $_POST['user_mot_de_passe_entreprise'];

        if (!empty($siret) && !empty($password)) {
            try {
                // ETAPE 1 : Localisation de l'entreprise via son numéro SIRET épuré d'espaces en BDD.
                $stmt = $bdd->prepare("SELECT * FROM Entreprise WHERE REPLACE(Siret_Entreprise, ' ', '') = ?");
                $stmt->execute([$siret]);
                $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($entreprise) {
                    // ETAPE 2 : Extraction du compte d'accès lié à l'adresse e-mail de contact de cette entreprise.
                    $stmt_user = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                    $stmt_user->execute([$entreprise['Email_Contact_Entreprise']]);
                    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        // ETAPE 3 : Contrôle de concordance du mot de passe (Hybride).
                        if (password_verify($password, $user['Mot_De_Passe_Utilisateur']) || $password === $user['Mot_De_Passe_Utilisateur']) {
                            
                            // Configuration de la session profilée "Entreprise".
                            $_SESSION['user_id'] = $user['Id_Utilisateur'];
                            $_SESSION['user_nom'] = $entreprise['Nom_Entreprise'];
                            $_SESSION['user_role'] = 'entreprise';
                            $_SESSION['entreprise_id'] = $entreprise['Id_Entreprise'];
                            $_SESSION['employe_id'] = $user['Id_Utilisateur']; // Évite les ruptures de scripts intermédiaires.

                            header("Location: " . BASE_URL . "/CompteEntreprise.php");
                            exit();
                        } else {
                            $erreur_entreprise = "Le mot de passe associé à cette entreprise est incorrect.";
                        }
                    } else {
                        $erreur_entreprise = "Aucun compte de connexion actif trouvé pour l'adresse email : " . $entreprise['Email_Contact_Entreprise'];
                    }
                } else {
                    $erreur_entreprise = "Aucune entreprise trouvée avec ce numéro SIRET.";
                }
            } catch (PDOException $e) {
                $erreur_entreprise = "Erreur technique : " . $e->getMessage();
            }
        } else {
            $erreur_entreprise = "Veuillez remplir tous les champs.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <title>Connexion | IBG FIRE ET SECURE</title>
    <style>
        .signalement-erreur {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            font-size: 14px;
            font-weight: bold;
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
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                <li><a href="<?= BASE_URL ?>/NosServices.php">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li>
                <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="Connexion">
            <h1>Connexion Employé</h1>
            
            <?php if (!empty($erreur_employe)): ?>
                <div class="signalement-erreur">
                    ⚠️ <?= htmlspecialchars($erreur_employe) ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/SeConnecter.php" method="post" class="formulaire">
                <input type="hidden" name="action_type" value="login_employe">

                <div class="form-group">
                    <label for="Email_employe">Email :</label>
                    <input type="email" name="user_email" id="Email_employe" placeholder="Ex : admin@ibgfire.fr" required>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_employe">Mot de Passe :</label>
                    <input type="password" name="user_mot_de_passe" id="Mot_de_passe_employe" required>
                    <a href="<?= BASE_URL ?>/MotDePasseOublier.php">Mot de passe oublié ?</a>
                    <a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte ?</a>
                </div>
                <button type="submit" class="btn-submit">Connexion</button>
            </form>
        </section>

        <section class="Connexion">
            <h1>Connexion Entreprise</h1>

            <?php if (!empty($erreur_entreprise)): ?>
                <div class="signalement-erreur">
                    ⚠️ <?= htmlspecialchars($erreur_entreprise) ?>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/SeConnecter.php" method="post" class="formulaire">
                <input type="hidden" name="action_type" value="login_entreprise">

                <div class="form-group">
                    <label for="Numero_siret">Numéro SIRET :</label>
                    <input type="text" name="user_siret" id="Numero_siret" placeholder="145 156 187 58694" required>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de Passe :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" required>
                    <a href="<?= BASE_URL ?>/MotDePasseOublier.php">Mot de passe oublié ?</a>
                    <a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte ?</a>
                </div>
                <button type="submit" class="btn-submit">Connexion</button>
            </form>
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