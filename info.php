<?php
// Définition des variables et des données en PHP
$titrePage = "Mon Projet en PHP";
$auteur = "Développeur";
$description = "Ceci est une page construite avec PHP et intégrée dans un serveur local.";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titrePage; ?></title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f8;
            color: #333;
            margin: 0;
            padding: 40px;
        }
        .conteneur {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        p {
            line-height: 1.6;
        }
        .date-dynamique {
            background-color: #e8f4f8;
            padding: 10px;
            border-left: 4px solid #3498db;
            margin-top: 20px;
        }
        footer {
            margin-top: 30px;
            text-align: center;
            font-size: 0.9em;
            color: #7f8c8d;
        }
    </style>
</head>
<body>

    <div class="conteneur">
        <h1><?php echo $titrePage; ?></h1>
        
        <p><?php echo $description; ?></p>
        
        <div class="date-dynamique">
            <p><strong>Information serveur :</strong> Nous sommes le 
            <?php 
                // Utilisation d'une fonction PHP pour afficher la date actuelle
                setlocale(LC_TIME, 'fr_FR.UTF-8');
                echo date('d/m/Y'); 
            ?></p>
        </div>
    </div>

    <footer>
        <p>Site créé par <?php echo $auteur; ?> - &copy; <?php echo date('Y'); ?></p>
    </footer>

</body>
</html>