<?php

ini_set('display_errors', 1);
ini_set('dispay_startup_errors', 1);
error_reporting(E_ALL);
 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page d'Accueil Site IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
</head>
<body>
        <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
            <nav class="navbar">
                <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="views/pages/NosServices.php">Nos services</a></li>
                <li><a href="views/pages/NousContacter.php">Nous contacter</a></li> 
                <li><a href="views/postulations/Postuler.php">Postuler</a></li> 
                <li><a href="views/auth/SeConnecter.php">Se connecter</a></li> 
                <li><a href="views/auth/CreerUnCompte.php">Créer un compte</a></li> 
                </ul>
                
            </nav>
        
    </header>
    <main>
        <section>
        <h1>IBG FIRE ET SECURE</h1>
        <p>Bienvenue chez IBG FIRE ET SECURE, votre partenaire de confiance pour la sécurité globale de vos infrastructures. Installés à Saint-Nazaire, nous mettons notre expertise et notre réactivité au service des entreprises, des collectivités et des particuliers pour garantir une protection sur-mesure face aux risques du quotidien</p>
        </section>

        <form action="recherche.php" method="GET" class="search_bar">
        <label for="site-search">Rechercher sur le site :</label>
        
        <input type="search" id="site-search" name="q" placeholder="Rechercher un service, une prestation...">
        
        <button type="submit">Rechercher</button>
</form>

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

    