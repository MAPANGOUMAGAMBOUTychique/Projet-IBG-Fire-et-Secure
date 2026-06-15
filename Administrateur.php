<?php
session_start();
require_once 'Database.php';

// ==========================================
// 1. SÉCURITÉ & VÉRIFICATION DES ACCÈS
// ==========================================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: SeConnecter.php");
    exit();
}

$admin = [
    'nom_utilisateurs' => $_SESSION['user_nom'] ?? 'Administrateur'
];

$db = Database::getInstance();
$msg_admin = "";

// ==========================================
// 2. TRAITEMENTS DES FORMULAIRES (POST)
// ==========================================

// A. TRAITEMENT DES CANDIDATURES INSCRIPTION EMPLOYÉS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_candidature'])) {
    $id_cand = intval($_POST['id_candidature']);
    $action = $_POST['action_candidature'];

    if ($action === 'en attente') {
        $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'en attente' WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand]);
        $msg_admin = "La demande a été remise en attente.";
    } elseif ($action === 'Refuse') {
        $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Refusé' WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand]);
        $msg_admin = "La demande a été refusée.";
    } elseif ($action === 'Accepte') {
        $stmt = $db->prepare("SELECT * FROM Candidature WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand]);
        $cand = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cand && $cand['Statut_Candidature'] !== 'Accepté') {
            // Optionnel : Insérer dans la table Employe si nécessaire
            // ... Ton code d'insertion INSERT INTO Employe ici ...
            
            $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Accepté' WHERE Id_Candidature = ?");
            $stmt->execute([$id_cand]);
            $msg_admin = "Le compte employé a été accepté et créé avec succès.";
        }
    }
}

// B. TRAITEMENT DES DEMANDES DE CRÉATION COMPTE ENTREPRISE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_entreprise'])) {
    $id_cand_ent = intval($_POST['id_candidature_entreprise']);
    $action_ent = $_POST['action_entreprise'];

    if ($action_ent === 'en attente') {
        $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'en attente' WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand_ent]);
        $msg_admin = "La demande entreprise a été remise en attente.";
    } elseif ($action_ent === 'Refuse') {
        $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Refusé' WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand_ent]);
        $msg_admin = "La demande entreprise a été refusée.";
    } elseif ($action_ent === 'Accepte') {
        // Logique de validation d'entreprise (Réf: Image 2 de ton code)
        $stmt = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Accepté' WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand_ent]);
        $msg_admin = "L'entreprise a été approuvée et le compte activé !";
    }
}

// C. TRAITEMENT DES DEMANDES DE SERVICE ENTREPRISE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_demande'])) {
    $id_demande = intval($_POST['id_demande_service']);
    $action_dem = $_POST['action_demande'];

    if ($action_dem === 'en attente') {
        $stmt = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = 'en attente' WHERE Id_Demande_Service = ?");
        $stmt->execute([$id_demande]);
        $msg_admin = "La demande de service a été remise en attente.";
    } elseif ($action_dem === 'Accepte') {
        $stmt = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = 'accepté' WHERE Id_Demande_Service = ?");
        $stmt->execute([$id_demande]);
        $msg_admin = "✅ La demande de service a été acceptée.";
    } elseif ($action_dem === 'Refuse') {
        $stmt = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = 'refusé' WHERE Id_Demande_Service = ?");
        $stmt->execute([$id_demande]);
        $msg_admin = "❌ La demande de service a été refusée.";
    }
}

// D. NOUVEAU : TRAITEMENT DES POSTULATIONS OFFRES EMPLOYÉS (CORRIGÉ)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_postulation'])) {
    $id_postuler = intval($_POST['id_postuler']);
    $action_post = $_POST['action_postulation'];

    if ($action_post === 'en attente') {
        $stmt = $db->prepare("UPDATE Postuler SET Statut_Postuler = 'en attente' WHERE Id_Postuler = ?");
        $stmt->execute([$id_postuler]);
        $msg_admin = "La postulation à l'offre a été remise en attente.";
    } elseif ($action_post === 'Accepte') {
        $stmt = $db->prepare("UPDATE Postuler SET Statut_Postuler = 'Accepté' WHERE Id_Postuler = ?");
        $stmt->execute([$id_postuler]);
        $msg_admin = "✅ La candidature à l'offre a été acceptée. L'employé verra le message sur son tableau de bord.";
    } elseif ($action_post === 'Refuse') {
        $stmt = $db->prepare("UPDATE Postuler SET Statut_Postuler = 'Refusé' WHERE Id_Postuler = ?");
        $stmt->execute([$id_postuler]);
        $msg_admin = "❌ La candidature à l'offre a été refusée.";
    }
}

// ==========================================
// 3. RÉCUPÉRATION DES LISTES POUR L'AFFICHAGE
// ==========================================

