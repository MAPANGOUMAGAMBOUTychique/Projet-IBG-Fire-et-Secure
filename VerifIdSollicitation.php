<?php
session_start();
require_once 'Database.php';

// 1. Sécurité : Si l'utilisateur n'est pas admin, on le renvoie vers SeConnecter.php
// ATTENTION : Si tu es bloqué en boucle ici, vérifie que tu t'es bien connecté avant
// et que ta page SeConnecter.php remplit correctement $_SESSION['user_role'] = 'admin';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: SeConnecter.php");
    exit();
}

$db = Database::getInstance();
$id_demande = intval($_GET['id'] ?? 0);

// Pour éviter la boucle infinie de redirections vers Administrateur.php si l'ID est absent,
// on affiche un message d'erreur explicite au lieu de faire un header("Location: ...") aveugle.
if (!$id_demande) {
    die("
        <div style='font-family:sans-serif; text-align:center; margin-top:50px; color:#333;'>
            <h2>❌ Identifiant introuvable</h2>
            <p>Aucun identifiant de demande n'a été transmis dans l'URL.</p>
            <a href='Administrateur.php' style='display:inline-block; padding:10px 20px; background:#e67e22; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

// Récupérer la demande + toutes les infos de l'entreprise
$stmt = $db->prepare("
    SELECT ds.*,
           e.Nom_Entreprise, e.Email_Contact_Entreprise, e.Telephone_Entreprise,
           e.Nom_Referent_Entreprise, e.Fonction_Referent_Entreprise, e.Siret_Entreprise,
           e.Numero_voie_Entreprise, e.Nom_Voie_Entreprise, e.Complement_,
           e.Ville_Entreprise, e.Pays_Entreprise, e.Code_NAF_Entreprise, e.Numero_TVA_Entreprise
    FROM Demande_service ds
    JOIN Entreprise e ON ds.Id_Entreprise = e.Id_Entreprise
    WHERE ds.Id_Demande_Service = ?
");
$stmt->execute([$id_demande]);
$demande = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$demande) {
    die("
        <div style='font-family:sans-serif; text-align:center; margin-top:50px; color:#333;'>
            <h2>❌ Demande inexistante</h2>
            <p>La demande spécifiée n'existe pas dans la base de données.</p>
            <a href='Administrateur.php' style='display:inline-block; padding:10px 20px; background:#e67e22; color:white; text-decoration:none; border-radius:5px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

// Parser le message compilé pour extraire les champs lisibles
$message_brut = $demande['Message_Demande_Service'] ?? '';
$lignes = explode("\n", $message_brut);
$champs = [];
$message_client = '';
$in_message = false;

foreach ($lignes as $ligne) {
    if ($in_message) {
        $message_client .= $ligne . "\n";
        continue;
    }
    if (trim($ligne) === '--- Message client ---') {
        $in_message = true;
        continue;
    }
    if (strpos($ligne, ' : ') !== false) {
        [$cle, $valeur] = explode(' : ', $ligne, 2);
        $champs[trim($cle)] = trim($valeur);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
    <title>Dossier sollicitation | IBG FIRE ET SECURE</title>
    <style>
        .dossier {
            max-width: 780px;
            margin: 35px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 40px;
        }
        .dossier h2 {
            color: #e67e22;
            border-bottom: 2px solid #e67e22;
            padding-bottom: 12px;
            margin-bottom: 30px;
            font-size: 1.5em;
        }
        .section-titre {
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #999;
            font-weight: bold;
            margin: 28px 0 12px;
            padding-bottom: 5px;
            border-bottom: 1px solid #eee;
        }
        .champ {
            display: flex;
            align-items: flex-start;
            margin-bottom: 12px;
            gap: 12px;
        }
        .champ label {
            font-weight: bold;
            min-width: 230px;
            color: #444;
            font-size: 0.95em;
            flex-shrink: 0;
        }
        .champ span {
            color: #555;
            font-size: 0.95em;
        }
        .statut-badge {
            display: inline-block;
            padding: 4px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .statut-en-attente { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .statut-accepte    { background: #d4edda; color: #155724; border: 1px solid #28a745; }
        .statut-refuse     { background: #f8d7da; color: #721c24; border: 1px solid #dc3545; }
        .message-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 15px 20px;
            border-radius: 4px;
            white-space: pre-wrap;
            color: #444;
            font-size: 0.92em;
            line-height: 1.6;
            margin-top: 5px;
        }
        .btn-retour {
            display: inline-block;
            margin-top: 35px;
            padding: 11px 28px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 0.95em;
            transition: background 0.2s;
        }
        .btn-retour:hover { background: #5a6268; }
        .vide { color: #bbb; font-style: italic; font-size: 0.9em; }
    </style>
</head>
<body>
    <header>
        <a href="index.php">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="Statistique.php">Statistiques</a></li>
                <li><a href="Entreprises.php">Entreprises</a></li>
                <li><a href="Employers.php">Employés</a></li>
                <li><a href="Services.php">Services</a></li>
                <li><a href="Missions.php">Missions</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="dossier">
            <h2>Dossier de Demande de Service - <?= htmlspecialchars($demande['Nom_Entreprise']) ?></h2>
            
            <div class="section-titre">Statut de la demande</div>
            <div class="champ">
                <label>État actuel :</label>
                <span class="statut-badge statut-<?= str_replace('é', 'e', $demande['Statut_Demande_Service']) ?>">
                    <?= ucfirst(htmlspecialchars($demande['Statut_Demande_Service'])) ?>
                </span>
            </div>

            <div class="section-titre">Informations Entreprise</div>
            <div class="champ">
                <label>Nom de l'entreprise :</label>
                <span><?= htmlspecialchars($demande['Nom_Entreprise']) ?></span>
            </div>
            <div class="champ">
                <label>N° SIRET :</label>
                <span><?= htmlspecialchars($demande['Siret_Entreprise']) ?></span>
            </div>
            <div class="champ">
                <label>Code NAF / TVA :</label>
                <span><?= htmlspecialchars($demande['Code_NAF_Entreprise']) ?> / <?= htmlspecialchars($demande['Numero_TVA_Entreprise'] ?? 'Non renseigné') ?></span>
            </div>
            <div class="champ">
                <label>Adresse :</label>
                <span><?= htmlspecialchars(($demande['Numero_voie_Entreprise'] ?? '') . ' ' . ($demande['Nom_Voie_Entreprise'] ?? '') . ' ' . ($demande['Complement_'] ?? '') . ', ' . $demande['Ville_Entreprise'] . ' (' . $demande['Pays_Entreprise'] . ')') ?></span>
            </div>

            <div class="section-titre">Contact Référent</div>
            <div class="champ">
                <label>Nom du contact :</label>
                <span><?= htmlspecialchars($demande['Nom_Referent_Entreprise'] . ' (' . $demande['Fonction_Referent_Entreprise'] . ')') ?></span>
            </div>
            <div class="champ">
                <label>Email :</label>
                <span><a href="mailto:<?= htmlspecialchars($demande['Email_Contact_Entreprise']) ?>"><?= htmlspecialchars($demande['Email_Contact_Entreprise']) ?></a></span>
            </div>
            <div class="champ">
                <label>Téléphone :</label>
                <span><?= htmlspecialchars($demande['Telephone_Entreprise'] ?? 'Non fourni') ?></span>
            </div>

            <div class="section-titre">Détails de la sollicitation</div>
            <div class="champ">
                <label>Type de Service demandé :</label>
                <span><strong><?= htmlspecialchars($demande['Type_Demande_Service'] ?? 'Général') ?></strong></span>
            </div>

            <?php if (!empty($message_client)): ?>
                <div class="section-titre">Message additionnel de l'entreprise</div>
                <div class="message-box"><?= htmlspecialchars(trim($message_client)) ?></div>
            <?php endif; ?>

            <a href="Administrateur.php" class="btn-retour">← Retour au panneau Admin</a>
        </div>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>