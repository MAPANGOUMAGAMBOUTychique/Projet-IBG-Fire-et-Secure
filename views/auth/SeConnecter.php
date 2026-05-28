 <!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Se connecter</title>
    <link rel="stylesheet" href="/SiteIBGFireSecure/assets/css/style.css">
</head>
<body>
    <header>
        <a href="/SiteIBGFireSecure/index.php">
            <img src="/SiteIBGFireSecure/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="/SiteIBGFireSecure/index.php">Accueil</a></li>
                <li><a href="/SiteIBGFireSecure/index.php?action=contact">Nous contacter</a></li>
                <li><a href="/SiteIBGFireSecure/index.php?action=postuler">Postuler</a></li>
                <li><a href="/SiteIBGFireSecure/index.php?action=seconnecter">Se connecter</a></li>
                <li><a href="/SiteIBGFireSecure/index.php?action=creercompte">Créer un compte</a></li>
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
            <a href="index.php?action=motdepasseoublie">Mot de passe oublié ?</a>
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
            <a href="index.php?action=motdepasseoublie">Mot de passe oublié ?</a>
        </div>
        <button type="submit" class="btn-submit">Connexion</button>
    </form>
</section>

    </main>

    <footer>
        <ul>
            <li>
                <a href="/SiteIBGFireSecure/index.php">
                    <img src="/SiteIBGFireSecure/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
                </a>
            </li>
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
                        <li><a href="/SiteIBGFireSecure/index.php?action=accueil#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=accueil#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=accueil#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="/SiteIBGFireSecure/index.php?action=mentions">Mentions légales</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php">Accueil</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=contact">Nous contacter</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=postuler">Je postule</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=seconnecter">Se connecter</a></li>
                        <li><a href="/SiteIBGFireSecure/index.php?action=creercompte">Créer un compte</a></li>
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