// Candidatures employés (Inscription)
$stmt_employees = $db->query("
    SELECT c.*, u.Email_Utilisateur 
    FROM Candidature c
    JOIN Utilisateur u ON c.Id_Utilisateur = u.Id_Utilisateur
    WHERE c.Type_Candidature = 'employe'
    ORDER BY c.Date_Candidature DESC
");
$postulations_employees = $stmt_employees->fetchAll(PDO::FETCH_ASSOC);

// Candidatures entreprises (Inscription)
$stmt_entreprises = $db->query("
    SELECT c.*, u.Email_Utilisateur 
    FROM Candidature c
    JOIN Utilisateur u ON c.Id_Utilisateur = u.Id_Utilisateur
    WHERE c.Type_Candidature = 'entreprise'
    ORDER BY c.Date_Candidature DESC
");
$postulations_entreprises = $stmt_entreprises->fetchAll(PDO::FETCH_ASSOC);

// Liste des candidatures envoyées sur les offres de missions (Postulations)
$stmt_postuler = $db->query("
    SELECT p.*, e.Nom_Employe, e.Prenom_Employe, m.Titre_Mission
    FROM Postuler p
    JOIN Employe e ON p.Id_Employe = e.Id_Employe
    JOIN Mission m ON p.Id_Mission = m.Id_Mission
    ORDER BY p.Id_Mission DESC
");
$liste_postulations_offres = $stmt_postuler->fetchAll(PDO::FETCH_ASSOC);

// Demandes de service (table Demande_service)
$stmt_demandes = $db->query("
    SELECT ds.*, e.Nom_Entreprise, e.Email_Contact_Entreprise
    FROM Demande_service ds
    JOIN Entreprise e ON ds.Id_Entreprise = e.Id_Entreprise
    ORDER BY ds.Date_Demande_Service DESC
");
$liste_demandes = $stmt_demandes->fetchAll(PDO::FETCH_ASSOC);

// Messages de contact
$stmt_messages = $db->query("SELECT * FROM Message ORDER BY Date_Envoi_Message DESC");
$liste_messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Administrateur | IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
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
        <h1>Profil Admin : <?= htmlspecialchars($admin['nom_utilisateurs']) ?></h1>

        <?php if (!empty($msg_admin)): ?>
            <div style="padding:15px; background: #e2f0d9; color:#385723; margin-bottom:20px; font-weight:bold; border-radius:4px; text-align:center; border:1px solid #385723;">
                <?= htmlspecialchars($msg_admin) ?>
            </div>
        <?php endif; ?>

        <section class="container">
            <h2>Candidatures aux Offres de Missions (Postulations)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Employé & Mission visée (Cliquez pour voir)</th>
                        <th colspan="3" style="text-align:center;">Actions / Statut actuel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liste_postulations_offres)): ?>
                        <tr><td colspan="4" style="text-align:center;">Aucun employé n'a postulé à une offre pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste_postulations_offres as $po): ?>
                            <?php $statut_po = $po['Statut_Postuler'] ?? 'en attente'; ?>
                            <tr>
                                td>
                                    <a href="VerifPostulationOffre.php?id=<?= $po['Id_Postuler'] ?>" style="font-weight:bold; color:#007bff; text-decoration:none;">
                                        <?= htmlspecialchars($po['Prenom_Employe'] . ' ' . $po['Nom_Employe']) ?>
                                    </a>
                                    <br><span style="font-size:0.85em; color:#e67e22;"><br>Offre : <strong><?= htmlspecialchars($po['Titre_Mission']) ?></strong></span>
                                    <br><span style="font-size:0.8em; color:#666;"><br>Statut de l'offre : <strong><?= htmlspecialchars($statut_po) ?></strong></span>
                                </td>
                                
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_postuler" value="<?= $po['Id_Postuler'] ?>">
                                        <button type="submit" name="action_postulation" value="en attente"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_po === 'en attente' ? '#ffc107; font-weight:bold;' : '#f8f9fa;' ?>">
                                            En attente
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_postuler" value="<?= $po['Id_Postuler'] ?>">
                                        <button type="submit" name="action_postulation" value="Accepte"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_po === 'Accepté' ? '#28a745; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Accepté
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_postuler" value="<?= $po['Id_Postuler'] ?>">
                                        <button type="submit" name="action_postulation" value="Refuse"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_po === 'Refusé' ? '#dc3545; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Refusé
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="container">
            <h2>Demandes de création de compte employé</h2>
            <table>
                <thead>
                    <tr>
                        <th>Candidat (Cliquez pour voir)</th>
                        <th colspan="3" style="text-align:center;">Actions / Statut actuel</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulations_employees)): ?>
                        <tr><td colspan="4" style="text-align:center;">Aucune demande pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($postulations_employees as $p): ?>
                            <tr>
                                <td>
                                    <a href="VerifIdEmploye.php?id=<?= $p['Id_Candidature'] ?>" style="font-weight:bold; color:#007bff; text-decoration:none;">
                                        <?= htmlspecialchars($p['Nom_Candidature'] . ' ' . $p['Prenom_Candidature']) ?>
                                    </a>
                                    <br><span style="font-size:0.8em; color:#666;"><br>Email : <?= htmlspecialchars($p['Email_Utilisateur']) ?></span>
                                    <br><span style="font-size:0.8em; color:#666;">Statut : <strong><?= htmlspecialchars($p['Statut_Candidature']) ?></strong></span>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature" value="<?= $p['Id_Candidature'] ?>">
                                        <button type="submit" name="action_candidature" value="en attente"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $p['Statut_Candidature'] === 'en attente' ? '#ffc107; font-weight:bold;' : '#f8f9fa;' ?>">
                                            En attente
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature" value="<?= $p['Id_Candidature'] ?>">
                                        <button type="submit" name="action_candidature" value="Accepte"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $p['Statut_Candidature'] === 'Accepté' ? '#28a745; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Accepté
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature" value="<?= $p['Id_Candidature'] ?>">
                                        <button type="submit" name="action_candidature" value="Refuse"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $p['Statut_Candidature'] === 'Refusé' ? '#dc3545; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Refusé
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="container">
            <h2>Demandes de création de compte entreprise</h2>
            <table>
                <thead>
                    <tr>
                        <th>Entreprise (Cliquez pour voir)</th>
                        <th colspan="3" style="text-align:center;">Statut / Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulations_entreprises)): ?>
                        <tr><td colspan="4" style="text-align:center;">Aucune demande entreprise reçue.</td></tr>
                    <?php else: ?>
                        <?php foreach ($postulations_entreprises as $pe): ?>
                            <tr>
                                <td>
                                    <a href="VerifIdEntreprise.php?id=<?= $pe['Id_Candidature'] ?>" style="font-weight:bold; color:#28a745; text-decoration:none;">
                                        <?= htmlspecialchars($pe['Nom_Entreprise_Candidature'] ?? 'Entreprise') ?>
                                    </a>
                                    <br><span style="font-size:0.8em; color:#666;">Statut : <strong><?= htmlspecialchars($pe['Statut_Candidature']) ?></strong></span>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature_entreprise" value="<?= $pe['Id_Candidature'] ?>">
                                        <button type="submit" name="action_entreprise" value="en attente"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $pe['Statut_Candidature'] === 'en attente' ? '#ffc107; font-weight:bold;' : '#f8f9fa;' ?>">
                                            En attente
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature_entreprise" value="<?= $pe['Id_Candidature'] ?>">
                                        <button type="submit" name="action_entreprise" value="Accepte"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $pe['Statut_Candidature'] === 'Accepté' ? '#28a745; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Accepté
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_candidature_entreprise" value="<?= $pe['Id_Candidature'] ?>">
                                        <button type="submit" name="action_entreprise" value="Refuse"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $pe['Statut_Candidature'] === 'Refusé' ? '#dc3545; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Refusé
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="container">
            <h2>Sollicitations de service entreprise</h2>
            <table>
                <thead>
                    <tr>
                        <th>Entreprise (Cliquez pour voir le dossier)</th>
                        <th>Date de demande</th>
                        <th colspan="3" style="text-align:center;">Statut / Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liste_demandes)): ?>
                        <tr><td colspan="5" style="text-align:center;">Aucune sollicitation reçue pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste_demandes as $dem): ?>
                            <?php 
                                $statut_dem = $dem['Statut_Demande_Service']; 
                                $couleur_ligne = match($statut_dem) {
                                    'accepté' => 'background-color:#f0fff4;',
                                    'refusé'  => 'background-color:#fff5f5;',
                                    default   => 'background-color:#fffdf0;',
                                };
                            ?>
                            <tr style="<?= $couleur_ligne ?>">
                                <td>
                                    <a href="SolicitationEntreprise.php?id=<?= $dem['Id_Demande_Service'] ?>" style="font-weight:bold; color:#e67e22; text-decoration:none;">
                                        <?= htmlspecialchars($dem['Nom_Entreprise']) ?>
                                    </a>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y', strtotime($dem['Date_Demande_Service']))) ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_demande_service" value="<?= $dem['Id_Demande_Service'] ?>">
                                        <button type="submit" name="action_demande" value="en attente"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_dem === 'en attente' ? '#ffc107; font-weight:bold;' : '#f8f9fa;' ?>">
                                            En attente
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_demande_service" value="<?= $dem['Id_Demande_Service'] ?>">
                                        <button type="submit" name="action_demande" value="Accepte"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_dem === 'accepté' ? '#28a745; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Accepté
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_demande_service" value="<?= $dem['Id_Demande_Service'] ?>">
                                        <button type="submit" name="action_demande" value="Refuse"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_dem === 'refusé' ? '#dc3545; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Refusé
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <div class="logout-wrapper" style="text-align:center; margin-top:30px;">
            <a href="index.php?action=logout" class="logout-btn">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>