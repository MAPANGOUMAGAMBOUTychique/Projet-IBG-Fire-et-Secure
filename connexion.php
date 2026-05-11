<?php
$serveur = "localhost";
$utilisateur = "fire_secure_user";
$motDepasse = "Fsulre@ib1n";
$nomBaseDeDonnees = "fire_secure";

try {
    $connexion = new PDO("mysql:host=$serveur;dbname=$fire_secure;charset=utf8", $utilisateur, $motDePasse);
    // Définir le mode d'erreur de PDO sur Exception
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion à la base de donnée réussie !";   

    } catch(PDOExeption $e) {
    echo "Erreur de connexion :" . $e->getMessage();
    }
    ?>