<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('pcre.jit', 0); // Correctif d'allocation de mémoire JIT si nécessaire

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

// 1. Sécurité : Admin uniquement (insensible à la casse)
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

$db = Database::getInstance();
$id_demande = intval($_GET['id'] ?? 0);

// Gestion des erreurs d'identifiant en amont rattachée à BASE_URL
if (!$id_demande) {
    die('
        <div style="font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="color:#dc3545;">❌ Identifiant introuvable</h2>
            <p style="color:#555; margin-bottom: 20px;">Aucun identifiant de demande n\'a été transmis dans l\'URL.</p>
            <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
        </div>
    ');
}

// 2. Récupérer la demande + toutes les infos de l'entreprise
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
    die('
        <div style="font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="color:#dc3545;">❌ Demande inexistante</h2>
            <p style="color:#555; margin-bottom: 20px;">La demande spécifiée n\'existe pas dans la base de données.</p>
            <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
        </div>
    ');
}

// 3. Parser le message compilé pour extraire les champs lisibles
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

// Fonction helper pour affichage sécurisé
function val(mixed $v, string $fallback = 'Non renseigné'): string {
    return htmlspecialchars(!empty($v) ? $v : $fallback);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier Sollicitation - <?= htmlspecialchars($demande['Nom_Entreprise'] ?? 'Inconnu') ?> | IBG FIRE ET SECURE</title>
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
        .badge-attente  { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-accepte  { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .badge-refuse   { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .description-box {
            background: #f8f9fa;
            border-left: 4px solid #007bff;
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
            <h2>Dossier de Demande de Service</h2>
            
            <div class="section-titre">Statut de la demande</div>
            <?php
                $statut = mb_strtolower(trim($demande['Statut_Demande_Service'] ?? ''), 'UTF-8');
                $badge_class = match(true) {
                    str_contains($statut, 'accept') => 'badge-accepte',
                    str_contains($statut, 'refus')  => 'badge-refuse',
                    default                          => 'badge-attente',
                };
            ?>
            <div class="champ">
                <label>État actuel :</label>
                <span class="statut-badge <?= $badge_class ?>">
                    <?= ucfirst(htmlspecialchars($demande['Statut_Demande_Service'] ?? 'En attente')) ?>
                </span>
            </div>

            <div class="section-titre">Informations Entreprise</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom de l'entreprise :</label>
                    <span><strong><?= val($demande['Nom_Entreprise']) ?></strong></span>
                </div>
                <div class="champ">
                    <label>N° SIRET :</label>
                    <span><?= val($demande['Siret_Entreprise']) ?></span>
                </div>
                <div class="champ">
                    <label>Code NAF :</label>
                    <span><?= val($demande['Code_NAF_Entreprise']) ?></span>
                </div>
                <div class="champ">
                    <label>Numéro de TVA :</label>
                    <span><?= val($demande['Numero_TVA_Entreprise']) ?></span>
                </div>
            </div>
            <div class="champ">
                <label>Adresse postale :</label>
                <span>
                    <?= htmlspecialchars(($demande['Numero_voie_Entreprise'] ?? '') . ' ' . ($demande['Nom_Voie_Entreprise'] ?? '')) ?>
                    <?= !empty($demande['Complement_']) ? '<br>' . htmlspecialchars($demande['Complement_']) : '' ?>
                    <br><?= htmlspecialchars($demande['Ville_Entreprise'] ?? '') ?>
                    <br><?= htmlspecialchars($demande['Pays_Entreprise'] ?? 'France') ?>
                </span>
            </div>

            <div class="section-titre">Contact Référent</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom du contact :</label>
                    <span><?= val($demande['Nom_Referent_Entreprise']) ?></span>
                </div>
                <div class="champ">
                    <label>Fonction occupée :</label>
                    <span><?= val($demande['Fonction_Referent_Entreprise']) ?></span>
                </div>
            </div>
            <div class="champ">
                <label>Téléphone :</label>
                <span><?= val($demande['Telephone_Entreprise'], 'Non fourni') ?></span>
            </div>
            <div class="champ">
                <label>Adresse e-mail :</label>
                <span>
                    <a href="mailto:<?= urlencode($demande['Email_Contact_Entreprise'] ?? '') ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                        <?= htmlspecialchars($demande['Email_Contact_Entreprise'] ?? '') ?>
                    </a>
                </span>
            </div>

            <div class="section-titre">Détails de la sollicitation</div>
            <div class="champ">
                <label>Service demandé :</label>
                <span><strong><?= val($demande['Type_Demande_Service'], 'Général') ?></strong></span>
            </div>

            <?php if (!empty($message_client)): ?>
                <div class="section-titre">Message additionnel de l'entreprise</div>
                <div class="description-box"><?= htmlspecialchars(trim($message_client)) ?></div>
            <?php endif; ?>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <a href="<?= BASE_URL ?>/Administrateur.php" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 30px; background: #6c757d; color: white; border-radius: 4px; font-weight: bold; font-size: 0.9em;">← Retour au panneau Admin</a>
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