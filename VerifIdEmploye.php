<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('pcre.jit', 0); // Correctif pour l'allocation de mémoire JIT

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

// 1. Sécurité : Admin uniquement
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// Vérification de la présence de l'identifiant
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die('
        <div style="font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="color:#dc3545;">❌ Identifiant introuvable</h2>
            <p style="color:#555; margin-bottom: 20px;">L\'identifiant de la candidature est manquant dans l\'URL.</p>
            <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
        </div>
    ');
}

try {
    require_once 'Database.php';
    $db = Database::getInstance();

    $stmt = $db->prepare("
        SELECT c.*, u.Email_Utilisateur
        FROM Candidature c
        JOIN Utilisateur u ON c.Id_Utilisateur = u.Id_Utilisateur
        WHERE c.Id_Candidature = ?
          AND c.Type_Candidature = 'employe'
    ");
    $stmt->execute([intval($_GET['id'])]);
    $cand = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$cand) {
        die('
            <div style="font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
                <h2 style="color:#ffc107;">⚠️ Introuvable</h2>
                <p style="color:#555; margin-bottom: 20px;">Aucune candidature employé ne correspond à l\'ID ' . htmlspecialchars($_GET['id']) . '.</p>
                <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
            </div>
        ');
    }

} catch (Throwable $e) {
    die('
        <div style="font-family: Arial, sans-serif; text-align:left; max-width: 600px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);">
            <h2 style="color:#dc3545; margin-top:0;">💥 Erreur fatale technique</h2>
            <p style="color:#555;"><strong>Message :</strong> ' . htmlspecialchars($e->getMessage()) . '</p>
            <p style="color:#555;"><strong>Fichier :</strong> ' . htmlspecialchars($e->getFile()) . '</p>
            <p style="color:#555;"><strong>Ligne :</strong> ' . $e->getLine() . '</p>
            <div style="text-align: center; margin-top: 20px;">
                <a href="' . BASE_URL . '/Administrateur.php" style="display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;">Retourner à l\'administration</a>
            </div>
        </div>
    ');
}

// Fonctions helpers
function val($v, string $fallback = 'Non renseigné'): string {
    return htmlspecialchars(!empty($v) ? $v : $fallback);
}

function date_fr(?string $d): string {
    return (!empty($d) && $d !== '0000-00-00') ? htmlspecialchars(date('d/m/Y', strtotime($d))) : 'Non renseignée';
}

/**
 * Génère une URL propre et sécurisée pour les documents joints
 */
function generer_lien_doc(string $path_bdd): string {
    $path_nettoye = ltrim(trim($path_bdd), '/');
    $segments = explode('/', $path_nettoye);
    if (!empty($segments)) {
        $dernier_index = count($segments) - 1;
        $segments[$dernier_index] = rawurlencode($segments[$dernier_index]);
    }
    $path_encode = implode('/', $segments);
    return BASE_URL . '/' . $path_encode;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dossier Employé - <?= val($cand['Prenom_Candidature'] . ' ' . $cand['Nom_Candidature']) ?> | IBG FIRE ET SECURE</title>
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
        
        .doc-container {
            display: flex;
            gap: 10px;
            margin-top: 5px;
            flex-wrap: wrap;
        }
        .doc-btn {
            display: inline-block;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            font-size: 0.9em;
            text-align: center;
        }
        .doc-btn.cv  { background: #007bff; color: white; }
        .doc-btn.lm  { background: #6c757d; color: white; }
        .doc-btn.casier { background: #dc3545; color: white; }
        .doc-absent { color: #999; font-style: italic; font-size: 0.9em; }
        
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
            <h2>Dossier de Création de Compte Employé</h2>

            <div class="section-titre">Statut du dossier</div>
            <?php
                $statut = strtolower(trim($cand['Statut_Candidature'] ?? ''));
                $badge_class = match(true) {
                    str_contains($statut, 'accept') => 'badge-accepte',
                    str_contains($statut, 'refus')  => 'badge-refuse',
                    default                          => 'badge-attente',
                };
            ?>
            <div class="champ">
                <label>État actuel :</label>
                <span class="statut-badge <?= $badge_class ?>">
                    <?= ucfirst(val($cand['Statut_Candidature'])) ?>
                </span>
            </div>
            <div class="champ">
                <label>Date de candidature :</label>
                <span><?= date_fr($cand['Date_Candidature']) ?></span>
            </div>

            <div class="section-titre">Identité</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom :</label>
                    <span><?= val($cand['Nom_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Prénom :</label>
                    <span><?= val($cand['Prenom_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Date de naissance :</label>
                    <span><?= date_fr($cand['Date_Naissance_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Lieu de naissance :</label>
                    <span><?= val($cand['Lieu_Naissance_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Nationalité :</label>
                    <span><?= val($cand['Nationalite_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Téléphone :</label>
                    <span><?= val($cand['Telephone_Candidature']) ?></span>
                </div>
            </div>
            <div class="champ">
                <label>Adresse e-mail :</label>
                <span>
                    <a href="mailto:<?= htmlspecialchars($cand['Email_Utilisateur']) ?>" style="color: #007bff; text-decoration: none; font-weight: bold;">
                        <?= htmlspecialchars($cand['Email_Utilisateur']) ?>
                    </a>
                </span>
            </div>

            <div class="section-titre">Informations professionnelles</div>
            <div class="grid-2">
                <div class="champ">
                    <label>N° CNAPS :</label>
                    <span><?= val($cand['Numero_CNAPS_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Expiration CNAPS :</label>
                    <span><?= date_fr($cand['Expiration_CNAPS_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Dernière visite médicale :</label>
                    <span><?= date_fr($cand['Date_Visite_Med_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Aptitude visuelle :</label>
                    <span><?= val($cand['Aptitude_Vue_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Type de contrat souhaité :</label>
                    <span><?= val($cand['Type_Contrat_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Rayon de mobilité :</label>
                    <span><?= !empty($cand['Mobilite_Rayon_Candidature']) ? htmlspecialchars($cand['Mobilite_Rayon_Candidature']) . ' km' : 'Non renseigné' ?></span>
                </div>
                <div class="champ">
                    <label>Permis B :</label>
                    <span><?= val($cand['Permis_b_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Véhicule :</label>
                    <span><?= val($cand['Vehicule_Candidature']) ?></span>
                </div>
            </div>
            <div class="champ">
                <label>Port de l'uniforme :</label>
                <span><?= val($cand['Port_Uniforme_Candidature']) ?></span>
            </div>

            <div class="section-titre">Disponibilités</div>
            <div class="description-box">
                <?= nl2br(htmlspecialchars($cand['Disponibilites_Candidature'] ?? 'Aucune disponibilité saisie.')) ?>
            </div>

            <div class="section-titre">Pièces justificatives</div>
            <div class="champ" style="flex-direction: column; gap: 4px;">
                <label>Documents transmis :</label>
                <div class="doc-container">
                    <?php if (!empty($cand['CV_Path_Candidature'])): ?>
                        <a href="<?= generer_lien_doc($cand['CV_Path_Candidature']) ?>" target="_blank" class="doc-btn cv">📄 Consulter le CV</a>
                    <?php else: ?>
                        <span class="doc-absent">Aucun CV fourni</span>
                    <?php endif; ?>

                    <?php if (!empty($cand['Lettre_Motivation_Candidature'])): ?>
                        <a href="<?= generer_lien_doc($cand['Lettre_Motivation_Candidature']) ?>" target="_blank" class="doc-btn lm">✉️ Lettre de motivation</a>
                    <?php else: ?>
                        <span class="doc-absent">Aucune lettre fournie</span>
                    <?php endif; ?>

                    <?php if (!empty($cand['Casier_Path_Candidature'])): ?>
                        <a href="<?= generer_lien_doc($cand['Casier_Path_Candidature']) ?>" target="_blank" class="doc-btn casier">📋 Casier Judiciaire</a>
                    <?php else: ?>
                        <span class="doc-absent">Aucun casier fourni</span>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <a href="<?= BASE_URL ?>/Administrateur.php" class="btn-submit" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 30px;">← Retour au panneau Admin</a>
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