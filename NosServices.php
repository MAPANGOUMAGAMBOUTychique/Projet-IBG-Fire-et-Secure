<?php
session_start();
require_once 'Database.php';

// 1. On active l'affichage des erreurs pour voir ce qui cloche si ça plante
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. Définition de la constante BASE_URL
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Nos Services | Site IBG FIRE ET SECURE</title>
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
        <section id="services"> 
            <h2>Nos Services</h2>

            <section id="GardiennageEtSurveillance">
                 <h3>Pôle Gardiennage et Surveillance</h3>
                <ul class="service-grid">
                    
                    <li class="reveal-items">
                        <article>
                            <h4>Surveillance Statique et contrôle d'Accès</h4>
                            <img src="assets/image/Jour4/Image gardinnage et sécurité-1682125948844-e2dc8996b0f0.avif" alt="Image Surveillace statique et controle" class="surveillance-control">
                            <p>Nos agents de sécurité qualifiés assurent un contrôle rigoureux des flux de personnes, de véhicules et de marchandises. En combinant vigilance humaine et protocoles de vérification stricts, nous garantissons l'étanchéité de vos périmètres. Qu'il s'agisse de sites industriels sensibles ou de complexes tertiaires, notre mission est de prévenir toute intrusion tout en préservant la fluidité de vos accès quotidiens.</p>
                            <a href="Identification.php?service=Surveillance Statique et contrôle d'Accès" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>
                        
                    <li class="reveal-items">
                        <article>
                            <h4>Rondes de Surveillance et Sécurité Mobile</h4>
                            <img src="assets/image/Jour4/Image rpnde de surveillance et sécurité mobile-1661499169247-81649e8667d8.avif" alt="Image Surveillance sécurité mobile">
                            <p>Patrouilles mobiles, aléatoires ou programmées, effectuées en véhicule pour inspecter les points sensibles de vos sites. Nos agents assurent une présence dynamique et une surveillance étendue pour prévenir toute anomalie.</p>
                            <a href="Identification.php?service=Rondes de Surveillance et Sécurité Mobile" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                    <li class="reveal-items">
                       <article>
                            <h4>Protection Événementielle</h4>
                            <img src="assets/image/Jour4/Image protection evenementielle-1760228604788-db8a36d5c1a3.avif" alt="Image Protection Événement">
                            <p>Garantissez le succès et la sérénité de vos événements de haut standing. De la sécurisation des salons internationaux aux rassemblements privés exclusifs, nos équipes gèrent avec diplomatie et fermeté le contrôle des accès et la fluidité des flux de foule. Nous allions discrétion et vigilance pour offer à vos invités un environnement sûr et prestigieux.</p>
                            <a href="Identification.php?service=Protection Événementielle" class="btn-solliciter">Solliciter</a>
                       </article>
                    </li>
                    
                </ul>
            </section>
                
            <section id="SecuriteEtIncendie">
                <h3>Pôle Sécurité Incendie</h3>
                <ul class="service-grid">

                    <li class="reveal-items">
                        <article>
                            <h4>Prévention et Intervention Incendie (SSIAP)</h4>
                            <img src="assets/image/Jour4/Image prévention et intervention incendie-1482173989-612x612.webp" alt="Image Prévention et intervention incendie">
                            <p>Nos agents certifiés SSIAP (niveaux 1, 2 et 3) assurent une veille constante contre les risques d'incendie. Experts en prévention, ils garantissent la conformité de vos installations par une vérification rigoureuse des équipements de secours et assurent une gestion exemplaire de l'évacuation et de la mise en sécurité des occupants en cas de sinistre.</p>
                            <a href="Identification.php?service=Prévention et Intervention Incendie (SSIAP)" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                    <li class="reveal-items">
                        <article>
                            <h4>Maintenance des Systèmes de Sécurité Incendie</h4>
                            <img src="assets/image/Jour4/Image maintenance des systèmes incendies-1482775856-612x612.webp" alt="Image Maintenance de sécurité Incendie">
                            <p>Garantissez l'opérationnalité de vos dispositifs de secours. Nous réalisons des audits approfondis et la maintenance technique de vos systèmes de sécurité incendie (SSI) : alarmes, détecteurs de fumée, colonnes sèches et Robinets d'Incendie Armés (RIA). Nos interventions certifiées assurent la conformité de vos installations aux normes en vigueur et une réactivité optimale de vos équipements.</p>
                            <a href="Identification.php?service=Maintenance des Systèmes de Sécurité Incendie" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                    <li class="reveal-items">
                        <article>
                            <h4>Formation et Exercices d'Évacuation</h4>
                            <img src="assets/image/Evacuation-1663075966038-6b37cc036924.avif" alt="Image Formation d'ecercice d'evacuation">
                            <p>La préparation est la clé d'une gestion effective des situations d'urgence. Nous formons vos équipes à adopter les bons réflexes et organisons des exercices d'évacuation grandeur nature pour tester vos procédures. En cas d'alerte, chaque seconde compte : nous vous aidons à bâtir une organisation fluide et sécurisée pour protéger toutes les personnes présentes dans vos locaux.</p>
                            <a href="Identification.php?service=Formation et Exercices d'Évacuation" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                </ul>
            </section>

             
            <section id="ConseilEtExpertise">
                <h3>Pôle Conseil et Expertise</h3>
                <ul class="service-grid">

                    <li class="reveal-items">
                        <article>
                            <h4>Audit et Conseil en Ingénierie et Sûreté</h4>
                            <img src="assets/image/Jour4/Image conseil et expertise-1661695279211-dfc3866380d1.avif" alt="Image Audit et conseil ingénieur en sureté">
                            <p>Anticipez les menaces par une approche analytique de votre sûreté. Nos experts réalisent un audit complet des vulnérabilités de vos infrastructures, englobant les risques humains, techniques et organisationnels. À l'issue de cette étude de terrain, nous concevons un plan de sécurité sur mesure, optimisant vos ressources pour garantir une protection maximale et pérenne de vos sites.</p>
                            <a href="Identification.php?service=Audit et Conseil en Ingénierie et Sûreté" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                    <li class="reveal-items">
                        <article>
                            <h4>Formation à la Gestion des Risques</h4>
                            <img src="assets/image/Jour4/Image gestion des risques-1663089690804-1c6d97412b7a.avif" alt="Image gestion des risques">
                            <p>Développez une véritable culture de la prévention au sein de vos équipes. Nos experts certifiés forment vos collaborateurs aux gestes qui sauvent et à la manipulation des équipements d'extinction. En maîtrisant les réflexes de premiers secours et l’usage des extincteurs, votre personnel devient le premier maillon de votre chaîne de sécurité, garantissant une réactivité immédiate face à l'accident ou au début d'incendie.</p>
                            <a href="Identification.php?service=Formation à la Gestion des Risques" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                    <li class="reveal-items">
                        <article>
                            <h4>Sécurisation des Sites Sensibles</h4>
                            <img src="assets/image/Jour4/Image sécurisation des sites sensibles-2248999048-612x612.webp" alt="Image Sécurisation des sites sensibles">
                            <p>Sécurisation des environnements critiques et sites à haut risque. Pour vos entrepôts de grande valeur, chantiers d'envergure ou zones industrielles isolées, nous déployons des dispositifs de protection renforcés. Alliant technologies de pointe et unités d'élite, notre approche garantit une surveillance hermétique de vos actifs les plus sensibles, même dans les conditions les plus exigeantes.</p>
                            <a href="Identification.php?service=Sécurisation des Sites Sensibles" class="btn-solliciter">Solliciter</a>
                        </article>
                    </li>

                </ul>
            </section>
        </section>
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
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
                        <li><a href="Postuler.php">Je postule</a></li>
                        <li><a href="SeConnecter.php">Se connecter</a></li>
                        <li><a href="CreerUnCompte.php">Créer un compte</a></li>
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