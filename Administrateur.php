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
            // Vérifier si cet utilisateur n'est pas déjà lié à un employé
            $stmt_check = $db->prepare("SELECT Id_Employe FROM Incarner WHERE Id_Utilisateur = ? LIMIT 1");
            $stmt_check->execute([$cand['Id_Utilisateur']]);
            $deja_lie = $stmt_check->fetch();

            if (!$deja_lie) {
                $stmt_insert = $db->prepare("
                    INSERT INTO Employe (
                        Nom_Employe, Prenom_Employe, Date_Naissance_Employe, Nationalite_Employe,
                        Telephone_Employe, Lieu_Naissance_Employe, Numero_CNAPS_Employe, Expiration_CNAPS_Employe,
                        Casier_Path_Employe, Date_Visite_Med_Employe, Permis_b_Employe, Vehicule_Employe,
                        Aptitude_Vue_Employe, Type_De_Contrat_Employe, Disponibilites_Employe, Mobilite_Rayon_Employe,
                        Port_Uniforme_Employe, CV_Path_Employe, Lettre_De_Motivation_Path_Employe, Date_Inscription_Employe
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
                ");

                $stmt_insert->execute([
                    $cand['Nom_Candidature'],
                    $cand['Prenom_Candidature'],
                    $cand['Date_Naissance_Candidature'],
                    $cand['Nationalite_Candidature'],
                    $cand['Telephone_Candidature'],
                    $cand['Lieu_Naissance_Candidature'],
                    $cand['Numero_CNAPS_Candidature'],
                    $cand['Expiration_CNAPS_Candidature'],
                    $cand['Casier_Path_Candidature'],
                    $cand['Date_Visite_Med_Candidature'],
                    $cand['Permis_b_Candidature'],
                    $cand['Vehicule_Candidature'],
                    $cand['Aptitude_Vue_Candidature'],
                    $cand['Type_Contrat_Candidature'],
                    $cand['Disponibilites_Candidature'],
                    $cand['Mobilite_Rayon_Candidature'],
                    $cand['Port_Uniforme_Candidature'],
                    $cand['CV_Path_Candidature'],
                    $cand['Lettre_Motivation_Candidature']
                ]);

                $id_employe = $db->lastInsertId();

                // Lier le nouvel employé à son compte utilisateur
                $stmt_link = $db->prepare("INSERT INTO Incarner (Id_Employe, Id_Utilisateur) VALUES (?, ?)");
                $stmt_link->execute([$id_employe, $cand['Id_Utilisateur']]);
                
                // Optionnel : Mettre à jour le rôle de l'utilisateur en 'employe' si nécessaire
                $stmt_role = $db->prepare("UPDATE Utilisateur SET Role = 'employe' WHERE Id_Utilisateur = ?");
                $stmt_role->execute([$cand['Id_Utilisateur']]);
            }

            $stmt_up = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Accepté' WHERE Id_Candidature = ?");
            $stmt_up->execute([$id_cand]);
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
        $stmt = $db->prepare("SELECT * FROM Candidature WHERE Id_Candidature = ?");
        $stmt->execute([$id_cand_ent]);
        $cand_ent = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cand_ent && $cand_ent['Statut_Candidature'] !== 'Accepté') {
            // Vérifier si cet utilisateur n'est pas déjà lié à une entreprise
            $stmt_check_ent = $db->prepare("SELECT Id_Entreprise FROM Representer WHERE Id_Utilisateur = ? LIMIT 1");
            $stmt_check_ent->execute([$cand_ent['Id_Utilisateur']]);
            $deja_lie_ent = $stmt_check_ent->fetch();

            if (!$deja_lie_ent) {
                $stmt_insert_ent = $db->prepare("
                    INSERT INTO Entreprise (
                        Nom_Entreprise, Siret_Entreprise, Code_NAF_Entreprise, Numero_TVA_Entreprise,
                        Telephone_Entreprise, Numero_voie_Entreprise, Nom_Voie_Entreprise, Complement_,
                        Ville_Entreprise, Pays_Entreprise, Nom_Referent_Entreprise, Fonction_Referent_Entreprise,
                        Email_Contact_Entreprise, Date_Creation_Inscription_Entreprise
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURDATE())
                ");

                $stmt_insert_ent->execute([
                    $cand_ent['Nom_Entreprise_Candidature'],
                    $cand_ent['Siret_Candidature'],
                    $cand_ent['Code_NAF_Candidature'],
                    $cand_ent['Numero_TVA_Candidature'],
                    $cand_ent['Telephone_Entreprise_Candidature'],
                    $cand_ent['Numero_Voie_Candidature'],
                    $cand_ent['Nom_Voie_Candidature'],
                    $cand_ent['Complement_Candidature'],
                    $cand_ent['Ville_Candidature'],
                    $cand_ent['Pays_Entreprise_Candidature'],
                    $cand_ent['Nom_Referent_Candidature'],
                    $cand_ent['Fonction_Referent_Candidature'],
                    $cand_ent['Email_Contact_Candidature']
                ]);

                $id_entreprise = $db->lastInsertId();

                // Lier la nouvelle entreprise à son compte utilisateur
                $stmt_link_ent = $db->prepare("INSERT INTO Representer (Id_Entreprise, Id_Utilisateur) VALUES (?, ?)");
                $stmt_link_ent->execute([$id_entreprise, $cand_ent['Id_Utilisateur']]);

                // Activer le compte utilisateur et mettre à jour son rôle en 'entreprise'
                $stmt_role_ent = $db->prepare("UPDATE Utilisateur SET Role = 'entreprise', Statut_Compte_Utilisateur = 'actif' WHERE Id_Utilisateur = ?");
                $stmt_role_ent->execute([$cand_ent['Id_Utilisateur']]);
            }

            $stmt_up_ent = $db->prepare("UPDATE Candidature SET Statut_Candidature = 'Accepté' WHERE Id_Candidature = ?");
            $stmt_up_ent->execute([$id_cand_ent]);
            $msg_admin = "L'entreprise a été approuvée et le compte activé !";
        }
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
 
    } elseif ($action_dem === 'Refuse') {
        $stmt = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = 'refusé' WHERE Id_Demande_Service = ?");
        $stmt->execute([$id_demande]);
        $msg_admin = "❌ La demande de service a été refusée.";
 
    } elseif ($action_dem === 'Accepte') {
 
        // 1. Récupérer les infos de la demande pour créer la Mission
        $stmt_info = $db->prepare("
            SELECT ds.*, e.Id_Entreprise
            FROM Demande_service ds
            JOIN Entreprise e ON ds.Id_Entreprise = e.Id_Entreprise
            WHERE ds.Id_Demande_Service = ?
        ");
        $stmt_info->execute([$id_demande]);
        $demande_info = $stmt_info->fetch(PDO::FETCH_ASSOC);
 
        if ($demande_info) {
            // 2. Trouver ou créer le Service correspondant dans la table Service
            $nom_service = $demande_info['Type_Demande_Service'] ?? 'Service divers';
 
            $stmt_serv = $db->prepare("SELECT Id_Service FROM Service WHERE Nom_Service = ? LIMIT 1");
            $stmt_serv->execute([$nom_service]);
            $service_row = $stmt_serv->fetch(PDO::FETCH_ASSOC);
 
            if ($service_row) {
                $id_service = $service_row['Id_Service'];
            } else {
                // Créer le service s'il n'existe pas encore
                $stmt_new_serv = $db->prepare("
                    INSERT INTO Service (Nom_Service, Description_Service, Date_Creation_Service)
                    VALUES (?, ?, CURDATE())
                ");
                $stmt_new_serv->execute([$nom_service, 'Service créé automatiquement depuis une demande entreprise.']);
                $id_service = $db->lastInsertId();
            }
 
            // 3. Créer la Mission dans la table Mission
            $titre_mission = $nom_service . ' — ' . ($demande_info['Nom_Entreprise'] ?? 'Entreprise');
 
            // Extraire le message client depuis le message compilé
            $message_brut  = $demande_info['Message_Demande_Service'] ?? '';
            $desc_mission  = '';
            $in_msg = false;
            foreach (explode("\n", $message_brut) as $ligne) {
                if ($in_msg) { $desc_mission .= $ligne . "\n"; continue; }
                if (trim($ligne) === '--- Message client ---') { $in_msg = true; }
            }
            $desc_mission = trim($desc_mission) ?: 'Mission créée depuis la sollicitation de service entreprise.';
 
            $stmt_mission = $db->prepare("
                INSERT INTO Mission (Titre_Mission, Description_Mission, Statut_Mission, Date_Creation_Mission, Id_Service, Id_Entreprise)
                VALUES (?, ?, 'disponible', CURDATE(), ?, ?)
            ");
            $stmt_mission->execute([
                $titre_mission,
                $desc_mission,
                $id_service,
                $demande_info['Id_Entreprise']
            ]);
 
            // 4. Mettre à jour le statut de la demande
            $stmt_up = $db->prepare("UPDATE Demande_service SET Statut_Demande_Service = 'accepté' WHERE Id_Demande_Service = ?");
            $stmt_up->execute([$id_demande]);
 
            $msg_admin = "✅ La demande a été acceptée et une mission a été créée. Les employés peuvent désormais y postuler.";
        }
    }
}
 

// D. TRAITEMENT DES POSTULATIONS OFFRES EMPLOYÉS
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

// E. TRAITEMENT DES MESSAGES DE CONTACT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_message'])) {
    $id_message = intval($_POST['id_message']);
    $action_msg = $_POST['action_message'];

    if ($action_msg === 'Non lu') {
        $stmt = $db->prepare("UPDATE Message SET Statut_Message = 'Non lu' WHERE Id_Message = ?");
        $stmt->execute([$id_message]);
        $msg_admin = "Le message a été remis en 'Non lu'.";
    } elseif ($action_msg === 'Lu') {
        $stmt = $db->prepare("UPDATE Message SET Statut_Message = 'Lu' WHERE Id_Message = ?");
        $stmt->execute([$id_message]);
        $msg_admin = "Le message a été marqué comme lu.";
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
$stmt_messages = $db->query("
    SELECT m.*, u.Email_Utilisateur AS Email_Compte_Utilisateur
    FROM Message m
    LEFT JOIN Utilisateur u ON m.Id_Utilisateur = u.Id_Utilisateur
    ORDER BY m.Date_Envoi_Message DESC
");
$liste_messages = $stmt_messages->fetchAll(PDO::FETCH_ASSOC);

// --- RÉCUPÉRATION DES MISSIONS DISPONIBLES ---
$missions = [];
try {
    // CORRIGÉ : Utilisation de $db et de $_SESSION['user_id']
    $stmt_missions = $db->query("
        SELECT m.*, s.Nom_Service, e.Nom_Entreprise,
               (SELECT COUNT(*) FROM Postuler p WHERE p.Id_Mission = m.Id_Mission AND p.Id_Employe = " . intval($_SESSION['user_id']) . ") as deja_postule
        FROM Mission m
        JOIN Service s ON m.Id_Service = s.Id_Service
        JOIN Entreprise e ON m.Id_Entreprise = e.Id_Entreprise
        WHERE m.Statut_Mission = 'disponible'
        ORDER BY m.Date_Creation_Mission DESC
    ");
    $missions = $stmt_missions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silencieux si la table n'existe pas encore
}
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
                                <td>
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
                                    <br><span style="font-size:0.8em; color:#666;">Email : <?= htmlspecialchars($pe['Email_Contact_Candidature'] ?? $pe['Email_Utilisateur'] ?? 'Non renseigné') ?></span>
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
                                    <a href="VerifIdSollicitation.php?id=<?= $dem['Id_Demande_Service'] ?>" style="font-weight:bold; color:#e67e22; text-decoration:none;">
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

        <section class="container">
            <h2>Messages de contact</h2>
            <table>
                <thead>
                    <tr>
                        <th>Expéditeur (Cliquez pour voir le message)</th>
                        <th>Date d'envoi</th>
                        <th colspan="2" style="text-align:center;">Statut / Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($liste_messages)): ?>
                        <tr><td colspan="4" style="text-align:center;">Aucun message reçu pour le moment.</td></tr>
                    <?php else: ?>
                        <?php foreach ($liste_messages as $msg): ?>
                            <?php
                                $statut_msg = $msg['Statut_Message'] ?? 'Non lu';
                                $couleur_msg_ligne = $statut_msg === 'Non lu' ? 'background-color:#fffdf0;' : 'background-color:#f0fff4;';
                            ?>
                            <tr style="<?= $couleur_msg_ligne ?>">
                                <td>
                                    <a href="VerifMessage.php?id=<?= $msg['Id_Message'] ?>" style="font-weight:bold; color:#007bff; text-decoration:none;">
                                        <?= htmlspecialchars($msg['Prenom_Message'] . ' ' . $msg['Nom_Message']) ?>
                                    </a>
                                    <br><span style="font-size:0.8em; color:#666;">Email : <?= htmlspecialchars($msg['Email_Message']) ?></span>
                                    <?php if (!empty($msg['Email_Compte_Utilisateur'])): ?>
                                        <br><span style="font-size:0.8em; color:#28a745;">Compte lié : <?= htmlspecialchars($msg['Email_Compte_Utilisateur']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($msg['Date_Envoi_Message']))) ?></td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_message" value="<?= $msg['Id_Message'] ?>">
                                        <button type="submit" name="action_message" value="Non lu"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_msg === 'Non lu' ? '#ffc107; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Non lu
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    <form method="post" style="display:inline;">
                                        <input type="hidden" name="id_message" value="<?= $msg['Id_Message'] ?>">
                                        <button type="submit" name="action_message" value="Lu"
                                            style="cursor:pointer; padding:5px 10px; background-color:<?= $statut_msg === 'Lu' ? '#28a745; color:white; font-weight:bold;' : '#f8f9fa;' ?>">
                                            Lu
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