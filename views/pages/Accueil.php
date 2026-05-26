
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <script src="js/global.js" defer></script>
</head>
<body>
    <header>
        <a href="index.html"><img src="image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
            <nav class="navbar">
                <ul>
                <li><a href="index.html">Accueil</a></li>
                <li><a href="NosServices.html">Nos services</a></li> 
                <li><a href="NousContacter.html">Nous contacter</a></li> 
                <li><a href="Postuler.html">Postuler</a></li> 
                <li><a href="SeConnecter.html">Se connecter</a></li> 
                <li><a href="CreerUnCompte.html">Créer un compte</a></li> 
                </ul>
                
            </nav>
        
    </header>
    <main>

    <?php

require_once 'Controllers/AuthController.php';

$action = $_GET['action'] ?? '';

if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AuthController();
    $controller->login();
} else {
    //Affiche la page de connexion par défaut
    require_once SeConnecter.php;
}


?>
        <section>
            <h1>IBG FIRE ET SECURE</h1>
            <p>
                Votre sécurité est notre priorité absolue. Chez IBG Fire et Secure, nous conjuguons expertise technologique et présence humaine pour protéger vos biens, vos infrastructures et vos collaborateurs. Spécialistes du gardiennage et de la prévention des risques d'incendie, nous déployons des solutions sur mesure pour garantir une sérénité totale au quotidien. Faites le choix d'une vigilance sans faille et d'une protection adaptée à vos exigences les plus strictes.
            </p>
        </section>

        <form action=" " method="get" class="search_bar">
            <label for="search-input">Recherchez un service</label>
            <input 
              type="search"
              id="search-input"
              name="q"
              placeholder="Recherchez votre besoin !"
              required
            >
            <button type="submit">
                Rechercher
            </button>
        </form>



































    </main>
    <footer>
        <ul>
            <li><a href="Accueil.html"><img src="image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
                   
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.html">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.html">Politique de Confidentialité</a></li>
                        <li><a href="index.html">Accueil</a></li>
                        <li><a href="NousContacter.html">Nous contacter</a></li>
                        <li><a href="Postuler.html">Je postule</a></li>
                        <li><a href="SeConnecter.html">Se connecter</a></li>
                        <li><a href="CreerUnCompte.html">Créer un compte</a></li>
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

