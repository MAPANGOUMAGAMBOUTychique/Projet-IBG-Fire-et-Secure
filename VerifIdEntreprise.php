<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('<div style="padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:5px;"><strong>❌ Erreur :</strong> ID d\'entreprise manquant dans l\'URL.</div>');
}

try {
    require_once 'Database.php';
    $db = Database::getInstance();
    
    // Récupération des données entreprise à partir de la table Candidature
    $stmt = $db->prepare("SELECT * FROM Candidature WHERE Id_Candidature = ? AND Type_Candidature = 'entreprise'");
    $stmt->execute([intval($_GET['id'])]);
    $ent = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ent) {
        die('<div style="padding:20px; background:#fff3cd; color:#856404; font-family:sans-serif; margin:20px; border-radius:5px;"><strong>⚠️ Introuvable :</strong> Aucune demande d\'entreprise ne correspond à cet ID.</div>');
    }

} catch (Throwable $e) {
    echo '<div style="padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; font-family:monospace; margin:20px; border-radius:5px;">';
    echo '<h3>💥 Erreur lors de la récupération des données</h3>';
    echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Entreprise - <?= htmlspecialchars($ent['Nom_Entreprise_Candidature'] ?? 'Inconnu') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .details-container { max-width: 800px; margin: 40px auto; padding: 30px; border: 1px solid #e3e6f0; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { color: #333; font-size: 24px; margin-top: 0; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
        h3 { color: #28a745; margin-top: 25px; border-bottom: 1px solid #e3e6f0; padding-bottom: 5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid #f8f9fc; padding-bottom: 8px; }
        .label { font-weight: bold; color: #28a745; width: 35%; }
        .value { color: #5a5c69; width: 65%; text-align: left; }
        .btn-back { display: inline-block; margin-bottom: 25px; padding: 10px 18px; background: #333; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; }
        .btn-back:hover { background: #555; }
        .statut-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.9em; font-weight: bold; background: #f6c23e; color: #fff; }
    </style>
</head>
<body>
    <div class="details-container">
        <a href="Administrateur.php" class="btn-back">← Retour au panneau Admin</a>
        
        <h1>Dossier Entreprise : <?= htmlspecialchars($ent['Nom_Entreprise_Candidature'] ?? '') ?></h1>
        <p>Statut actuel de la demande : <span class="statut-badge"><?= htmlspecialchars($ent['Statut_Candidature'] ?? 'Non défini') ?></span></p>

        <h3>Informations Légales</h3>
        <div class="row"><span class="label">Nom de l'entreprise :</span> <span class="value"><?= htmlspecialchars($ent['Nom_Entreprise_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">N° SIRET :</span> <span class="value"><?= htmlspecialchars($ent['Siret_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Code NAF :</span> <span class="value"><?= htmlspecialchars($ent['Code_NAF_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Numéro de TVA :</span> <span class="value"><?= htmlspecialchars($ent['Numero_TVA_Candidature'] ?? 'Non renseigné') ?></span></div>

        <h3>Coordonnées de l'Établissement</h3>
        <div class="row"><span class="label">Téléphone :</span> <span class="value"><?= htmlspecialchars($ent['Telephone_Entreprise_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Adresse :</span> 
            <span class="value">
                <?= htmlspecialchars(($ent['Numero_Voie_Candidature'] ?? '') . ' ' . ($ent['Nom_Voie_Candidature'] ?? '')) ?>
                <?= !empty($ent['Complement_Candidature']) ? '<br>' . htmlspecialchars($ent['Complement_Candidature']) : '' ?>
                <br><?= htmlspecialchars($ent['Ville_Candidature'] ?? '') ?>
                <br><?= htmlspecialchars($ent['Pays_Entreprise_Candidature'] ?? '') ?>
            </span>
        </div>

        <h3>Référent / Contact Principal</h3>
        <div class="row"><span class="label">Nom du référent :</span> <span class="value"><?= htmlspecialchars($ent['Nom_Referent_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Fonction du référent :</span> <span class="value"><?= htmlspecialchars($ent['Fonction_Referent_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Email de contact :</span> <span class="value"><?= htmlspecialchars($ent['Email_Contact_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Date de soumission :</span> <span class="value"><?= htmlspecialchars($ent['Date_Candidature'] ?? 'Non renseignée') ?></span></div>
    </div>
</body>
</html>