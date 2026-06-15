<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';

date_default_timezone_set('Europe/Paris');

$message = "";
$erreur = "";
$token_valide = false;
$email_concerne = "";

// 1. VÉRIFICATION DU TOKEN DANS LA TABLE UTILISATEUR
if (isset($_GET['token']) && !empty($_GET['token'])) {
    $token = trim($_GET['token']);
    
    try {
        $bdd = Database::getInstance();
        
        // On cherche l'utilisateur qui possède ce reset_token
        $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE reset_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Comparaison temporelle sécurisée des dates
            $date_actuelle = strtotime(date('Y-m-d H:i:s'));
            $date_expiration = strtotime($user['reset_expires']);
            
            if ($date_actuelle <= $date_expiration) {
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
    $erreur = "Aucun jeton de réinitialisation n'a été fourni.";
}

// 2. SÉCURISATION ET ENREGISTREMENT DU NOUVEAU MOT DE PASSE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_update'])) {
    $token = trim($_POST['token']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    if (empty($password) || strlen($password) < 6) {
        $erreur = "Le mot de passe doit contenir au moins 6 caractères.";
        $token_valide = true; 
    } elseif ($password !== $password_confirm) {
        $erreur = "Les deux mots de passe ne sont pas identiques.";
        $token_valide = true; 
    } else {
        try {
            $bdd = Database::getInstance();
            
            // Hachage du mot de passe en BCRYPT
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // On met à jour le mot de passe ET on vide les champs de reset (Sécurité)
            $stmt = $bdd->prepare("UPDATE Utilisateur SET Mot_De_Passe_Utilisateur = ?, reset_token = NULL, reset_expires = NULL WHERE Email_Utilisateur = ?");
            $stmt->execute([$password_hash, $email]);
            
            $message = "Votre mot de passe a bien été réinitialisé ! Vous pouvez maintenant vous connecter.";
            $token_valide = false; 
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
    <title>Nouveau mot de passe | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main style="max-width: 450px; margin: 60px auto; padding: 20px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; color: #c90000;">Nouveau mot de passe</h2>
        
        <?php if (!empty($message)): ?>
            <p style="color: green; background: #e6f4ea; padding: 10px; border-radius: 4px; border: 1px solid #34a853; text-align: center;"><?= $message ?></p>
            <p style="text-align:center; margin-top:20px;"><a href="SeConnecter.php" style="padding:10px 20px; background:#333; color:#fff; text-decoration:none; border-radius:4px; font-weight:bold;">Se connecter</a></p>
        <?php endif; ?>
        
        <?php if (!empty($erreur)): ?>
            <p style="color: red; background: #fce8e6; padding: 10px; border-radius: 4px; border: 1px solid #ea4335;"><?= $erreur ?></p>
        <?php endif; ?>

        <?php if ($token_valide): ?>
            <form action="" method="POST" style="margin-top: 20px;">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
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
                
                <button type="submit" style="width: 100%; padding: 10px; background: #c90000; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">Mettre à jour le mot de passe</button>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>