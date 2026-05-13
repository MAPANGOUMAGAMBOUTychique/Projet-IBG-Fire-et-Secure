<?php
$host = '127.0.0.1';
$dbname = 'fire_secure';
$user = 'fire_secure_user';
$password = 'Fsulre@ib1n';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connexion réussie !";
} catch (PDOException $e) {
    die("Erreur : " . $e->getMessage());
}
?>