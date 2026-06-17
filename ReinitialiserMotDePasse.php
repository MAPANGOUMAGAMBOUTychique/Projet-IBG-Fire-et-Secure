<?php
// =========================================================================
// 1. INITIALISATION DE LA SESSION ET CONFIGURATION DU TEMPS
// =========================================================================

// On démarre la session si elle n'est pas déjà active sur le serveur.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe Singleton de gestion de la base de données.
require_once 'Database.php';

// Fixe le fuseau horaire sur Paris pour éviter les décalages de calcul d'expiration du token.
date_default_timezone_set('Europe/Paris');

// Définition de l'URL racine de l'application.
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Initialisation des variables de contrôle pour l'affichage des retours utilisateurs.
$message = "";
$erreur = "";
$token_valide = false;
$email_concerne = "";

// =========================================================================
// 2. ÉTAPE DE VÉRIFICATION DU TOKEN (ACCÈS EN REQUÊTE GET VIA LE LIEN MAILING)
// =========================================================================
if (isset($_GET['token']) && !empty($_GET['token'])) {
    // Nettoyage des espaces éventuels autour du token.
    $token = trim($_GET['token']);
    
    try {
        $bdd = Database::getInstance();
        
        // Requête préparée pour rechercher si ce jeton existe bien dans la table Utilisateur.
        $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            /* SÉCURISATION TEMPORELLE (Validité 1 heure) :
               On convertit la date système actuelle et la date d'expiration stockée en timestamp (secondes).
               Si l'heure actuelle dépasse la date limite, le lien est rejeté.
            */
            $date_actuelle = strtotime(date('Y-m-d H:i:s'));
            $date_expiration = strtotime($user['reset_expires']);
            
            if ($date_actuelle <= $date_expiration) {
                // Le jeton est bon et toujours dans les temps : on autorise l'affichage du formulaire.
                $token_valide = true;
                $email_concerne = $user['Email_Utilisateur'];
            } else {
                $erreur = "Le lien de réinitialisation a expiré (valable 1h). Veuillez refaire une demande.";
            }
        } else {
            $erreur = "Le lien de réinitialisation est invalide ou a déjà été utilisé.";
        }
    } catch (PDOException $e) {
        $erreur = "Erreur de base de données : " . $e->getMessage();
    }
} else if (!isset($_POST['action_update'])) {
    // Si aucun token n'est passé en GET et que le formulaire POST n'est pas soumis, l'accès est frauduleux.
    $erreur = "Aucun jeton de réinitialisation n'a été fourni.";
}

// =========================================================================
// 3. ÉTAPE DE MISE À JOUR DU MOT DE PASSE (RÉCEPTION EN REQUÊTE POST)
// =========================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    // Récupération des données masquées et des nouveaux mots de passe saisis.
    $token = trim($_POST['token']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // ÉTAPE 3.1 : Règles de validation de sécurité du mot de passe
    if (empty($password) || strlen($password) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
        $token_valide = true; // On maintient le formulaire ouvert pour correction.
    } elseif ($password !== $password_confirm) {
        $erreur = "Les deux mots de passe ne sont pas identiques.";
        $token_valide = true; // On maintient le formulaire ouvert pour correction.
    } else {
        try {
            $bdd = Database::getInstance();
            
            /* SÉCURISATION DU MOT DE PASSE :
               On utilise la fonction native password_hash avec l'algorithme BCRYPT.
               Il applique un salage automatique, rendant le mot de passe impossible à décrypter dans la base.
            */
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            /* PROTECTION CONTRE LE REPLAY ATTACK (Réutilisation du lien) :
               Une fois le mot de passe mis à jour, on repasse impérativement 'reset_token' et 'reset_expires' à NULL.
               Cela invalide définitivement le lien reçu par mail, empêchant un pirate de le réutiliser.
            */
            $stmt = $bdd->prepare("
                UPDATE Utilisateur 
                SET Mot_De_Passe_Utilisateur = ?, reset_token = NULL, reset_expires = NULL 
                WHERE Email_Utilisateur = ?
            ");
            $stmt->execute([$password_hash, $email]);
            
            $message = "Votre mot de passe a bien été réinitialisé ! Vous pouvez maintenant vous connecter.";
            $token_valide = false; // Ferme définitivement le formulaire puisque l'opération a réussi.
            
        } catch (PDOException $e) {
            $erreur = "Erreur lors de la mise à jour : " . $e->getMessage();
            $token_valide = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouveau mot de passe | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
</head>
<body style="background: #f4f6f9; font-family: Arial, sans-serif;">

    <main style="max-width: 450px; margin: 60px auto; padding: 20px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px;">
        <h2 style="text-align: center; color: #c90000; margin-top: 0;">Nouveau mot de passe</h2>
        
        <?php if (!empty($message)): ?>
            <p style="color: green; background: #e6f4ea; padding: 10px; border-radius: 4px; border: 1px solid #34a853; text-align: center; font-weight: bold;">
                <?= htmlspecialchars($message) ?>
            </p>
            <p style="text-align:center; margin-top:20px;">
                <a href="<?= BASE_URL ?>/SeConnecter.php" style="padding:10px 20px; background:#333; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold; display: inline-block;">
                    Se connecter
                </a>
            </p>
        <?php endif; ?>
        
        <?php if (!empty($erreur)): ?>
            <p style="color: red; background: #fce8e6; padding: 10px; border-radius: 4px; border: 1px solid #ea4335; font-weight: bold;">
                <?= htmlspecialchars($erreur) ?>
            </p>
        <?php endif; ?>

        <?php if ($token_valide): ?>
            <form action="" method="POST" style="margin-top: 20px;">
                
                <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
                <input type="hidden" name="email" value="<?= htmlspecialchars($email_concerne) ?>">
                <input type="hidden" name="action_update" value="1">

                <div style="margin-bottom: 15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Nouveau mot de passe :</label>
                    <input type="password" name="password" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <div style="margin-bottom: 20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:bold;">Confirmez le mot de passe :</label>
                    <input type="password" name="password_confirm" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
                </div>
                
                <button type="submit" style="width: 100%; padding: 10px; background: #c90000; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer; transition: background 0.2s;">
                    Mettre à jour le mot de passe
                </button>
            </form>
        <?php endif; ?>
    </main>

</body>
</html>