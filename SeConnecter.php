<?php
// 1. CONFIGURATION SÉCURISÉE DES COOKIES DE SESSION (OPTIMISÉE POUR LOCALHOST)
session_set_cookie_params([
    'lifetime' => 0,                      
    'path' => '/',                        
    'secure' => false, // Garder à false tant que tu n'as pas de HTTPS (SSL) en local
    'httponly' => true,                   
    'samesite' => 'Lax' // Remplacé 'Strict' par 'Lax' pour éviter les pertes de session lors des redirections en local
]);

session_start();
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

$erreur_employe = "";
$erreur_entreprise = "";

// LOGIQUE DU CONTRÔLEUR UNIQUE
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action_type'])) {
    
    $bdd = Database::getInstance();

    // ==========================================================
    // 1. CONNEXION EMPLOYÉ & ADMIN (Via Email de l'Utilisateur)
    // ==========================================================
    if ($_POST['action_type'] === 'login_employe') {
        $email = filter_var(trim($_POST['user_email']), FILTER_SANITIZE_EMAIL);
        $password = $_POST['user_mot_de_passe'];

        if (!empty($email) && !empty($password)) {
            try {
                $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user) {
                    // Vérification hybride : supporte le hash sécurisé OU le texte brut
                    if (password_verify($password, $user['Mot_De_Passe_Utilisateur']) || $password === $user['Mot_De_Passe_Utilisateur']) {
                        
                        $_SESSION['user_id'] = $user['Id_Utilisateur'];
                        $_SESSION['user_nom'] = $user['Nom_Utilisateur'];
                        // CORRECTION CRITIQUE : On force le rôle en minuscules pour éviter les conflits de casse
                        $_SESSION['user_role'] = strtolower(trim($user['Role'])); 

                        if ($_SESSION['user_role'] === 'admin') {
                            header("Location: " . BASE_URL . "/Administrateur.php");
                            exit();
                        } else {
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

    // ==========================================================
    // 2. CONNEXION ENTREPRISE (Via le SIRET de l'Entreprise)
    // ==========================================================
    if ($_POST['action_type'] === 'login_entreprise') {
        $siret = str_replace(' ', '', trim($_POST['user_siret']));
        $password = $_POST['user_mot_de_passe_entreprise'];

        if (!empty($siret) && !empty($password)) {
            try {
                // 1. On trouve l'entreprise par son SIRET
                $stmt = $bdd->prepare("SELECT * FROM Entreprise WHERE REPLACE(Siret_Entreprise, ' ', '') = ?");
                $stmt->execute([$siret]);
                $entreprise = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($entreprise) {
                    // 2. On cherche le compte de connexion dans Utilisateur via l'Email_Contact_Entreprise
                    $stmt_user = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                    $stmt_user->execute([$entreprise['Email_Contact_Entreprise']]);
                    $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        // 3. Vérification du mot de passe
                        if (password_verify($password, $user['Mot_De_Passe_Utilisateur']) || $password === $user['Mot_De_Passe_Utilisateur']) {
                            
                            $_SESSION['user_id'] = $user['Id_Utilisateur'];
                            $_SESSION['user_nom'] = $entreprise['Nom_Entreprise'];
                            $_SESSION['user_role'] = 'entreprise';
                            $_SESSION['entreprise_id'] = $entreprise['Id_Entreprise'];
                            $_SESSION['employe_id'] = $user['Id_Utilisateur']; // Évite les bugs de redirection

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

            <form action="SeConnecter.php" method="post" class="formulaire">
                <input type="hidden" name="action_type" value="login_employe">

                <div class="form-group">
                    <label for="Email_employe">Email :</label>
                    <input type="email" name="user_email" id="Email_employe" placeholder="Ex : admin@ibgfire.fr" required>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_employe">Mot de Passe :</label>
                    <input type="password" name="user_mot_de_passe" id="Mot_de_passe_employe" required>
                    <a href="MotDePasseOublier.php">Mot de passe oublié ?</a>
                    <a href="CreerUnCompte.php">Créer un compte ?</a>
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

            <form action="SeConnecter.php" method="post" class="formulaire">
                <input type="hidden" name="action_type" value="login_entreprise">

                <div class="form-group">
                    <label for="Numero_siret">Numéro SIRET :</label>
                    <input type="text" name="user_siret" id="Numero_siret" placeholder="145 156 187 58694" required>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de Passe :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" required>
                    <a href="MotDePasseOublier.php">Mot de passe oublié ?</a>
                    <a href="CreerUnCompte.php">Créer un compte ?</a>
                </div>
                <button type="submit" class="btn-submit">Connexion</button>
            </form>
        </section>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>