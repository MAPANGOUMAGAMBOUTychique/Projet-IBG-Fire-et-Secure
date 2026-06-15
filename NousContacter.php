<?php
session_start();
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

ini_set('pcre.jit', 0); // Désactive le JIT PCRE pour éviter le Warning lié aux restrictions de mémoire

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');
$message_succes = false;
$erreur_formulaire = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db = Database::getInstance();

        // Nettoyage et sécurisation des données reçues
        $nom       = strip_tags(trim($_POST['user_name']));
        $prenom    = strip_tags(trim($_POST['user_prenom']));
        $email     = filter_var(trim($_POST['user_email']), FILTER_VALIDATE_EMAIL);
        $telephone = !empty($_POST['user_phone']) ? strip_tags(trim($_POST['user_phone'])) : null;
        $texte     = strip_tags(trim($_POST['user_message']));
        
        // Si l'utilisateur est connecté, on récupère son ID, sinon NULL
        $id_utilisateur = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

        if (!$email) {
            throw new Exception("L'adresse e-mail saisie n'est pas valide.");
        }
        if (empty($nom) || empty($prenom) || empty($texte)) {
            throw new Exception("Veuillez remplir tous les champs obligatoires.");
        }

        // Requête d'insertion dans la table Message
        $stmt = $db->prepare("
            INSERT INTO Message (
                Nom_Message, Prenom_Message, Email_Message, Telephone_Message, Texte_Message, Id_Utilisateur
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([$nom, $prenom, $email, $telephone, $texte, $id_utilisateur]);
        $message_succes = true;

    } catch (Throwable $e) {
        $erreur_formulaire = $e->getMessage();
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
    <link rel="stylesheet" href="assets/NousContracter.css">
    <title>Nous contacter | Site IBG FIRE ET SECURE</title>
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
        <?php if (!$message_succes): ?>
        <section id="contact">
            <h1>Nous contacter !</h1>

            <?php if ($erreur_formulaire): ?>
                <div style="padding:10px; background:#f8d7da; color:#721c24; border-radius:4px; margin-bottom:15px;">
                    <strong>❌ Erreur :</strong> <?= htmlspecialchars($erreur_formulaire) ?>
                </div>
            <?php endif; ?>

            <form action="" method="post" class="formulaire">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" name="user_name" id="nom" placeholder="Votre nom" required>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="user_prenom" id="prenom" placeholder="Votre prénom" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mail :</label>
                    <input type="email" id="email" name="user_email" placeholder="exemple@domaine.com" required>
                </div>
                <div class="form-group">
                    <label for="telephone">Téléphone :</label>
                    <input type="tel" id="telephone" name="user_phone" placeholder="+33 06 00 00 00 00" pattern="(\+33|0)[1-9](\s?\d{2}){4}" title="Format attendu : +33 ou 0 suivi de 9 chiffres">
                </div>
                <div class="form-group">
                    <label for="message">Message :</label>
                    <textarea name="user_message" id="message" rows="5" placeholder="Comment pouvons-nous vous aider ?" required></textarea>
                </div>

                <button type="submit" class="btn-submit">Envoyer le message</button>
            </form>
        </section>
        <?php endif; ?>

        <?php if ($message_succes): ?>
        <section id="MessageEnvoyer" class="reponse" style="display: block; text-align:center; padding: 40px 20px;">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" style="max-width:150px; margin-bottom:20px;">
            <p style="font-size:1.2em; color:#28a745; font-weight:bold;">Votre message a été envoyé avec succès !</p>
            <p><a href="index.php" style="color:#333; text-decoration:underline;">Retour à l'accueil</a></p>
        </section>
        <?php endif; ?>
    </main>
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>