<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

// 1. Vérification de sécurité Admin (insensible à la casse)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("
        <div style='font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <h2 style='color:#dc3545;'>❌ Identifiant manquant</h2>
            <p style='color:#555; margin-bottom: 20px;'>Aucun identifiant de message n'a été transmis dans l'URL.</p>
            <a href='" . BASE_URL . "/Administrateur.php' style='display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

$id_message = intval($_GET['id']);
$db = Database::getInstance();

// 2. Marquer le message comme "Lu" dès qu'il est ouvert
$update = $db->prepare("UPDATE Message SET Statut_Message = 'Lu' WHERE Id_Message = ?");
$update->execute([$id_message]);

// 3. Récupérer les informations complètes du message
$stmt = $db->prepare("SELECT * FROM Message WHERE Id_Message = ?");
$stmt->execute([$id_message]);
$msg = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$msg) {
    die("
        <div style='font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <h2 style='color:#dc3545;'>❌ Message introuvable</h2>
            <p style='color:#555; margin-bottom: 20px;'>Le message spécifié n'existe pas ou a été supprimé.</p>
            <a href='" . BASE_URL . "/Administrateur.php' style='display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

// Fonction utilitaire pour uniformiser les valeurs vides
function val(mixed $v, string $fallback = 'Non renseigné'): string {
    return htmlspecialchars(!empty($v) ? $v : $fallback);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails du message n° <?= $msg['Id_Message'] ?> | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <style>
        .dossier {
            max-width: 750px;
            margin: 40px auto;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            padding: 30px;
            font-family: Arial, sans-serif;
        }
        .dossier h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 1.4em;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .section-titre {
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #007bff;
            font-weight: bold;
            margin: 25px 0 15px;
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
            min-width: 200px;
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
            padding: 4px 14px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 0.85em;
        }
        .statut-lu { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .statut-non-lu { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        
        .description-box {
            background: #f8f9fa;
            border-left: 4px solid #6c757d;
            padding: 15px;
            border-radius: 4px;
            white-space: pre-wrap;
            color: #555;
            font-size: 0.92em;
            line-height: 1.5;
            margin-top: 5px;
            width: 100%;
            box-sizing: border-box;
        }
        .description-box.message-content { border-left-color: #007bff; }
        
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 20px;
        }
        @media (max-width: 600px) {
            .grid-2 { grid-template-columns: 1fr; }
            .champ { flex-direction: column; gap: 4px; }
            .champ label { min-width: 100%; }
        }
    </style>
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/Administrateur.php">Accueil Admin</a></li>
                <li><a href="<?= BASE_URL ?>/Statistique.php">Statistiques</a></li>
                <li><a href="<?= BASE_URL ?>/Entreprises.php">Entreprises</a></li>
                <li><a href="<?= BASE_URL ?>/Employers.php">Employés</a></li>
                <li><a href="<?= BASE_URL ?>/Services.php">Services</a></li>
                <li><a href="<?= BASE_URL ?>/Missions.php">Missions</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <div class="dossier">
            <h2>Fiche Message de Contact</h2>

            <div class="section-titre">Statut du message</div>
            <div class="grid-2">
                <div class="champ">
                    <label>État du traitement :</label>
                    <span class="statut-badge statut-<?= strtolower(trim($msg['Statut_Message'] ?? 'non lu')) === 'lu' ? 'lu' : 'non-lu' ?>">
                        <?= ucfirst(htmlspecialchars($msg['Statut_Message'] ?? 'Non lu')) ?>
                    </span>
                </div>
                <div class="champ">
                    <label>Date de réception :</label>
                    <span><?= htmlspecialchars(date('d/m/Y à H:i', strtotime($msg['Date_Envoi_Message']))) ?></span>
                </div>
            </div>

            <div class="section-titre">Informations Expéditeur</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom complet :</label>
                    <span><?= val(($msg['Prenom_Message'] ?? '') . ' ' . ($msg['Nom_Message'] ?? '')) ?></span>
                </div>
                <div class="champ">
                    <label>Téléphone :</label>
                    <span><?= val($msg['Telephone_Message']) ?></span>
                </div>
                <div class="champ">
                    <label>Adresse e-mail :</label>
                    <span><a href="mailto:<?= htmlspecialchars($msg['Email_Message']) ?>" style="color: #007bff; text-decoration: none;"><?= htmlspecialchars($msg['Email_Message']) ?></a></span>
                </div>
                <div class="champ">
                    <label>Type d'expéditeur :</label>
                    <span><?= !empty($msg['Id_Utilisateur']) ? 'Utilisateur membre (ID #' . intval($msg['Id_Utilisateur']) . ')' : 'Visiteur Anonyme' ?></span>
                </div>
            </div>

            <div class="section-titre">Contenu de la correspondance</div>
            <div class="champ" style="flex-direction: column; gap: 4px;">
                <label>Message transmis :</label>
                <div class="description-box message-content"><?= val($msg['Texte_Message']) ?></div>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <a href="<?= BASE_URL ?>/Administrateur.php" class="btn-submit" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 30px; background: #6c757d; color: white; border-radius: 4px; font-weight: bold; font-size: 0.9em;">← Retour au panneau Admin</a>
            </div>
        </div>
    </main>

    <footer>
        <ul>
            <li><a href="<?= BASE_URL ?>/index.php"><img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="<?= BASE_URL ?>/NosServices.php#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>                
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="<?= BASE_URL ?>/PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                        <li><a href="<?= BASE_URL ?>/NosServices.php">Nos Services</a></li>
                        <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li>
                        <li><a href="<?= BASE_URL ?>/SeConnecter.php">Se connecter</a></li>
                        <li><a href="<?= BASE_URL ?>/CreerUnCompte.php">Créer un compte</a></li>
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