<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../assets/index.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <link rel="stylesheet" href="../../assets/NousContracter.css">
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

        <section id="contact">
            <h1>Nous contacter !</h1>

            <form action="" method="post" class="formulaire">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" name="user_name" id="nom"  placeholder="Votre nom" required>
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

        <section id="MessageEnvoyer" class="reponse">
            <img src="../../assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE">
            <p>Votre message a été envoyé avec succès !</p>
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
                <article>

            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="../../views/pages/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="../../views/pages/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="../../index.php">Accueil</a></li>
                        <li><a href="../../views/pages/NosServices.php">Nos Services</a></li>
                        <li><a href="../../views/pages/NousContacter.php">Nous contacter</a></li>
                        <li><a href="../../views/postulations/Postuler.php">Je postule</a></li>
                        <li><a href="../../views/auth/SeConnecter.php">Se connecter</a></li>
                        <li><a href="../../views/auth/CreerUnCompte.php">Créer un compte</a></li>
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