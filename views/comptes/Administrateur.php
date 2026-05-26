<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: /index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Administrateur | Site IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
</head>
<body>
    <header>
        <a href="index.html"><img src="image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
            <nav class="navbar">
                <ul>
                <li><a href="index.php">Accueil</a></li> 
                <li><a href="Statistique.html">Statistiques</a></li> 
                <li><a href="Entreprises.html">Entreprises</a></li> 
                <li><a href="Employers.html">Employés</a></li> 
                <li><a href="Services.html">Services</a></li>
                <li><a href="Missions.html">Missions</a></li>  
                </ul>
                
            </nav>
        
    </header>
<main>
    <h1>Nom de l'administrateur</h1>

    <section class="container">
        <h2>Postulation Création du compte Employer</h2>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th colspan="3">Statut</th>
                </tr>
                <tr>
                    <th></th>
                    <th>En attente</th>
                    <th>Accepté</th>
                    <th>Refusé</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="Postuler.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
                <tr>
                    <td><a href="Postuler.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
                <tr>
                    <td><a href="Postuler.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
            </tbody>
        </table>
    </section>

    <section class="container">
        <h2>Postulation Création de compte Entreprise</h2>
        <table>
            <thead>
                <tr>
                    <th></th>
                    <th colspan="3">Statut</th>
                </tr>
                <tr>
                    <th></th>
                    <th>En attente</th>
                    <th>Accepté</th>
                    <th>Refusé</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><a href="PostulationEntreprise.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
                <tr>
                    <td><a href="PostulationEntreprise.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
                <tr>
                    <td><a href="PostulationEntreprise.html">Employer 1</a></td>
                    <td><span class="dot yellow"></span></td>
                    <td><span class="dot green"></span></td>
                    <td><span class="dot red"></span></td>
                </tr>
            </tbody>
        </table>
    </section>

    <div class="logout-wrapper">
        <a href="index.php" class="logout-btn">Se déconnecter</a>
    </div>
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
                        <li><a href="index.html#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="index.html#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="index.html#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
                   
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.html">Mentions légales</a></li>
                        <li><a href="Accueil.html">Accueil</a></li>
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