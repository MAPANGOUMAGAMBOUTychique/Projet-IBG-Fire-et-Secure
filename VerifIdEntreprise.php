<?php
session_start();
require_once 'Database.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sécurité : Vérification de la présence des variables et tolérance "Admin" / "admin"
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || strtolower(trim($_SESSION['user_role'])) !== 'admin') {
    header("Location: SeConnecter.php");
    exit();
}

$db = Database::getInstance();
$id_candidature = intval($_GET['id'] ?? 0);

if (!$id_candidature) {
    die("
        <div style='font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <h2 style='color:#dc3545;'>❌ Identifiant introuvable</h2>
            <p style='color:#555; margin-bottom: 20px;'>Aucun identifiant de candidature n'a été transmis dans l'URL.</p>
            <a href='Administrateur.php' style='display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

// Récupérer la candidature entreprise + l'email du compte utilisateur lié
$stmt = $db->prepare("
    SELECT c.*, u.Email_Utilisateur
    FROM Candidature c
    JOIN Utilisateur u ON c.Id_Utilisateur = u.Id_Utilisateur
    WHERE c.Id_Candidature = ? AND c.Type_Candidature = 'entreprise'
");
$stmt->execute([$id_candidature]);
$candidature = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$candidature) {
    die("
        <div style='font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <h2 style='color:#dc3545;'>❌ Entreprise introuvable</h2>
            <p style='color:#555; margin-bottom: 20px;'>La candidature d'entreprise spécifiée n'existe pas ou n'est pas de type adéquat.</p>
            <a href='Administrateur.php' style='display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>Retourner à l'administration</a>
        </div>
    ");
}

function statut_class(string $statut): string {
    return match(strtolower(trim($statut))) {
        'accepté', 'accepte' => 'accepte',
        'refusé', 'refuse'   => 'refuse',
        default              => 'en-attente',
    };
}

function val(mixed $v, string $fallback = 'Non renseigné'): string {
    return htmlspecialchars(!empty($v) ? $v : $fallback);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Dossier candidature entreprise | IBG FIRE ET SECURE</title>
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
            color: #28a745;
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
        .statut-en-attente { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .statut-accepte    { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .statut-refuse     { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

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
        .description-box.service { border-left-color: #28a745; }

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
            <h2>Dossier de Candidature Entreprise</h2>

            <div class="section-titre">Statut de la candidature</div>
            <div class="champ">
                <label>État actuel :</label>
                <span class="statut-badge statut-<?= statut_class($candidature['Statut_Candidature']) ?>">
                    <?= ucfirst(htmlspecialchars($candidature['Statut_Candidature'])) ?>
                </span>
            </div>
            <div class="champ">
                <label>Date de la candidature :</label>
                <span><?= !empty($candidature['Date_Candidature']) ? htmlspecialchars(date('d/m/Y', strtotime($candidature['Date_Candidature']))) : 'Non renseignée' ?></span>
            </div>

            <div class="section-titre">Informations Entreprise</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom de l'entreprise :</label>
                    <span><?= val($candidature['Nom_Entreprise_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>N° SIRET :</label>
                    <span><?= val($candidature['Siret_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Code NAF / TVA :</label>
                    <span><?= val($candidature['Code_NAF_Candidature']) ?> / <?= val($candidature['Numero_TVA_Candidature']) ?></span>
                </div>
                <div class="champ">
                    <label>Téléphone entreprise :</label>
                    <span><?= val($candidature['Telephone_Entreprise_Candidature'], 'Non fourni') ?></span>
                </div>
                <div class="champ" style="grid-column: 1 / -1;">
                    <label>Adresse :</label>
                    <span>
                        <?= htmlspecialchars(
                            trim(($candidature['Numero_Voie_Candidature'] ?? '') . ' ' .
                            ($candidature['Nom_Voie_Candidature'] ?? '') . ' ' .
                            ($candidature['Complement_Candidature'] ?? ''))
                        ) ?>, <br>
                        <?= val($candidature['Ville_Candidature']) ?> (<?= val($candidature['Pays_Entreprise_Candidature']) ?>)
                    </span>
                </div>
            </div>

            <div class="section-titre">Contact Référent</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Nom du contact :</label>
                    <span><?= htmlspecialchars(trim(($candidature['Nom_Referent_Candidature'] ?? 'Non renseigné') . ' (' . ($candidature['Fonction_Referent_Candidature'] ?? 'Non renseigné') . ')')) ?></span>
                </div>
                <div class="champ">
                    <label>Email de contact :</label>
                    <span>
                        <?php if (!empty($candidature['Email_Contact_Candidature'])): ?>
                            <a href="mailto:<?= htmlspecialchars($candidature['Email_Contact_Candidature']) ?>"><?= htmlspecialchars($candidature['Email_Contact_Candidature']) ?></a>
                        <?php else: ?>
                            Non renseigné
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <div class="section-titre">Compte utilisateur lié</div>
            <div class="grid-2">
                <div class="champ">
                    <label>Email de connexion :</label>
                    <span><a href="mailto:<?= htmlspecialchars($candidature['Email_Utilisateur']) ?>"><?= htmlspecialchars($candidature['Email_Utilisateur']) ?></a></span>
                </div>
                <div class="champ">
                    <label>Id Utilisateur :</label>
                    <span>#<?= htmlspecialchars($candidature['Id_Utilisateur']) ?></span>
                </div>
            </div>

            <?php if (!empty($candidature['Lettre_Motivation_Candidature'])): ?>
                <div class="section-titre">Lettre de motivation / Message</div>
                <div class="champ" style="flex-direction: column; gap: 4px;">
                    <div class="description-box service"><?= htmlspecialchars(trim($candidature['Lettre_Motivation_Candidature'])) ?></div>
                </div>
            <?php endif; ?>

            <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px; text-align: center;">
                <a href="Administrateur.php" style="display: inline-block; text-decoration: none; width: auto; padding: 10px 30px; background: #6c757d; color: white; border-radius: 4px; font-weight: bold; font-size: 0.95em;">← Retour au panneau Admin</a>
            </div>
        </div>
    </main>

    <footer>
        <ul>
            <li><a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a></li>
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
                        <li><a href="#SecuriteEtIncendie">Sécurité et Incendie</a></li>
                        <li><a href="#GardiennageEtSurveillance">Gardiennage et Surveillance</a></li>
                        <li><a href="#ConseilEtExpertise">Conseil et Expertise</a></li>
                    </ul>
                </article>
            </li>
            <li>
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
                        <li><a href="SeConnecter.php">Se connecter</a></li>
                        <li><a href="CreerUnCompte.php">Créer un compte</a></li>
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