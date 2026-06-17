<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

// 1. Sécurité : Admin uniquement
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 2. Vérification des paramètres requis
$id_demande = intval($_GET['id'] ?? 0);
$action = trim($_GET['action'] ?? '');

if (!$id_demande || !in_array($action, ['accepter', 'refuser'])) {
    die('
        <div style="font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="color:#dc3545;">❌ Action ou identifiant invalide</h2>
            <p style="color:#555; margin-bottom: 20px;">Les données transmises pour modifier la demande sont incorrectes.</p>
            <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
        </div>
    ');
}

$db = Database::getInstance();

// 3. Détermination du nouveau statut attendu par votre base de données
$nouveau_statut = ($action === 'accepter') ? 'Acceptée' : 'Refusée';

// 4. Exécution de la mise à jour
$stmt = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = ? WHERE Id_Demande_Service = ?");
$success = $stmt->execute([$nouveau_statut, $id_demande]);

// 5. Redirection vers la page de détails (le fichier que vous venez de me montrer)
// Vous pouvez remplacer 'VoirDemandeService.php' par le nom réel de votre fichier de consultation
header("Location: " . BASE_URL . "/VoirDemandeService.php?id=" . $id_demande . "&mutation=success");
exit();