<?php
// ==========================================
// 1. INITIALISATION DE LA SESSION & CONFIGURATION
// ==========================================

// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Définition de la racine du site web pour centraliser et sécuriser les URLs (évite les ../../ complexes)
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// ==========================================
// 2. LOGIQUE DE TRAITEMENT DU FORMULAIRE (À implémenter si besoin)
// ==========================================
$message_success = false;
$message_erreur = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et sécurisation des données soumises
    $password = $_POST['user_mot_de_passe'] ?? '';
    $confirm_password = $_POST['user_confirm_mot_de_passe'] ?? '';

    // Exemple de vérification basique de correspondance
    if (!empty($password) && $password === $confirm_password) {
        // C'est ici que vous insérerez votre logique de hashage (password_hash) et de sauvegarde en BDD
        $message_success = true; 
    } else {
        $message_erreur = "Les mots de passe ne correspondent pas.";
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
    
    <title>Définition du mot de passe | Site IBG FIRE ET SECURE</title>
</head>
<body>

    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li> 
                <li><a href="<?= BASE_URL ?>/views/pages/NosServices.php">Nos services</a></li>
                <li><a href="<?= BASE_URL ?>/views/pages/NousContacter.php">Nous contacter</a></li> 
                <li><a href="<?= BASE_URL ?>/views/postulations/Postuler.php">Postuler</a></li> 
                <li><a href="<?= BASE_URL ?>/views/auth/SeConnecter.php">Se connecter</a></li> 
                <li><a href="<?= BASE_URL ?>/views/auth/CreerUnCompte.php">Créer un compte</a></li> 
            </ul>
        </nav>
    </header>

    <main>
        
        <section>
            <h1>Définissez votre mot de passe !</h1>
            
            <?php if (!empty($message_erreur)): ?>
                <p style="color: red; text-align: center;"><?= htmlspecialchars($message_erreur) ?></p>
            <?php endif; ?>

            <form action="" method="post" class="formulaire">
                <h2>Mot de passe</h2>
                
                <div class="form-group">
                    <label for="Mot_de_passe">Mot de Passe :</label>
                    <input type="password" name="user_mot_de_passe" id="Mot_de_passe" minlength="8" required>
                </div>
                
                <div class="form-group">
                    <label for="Confirmation_Mot_de_passe">Confirmation du mot de Passe :</label>
                    <input type="password" name="user_confirm_mot_de_passe" id="Confirmation_Mot_de_passe" minlength="8" required>
                </div>

                <button type="submit" class="btn-submit">Confirmer</button>
            </form>
        </section>

        <?php if ($message_success): ?>
            <section id="Compte_creeer" class="reponse" style="display: block;">
                <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE">
                <p>Votre compte Employeur a été créé avec succès !</p>
            </section>
        <?php endif; ?>

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
            
            <li></li>
            
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/views/pages/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="<?= BASE_URL ?>/views/pages/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/views/pages/NosServices.php">Nos Services</a></li>
                        <li><a href="<?= BASE_URL ?>/views/pages/NousContacter.php">Nous contacter</a></li>
                        <li><a href="<?= BASE_URL ?>/views/postulations/Postuler.php">Je postule</a></li>
                        <li><a href="<?= BASE_URL ?>/views/auth/SeConnecter.php">Se connecter</a></li>
                        <li><a href="<?= BASE_URL ?>/views/auth/CreerUnCompte.php">Créer un compte</a></li>
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