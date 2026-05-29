 <!DOCTYPE html>
<html lang="fr">
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

<section class="Connexion">
    <h1>Connexion Employé</h1>

    <?php if (!empty($_GET['error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="index.php?action=login" method="post" class="formulaire">
        <div class="form-group">
            <label for="Email_employe">Email :</label>
            <input type="email" name="user_email" id="Email_employe" placeholder="Ex : domaine@gmail.com" required>
        </div>
        <div class="form-group">
            <label for="Mot_de_passe_employe">Mot de Passe :</label>
            <input type="password" name="user_mot_de_passe" id="Mot_de_passe_employe" required>
            <a href="../../views/auth/MotDePasseOublier.php?action=motdepasseoublie">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-submit">Connexion</button>
    </form>
</section>

<section class="Connexion">
    <h1>Connexion Entreprise</h1>

    <?php if (!empty($_GET['error'])): ?>
        <p style="color:red;"><?= htmlspecialchars($_GET['error']) ?></p>
    <?php endif; ?>

    <form action="index.php?action=login" method="post" class="formulaire">
        <div class="form-group">
            <label for="Numero_siret">Numéro SIRET :</label>
            <input type="text" name="user_siret" id="Numero_siret" placeholder="123 456 789 00012" pattern="[0-9\s]{14,18}" title="Le SIRET doit être composé de 14 chiffres" required>
        </div>
        <div class="form-group">
            <label for="Mot_de_passe_entreprise">Mot de Passe :</label>
            <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" required>
            <a href="../../views/auth/MotDePasseOublier.php?action=motdepasseoublie">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-submit">Connexion</button>
    </form>
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