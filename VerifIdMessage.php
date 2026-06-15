<?php
session_start();
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

// Vérification de sécurité Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("ID du message manquant.");
}

$id_message = intval($_GET['id']);
$db = Database::getInstance();

// 1. Marquer le message comme "Lu" dès qu'il est ouvert
$update = $db->prepare("UPDATE Message SET Statut_Message = 'Lu' WHERE Id_Message = ?");
$update->execute([$id_message]);

// 2. Récupérer les informations complètes du message
$stmt = $db->prepare("SELECT * FROM Message WHERE Id_Message = ?");
$stmt->execute([$id_message]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    die("Message introuvable.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecture Message - <?= htmlspecialchars($msg['Nom_Message']) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <style>
        body { background-color: #f4f6f9; font-family: sans-serif; margin: 0; padding: 0; }
        .message-box { max-width: 700px; margin: 50px auto; padding: 30px; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); border-top: 4px solid #007bff; }
        h1 { font-size: 20px; color: #333; margin-top: 0; margin-bottom: 20px; }
        .meta-info { background: #f8f9fa; padding: 15px; border-radius: 6px; margin-bottom: 20px; font-size: 0.95em; line-height: 1.6; color: #495057; }
        .meta-info strong { color: #007bff; }
        .content-view { padding: 20px; background: #fafafa; border: 1px solid #eee; border-radius: 6px; white-space: pre-wrap; font-size: 1.05em; color: #212529; line-height: 1.5; }
        .btn-back { display: inline-block; margin-bottom: 20px; padding: 8px 15px; background: #333; color: #fff; text-decoration: none; border-radius: 4px; font-size: 0.9em; }
        .btn-back:hover { background: #555; }
    </style>
</head>
<body>
    <div class="message-box">
        <a href="Administrateur.php" class="btn-back">← Retour aux messages</a>
        
        <h1>Détails du message de contact n° <?= $msg['Id_Message'] ?></h1>

        <div class="meta-info">
            <strong>Nom de l'expéditeur :</strong> <?= htmlspecialchars($msg['Nom_Message'] . ' ' . $msg['Prenom_Message']) ?><br>
            <strong>Adresse e-mail :</strong> <a href="mailto:<?= htmlspecialchars($msg['Email_Message']) ?>"><?= htmlspecialchars($msg['Email_Message']) ?></a><br>
            <strong>Téléphone :</strong> <?= htmlspecialchars($msg['Telephone_Message'] ?? 'Non communiqué') ?><br>
            <strong>Date d'envoi :</strong> <?= htmlspecialchars($msg['Date_Envoi_Message']) ?><br>
            <strong>Statut du compte au moment du clic :</strong> 
            <?= !empty($msg['Id_Utilisateur']) ? 'Utilisateur enregistré (ID #' . $msg['Id_Utilisateur'] . ')' : 'Visiteur Anonyme' ?>
        </div>

        <h3>Contenu du message :</h3>
        <div class="content-view"><?= htmlspecialchars($msg['Texte_Message']) ?></div>
    </div>
</body>
</html>