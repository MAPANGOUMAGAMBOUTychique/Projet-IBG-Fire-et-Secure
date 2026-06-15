<?php
session_start();

// 1. Force l'affichage de toutes les erreurs PHP cachées par le serveur
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// 2. Sécurité : Vérification des droits d'accès de l'administrateur
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 3. Vérification de la présence de l'ID dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('<div style="padding:20px; background:#f8d7da; color:#721c24; font-family:sans-serif; margin:20px; border-radius:5px;"><strong>❌ Erreur :</strong> L\'identifiant (ID) de la candidature est manquant dans l\'URL.</div>');
}

try {
    // Inscription sécurisée de la classe Database
    require_once 'Database.php';
    
    // Récupération de l'instance PDO
    $db = Database::getInstance();
    
    // Requête préparée sur la table "Candidature"
    $stmt = $db->prepare("SELECT * FROM Candidature WHERE Id_Candidature = ?");
    $stmt->execute([intval($_GET['id'])]);
    $cand = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si aucun enregistrement ne correspond à cet ID
    if (!$cand) {
        die('<div style="padding:20px; background:#fff3cd; color:#856404; font-family:sans-serif; margin:20px; border-radius:5px;"><strong>⚠️ Introuvable :</strong> Aucune candidature ne correspond à l\'ID ' . htmlspecialchars($_GET['id']) . ' dans la base de données.</div>');
    }

} catch (Throwable $e) {
    // Capturateur universel de crash (S'affiche sous forme d'encadré rouge détaillé)
    echo '<div style="padding:20px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; font-family:monospace; margin:20px; border-radius:5px; line-height: 1.6;">';
    echo '<h3 style="margin-top:0;">💥 Une erreur fatale est survenue !</h3>';
    echo '<p><strong>Message de l\'erreur :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '<p><strong>Fichier coupable :</strong> ' . htmlspecialchars($e->getFile()) . '</p>';
    echo '<p><strong>Ligne du crash :</strong> ' . $e->getLine() . '</p>';
    echo '<hr style="border:0; border-top:1px solid #f5c6cb;">';
    echo '<small>Vérifiez que le fichier Database.php est bien dans le même dossier et que vos tables/colonnes MySQL sont correctement orthographiées.</small>';
    echo '</div>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification Dossier - <?= htmlspecialchars($cand['Nom_Candidature'] ?? 'Inconnu') ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0; }
        .details-container { max-width: 800px; margin: 40px auto; padding: 30px; border: 1px solid #e3e6f0; background: #fff; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h1 { color: #333; font-size: 24px; margin-top: 0; border-bottom: 2px solid #4e73df; padding-bottom: 10px; }
        h3 { color: #4e73df; margin-top: 25px; border-bottom: 1px solid #e3e6f0; padding-bottom: 5px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 12px; border-bottom: 1px solid #f8f9fc; padding-bottom: 8px; }
        .label { font-weight: bold; color: #4e73df; width: 35%; }
        .value { color: #5a5c69; width: 65%; text-align: left; }
        .btn-back { display: inline-block; margin-bottom: 25px; padding: 10px 18px; background: #4e73df; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; transition: background 0.2s; }
        .btn-back:hover { background: #2e59d9; }
        .doc-link { color: #1cc88a; text-decoration: none; font-weight: bold; }
        .doc-link:hover { text-decoration: underline; }
        .statut-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.9em; font-weight: bold; background: #f6c23e; color: #fff; }
    </style>
</head>
<body>
    <div class="details-container">
        <a href="Administrateur.php" class="btn-back">← Retour au panneau Admin</a>
        
        <h1>Dossier de candidature : <?= htmlspecialchars(($cand['Prenom_Candidature'] ?? '') . " " . ($cand['Nom_Candidature'] ?? '')) ?></h1>
        <p>Statut actuel du dossier : <span class="statut-badge"><?= htmlspecialchars($cand['Statut_Candidature'] ?? 'Non défini') ?></span></p>

        <div class="row"><span class="label">Date de naissance :</span> <span class="value"><?= htmlspecialchars($cand['Date_Naissance_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Lieu de naissance :</span> <span class="value"><?= htmlspecialchars($cand['Lieu_Naissance_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Nationalité :</span> <span class="value"><?= htmlspecialchars($cand['Nationalite_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Téléphone :</span> <span class="value"><?= htmlspecialchars($cand['Telephone_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">N° CNAPS :</span> <span class="value"><?= htmlspecialchars($cand['Numero_CNAPS_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Expiration CNAPS :</span> <span class="value"><?= htmlspecialchars($cand['Expiration_CNAPS_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Dernière Visite Médicale :</span> <span class="value"><?= htmlspecialchars($cand['Date_Visite_Med_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Permis B :</span> <span class="value"><?= htmlspecialchars($cand['Permis_b_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Véhiculé :</span> <span class="value"><?= htmlspecialchars($cand['Vehicule_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Aptitude Visuelle :</span> <span class="value"><?= htmlspecialchars($cand['Aptitude_Vue_Candidature'] ?? 'Non renseignée') ?></span></div>
        <div class="row"><span class="label">Type de contrat désiré :</span> <span class="value"><?= htmlspecialchars($cand['Type_Contrat_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row"><span class="label">Rayon de mobilité :</span> <span class="value"><?= htmlspecialchars($cand['Mobilite_Rayon_Candidature'] ?? '0') ?> KM</span></div>
        <div class="row"><span class="label">Uniforme accepté :</span> <span class="value"><?= htmlspecialchars($cand['Port_Uniforme_Candidature'] ?? 'Non renseigné') ?></span></div>
        <div class="row" style="flex-direction: column;"><span class="label" style="width: 100%; margin-bottom: 5px;">Disponibilités :</span> <span class="value" style="width: 100%; background: #f8f9fc; padding: 10px; border-radius: 4px; border: 1px solid #eaecf4;"><?= nl2br(htmlspecialchars($cand['Disponibilites_Candidature'] ?? 'Aucune disponibilité saisie.')) ?></span></div>

        <h3>Pièces Justificatives</h3>
        <div class="row">
            <span class="label">CV :</span> 
            <span class="value">
                <?= (!empty($cand['CV_Path_Candidature'])) ? '<a href="'.BASE_URL.'/'.$cand['CV_Path_Candidature'].'" target="_blank" class="doc-link">📄 Voir le CV (PDF)</a>' : 'Non fourni' ?>
            </span>
        </div>
        <div class="row">
            <span class="label">Lettre de Motivation :</span> 
            <span class="value">
                <?= (!empty($cand['Lettre_Motivation_Candidature'])) ? '<a href="'.BASE_URL.'/'.$cand['Lettre_Motivation_Candidature'].'" target="_blank" class="doc-link">📄 Voir la LM (PDF)</a>' : 'Non fournie' ?>
            </span>
        </div>
        <div class="row">
            <span class="label">Casier Judiciaire :</span> 
            <span class="value">
                <?= (!empty($cand['Casier_Path_Candidature'])) ? '<a href="'.BASE_URL.'/'.$cand['Casier_Path_Candidature'].'" target="_blank" class="doc-link">📄 Voir le Casier</a>' : 'Non fourni' ?>
            </span>
        </div>
    </div>
</body>
</html>