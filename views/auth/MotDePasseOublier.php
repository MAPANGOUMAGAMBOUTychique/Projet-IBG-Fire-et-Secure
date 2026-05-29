<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/index.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <title>Nos Services</title>
</head>
<body>
    <header>
        <a href="index.php"><img src="../../assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
            <nav class="navbar">
                <ul>
                <li><a href="../../index.php">Accueil</a></li> 
                <li><a href="../../views/pages/NosServices.php">Nos services</a></li>
                <li><a href="../../views/pages/NousContacter.php">Nous contacter</a></li> 
                <li><a href="../../views/postulations/Postuler.php">Postuler</a></li> 
                <li><a href="../../views/auth/SeConnecter.php">Se connecter</a></li> 
                <li><a href="../../views/auth/CreerUnCompte.php">Créer un compte</a></li> 
                </ul>
                
            </nav>
        
    </header>
    <main>
        <h1>Mot de passe oublié</h1>
        <form action="" method="post" class="formulaire">
            <div class="form-group">
            <label for="E-mail_utilisateur_entreprise">E-mail :</label>
            <input type="email" name="user_email_entreprise" id="E-mail_utilisateur_entreprise" placeholder="Ex: service@gmail.com" required>
        </div>

        <button type="submit" class="btn-submit">Envoyer</button>
        </form>

        <section id="Envoyé_email" class="reponse">
            <img src="../../assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE">
            <p>Vous allez recevoir un e-mail contenant un lien pour réinitialisr votre mot de passe.</p>
        </section>
    </main>
<footer>
        <ul>
            <li><a href="index.php"><img src="../../assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
            <li>
                <article>
                    <h4>Siège social IBG FIRE ET SECURE</h4>
                    <p>24 allée de la mer d'iroise 44600 Saint-Nazaire</p>
                </article>
            </li>
            <li>

            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="views/pages/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="views/pages/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="views/pages/NosServices.php">Nos Services</a></li>
                        <li><a href="views/pages/NousContacter.php">Nous contacter</a></li>
                        <li><a href="views/postulations/Postuler.php">Je postule</a></li>
                        <li><a href="views/auth/SeConnecter.php">Se connecter</a></li>
                        <li><a href="views/auth/CreerUnCompte.php">Créer un compte</a></li>
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