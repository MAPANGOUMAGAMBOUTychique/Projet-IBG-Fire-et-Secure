<?php
session_start();
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('pcre.jit', 0); // Correctif pour l'allocation de mémoire JIT

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

$db = Database::getInstance();
$erreur = null;
$email_saisi = '';
$demander_mdp = false;

// On récupère le service depuis l'URL s'il existe
$service_url = isset($_GET['service']) ? trim($_GET['service']) : '';
$param_service = !empty($service_url) ? '?service=' . urlencode($service_url) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['user_email_solicitation'])) {
        $email_saisi = filter_var(trim($_POST['user_email_solicitation']), FILTER_VALIDATE_EMAIL);

        if (!$email_saisi) {
            $erreur = "L'adresse e-mail n'est pas valide.";
        } else {
            // On cherche l'utilisateur par son email
            $stmt = $db->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
            $stmt->execute([$email_saisi]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                $demander_mdp = true;

                if (isset($_POST['user_password'])) {
                    $password = trim($_POST['user_password']);
                    
                    if (password_verify($password, $user['Mot_De_Passe_Utilisateur'])) {
                        
                        if (isset($user['Statut_Compte_Utilisateur']) && $user['Statut_Compte_Utilisateur'] !== 'actif') {
                            $erreur = "Votre compte entreprise est en attente de validation par l'administrateur.";
                        } else {
                            // Initialisation de la session Entreprise
                            $_SESSION['user_id'] = $user['Id_Utilisateur'];
                            $_SESSION['user_nom'] = $user['Nom_Utilisateur'] ?? 'Entreprise';
                            $_SESSION['user_role'] = 'entreprise';
                            
                            // Redirection vers l'espace entreprise EN PASSANT le service
                            header("Location: " . BASE_URL . "/CompteEmploye.php" . $param_service);
                            exit();
                        }
                    } else {
                        $erreur = "Le mot de passe saisi est incorrect.";
                    }
                }
            } else {
                // L'email n'est lié à aucun compte -> Mode Invité
                $_SESSION['invite_email'] = $email_saisi;
                $_SESSION['user_role'] = 'invite';

                // Redirection vers la page de sollicitation EN PASSANT le service dans l'URL
                header("Location: " . BASE_URL . "/SolicitationEntreprise.php" . $param_service);
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Identification | Site IBG FIRE ET SECURE</title>
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="NosServices.php">Nos services</a></li>
                <li><a href="NousContacter.php">Nous contacter</a></li> 
                <li><a href="SeConnecter.php">Se connecter</a></li> 
                <li><a href="CreerUnCompte.php">Créer un compte</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        <section style="max-width: 500px; margin: 40px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="text-align: center; margin-bottom: 20px;">Identification Entreprise</h2>
            
            <?php if ($erreur): ?>
                <div style="padding: 10px; background: #f8d7da; color: #721c24; border-radius: 4px; margin-bottom: 15px; font-size: 0.9em; border: 1px solid #f5c6cb;">
                    <strong>❌ Erreur :</strong> <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <form action="Identification.php<?= $param_service ?>" method="post" class="formulaire">
                
                <div class="form-group" style="margin-bottom: 15px;">
                    <label for="E-mail_solicitation">E-mail de l'entreprise</label>
                    <input type="email" name="user_email_solicitation" id="E-mail_solicitation" 
                           value="<?= htmlspecialchars($email_saisi) ?>" 
                           <?= $demander_mdp ? 'readonly style="background-color: #e9ecef;"' : 'required' ?>>
                </div>

                <?php if ($demander_mdp): ?>
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label for="password_solicitation" style="color: #007bff; font-weight: bold;">Un compte existe. Veuillez saisir votre mot de passe :</label>
                        <input type="password" name="user_password" id="password_solicitation" required autofocus>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 10px; margin-top: 20px;">
                    <button type="submit" class="btn-submit" style="flex: 1;">
                        <?= $demander_mdp ? 'Se connecter' : 'Continuer' ?>
                    </button>
                    
                    <?php if ($demander_mdp): ?>
                        <a href="Identification.php<?= $param_service ?>" style="padding: 10px; background: #6c757d; color: white; text-decoration: none; border-radius: 4px; font-size: 0.9em; text-align: center;">Retour</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>
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