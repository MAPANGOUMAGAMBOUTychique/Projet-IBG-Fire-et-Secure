<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'Database.php';

// FORCE LE FUSEAU HORAIRE EN EUROPE/PARIS
date_default_timezone_set('Europe/Paris');

$message = "";
$erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Adresse e-mail invalide.";
    } else {
        try {
            $bdd = Database::getInstance();
            
            // 1. On vérifie d'abord si l'e-mail existe dans la table Utilisateur
            $stmt = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            // ADAPTATION : Si non trouvé, on regarde s'il correspond à l'Email_Contact_Entreprise d'une Entreprise
            if (!$user) {
                $stmt_ent = $bdd->prepare("SELECT Email_Contact_Entreprise FROM Entreprise WHERE Email_Contact_Entreprise = ?");
                $stmt_ent->execute([$email]);
                $entreprise = $stmt_ent->fetch();
                
                if ($entreprise) {
                    // Si l'e-mail appartient à une entreprise, on s'assure qu'une ligne correspondante existe ou est reliée dans Utilisateur
                    $stmt_check = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                    $stmt_check->execute([$email]);
                    $user = $stmt_check->fetch();
                    
                    if (!$user) {
                        // Cas de secours : Si aucun compte Utilisateur n'avait été créé pour cette entreprise, on le crée
                        $stmt_ins = $bdd->prepare("INSERT INTO Utilisateur (Email_Utilisateur, Nom_Utilisateur, Role, Mot_De_Passe_Utilisateur) VALUES (?, ?, 'entreprise', 'TEMPORAIRE')");
                        $stmt_ins->execute([$email, 'Entreprise']);
                        
                        // On récupère le compte utilisateur tout juste créé
                        $stmt_refe = $bdd->prepare("SELECT * FROM Utilisateur WHERE Email_Utilisateur = ?");
                        $stmt_refe->execute([$email]);
                        $user = $stmt_refe->fetch();
                    }
                }
            }
            
            if ($user) {
                // 2. Génération du Token unique
                $token = bin2hex(random_bytes(32));
                
                // 3. Calcul de l'expiration : Maintenant + 1 heure
                $expiration = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // 4. Mise à jour DIRECTE de la table Utilisateur avec le token et l'expiration
                $stmt = $bdd->prepare("UPDATE Utilisateur SET reset_token = ?, reset_expires = ? WHERE Email_Utilisateur = ?");
                $stmt->execute([$token, $expiration, $user['Email_Utilisateur']]);
                
                // 5. Création du lien
                $lien = "http://localhost/StageTychique/SiteIbgFireEtSecure/ReinitialiserMotDePasse.php?token=" . $token;
                
                // 6. Envoi du mail ou simulation
                $sujet = "Réinitialisation de votre mot de passe - IBG FIRE ET SECURE";
                $message_mail = "Bonjour,\n\nPour réinitialiser votre mot de passe, veuillez cliquer sur le lien ci-dessous (valable 1h) :\n" . $lien;
                $headers = "From: no-reply@ibgfire.com\r\nContent-Type: text/plain; charset=UTF-8";
                
                if (mail($user['Email_Utilisateur'], $sujet, $message_mail, $headers)) {
                    $message = "Un lien de réinitialisation vous a été envoyé par e-mail.";
                } else {
                    // TRICHE POUR LE STAGE (Localhost)
                    $message = "Lien généré ! <a href='$lien' style='color:#c90000; font-weight:bold;'>Cliquez ici pour réinitialiser votre mot de passe</a>";
                }
            } else {
                // Phrase générique pour des raisons de sécurité (éviter de faire savoir si un e-mail existe ou non)
                $message = "Un lien de réinitialisation vous a été envoyé si cet e-mail existe.";
            }
        } catch (PDOException $e) {
            $erreur = "Erreur technique : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mot de passe oublié | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <main style="max-width: 450px; margin: 60px auto; padding: 20px; background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; color: #c90000;">Mot de passe oublié</h2>
        <p style="font-size: 14px; color: #666; text-align: center; margin-bottom: 20px;">Entrez votre e-mail pour récupérer votre accès.</p>
        
        <?php if (!empty($message)): ?>
            <p style="color: green; background: #e6f4ea; padding: 10px; border-radius: 4px; border: 1px solid #34a853;"><?= $message ?></p>
        <?php endif; ?>
        
        <?php if (!empty($erreur)): ?>
            <p style="color: red; background: #fce8e6; padding: 10px; border-radius: 4px; border: 1px solid #ea4335;"><?= $erreur ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div style="margin-bottom: 15px;">
                <label style="display:block; margin-bottom:5px; font-weight:bold;">Votre adresse e-mail :</label>
                <input type="email" name="email" required style="width: 100%; padding: 10px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
            </div>
            <button type="submit" style="width: 100%; padding: 10px; background: #c90000; color:#fff; border:none; border-radius:4px; font-weight:bold; cursor:pointer;">Envoyer le lien</button>
        </form>
        <p style="text-align:center; margin-top: 15px;"><a href="SeConnecter.php" style="color: #333; text-decoration: none;">Retour à la connexion</a></p>
    </main>
</body>
</html>