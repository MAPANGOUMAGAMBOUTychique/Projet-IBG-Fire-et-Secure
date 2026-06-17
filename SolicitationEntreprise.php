<?php
// =========================================================================
// 1. GESTION DE LA SESSION ET CONFIGURATION DE SÉCURITÉ
// =========================================================================

// Vérifie si une session est déjà active. Si non, on la démarre pour accéder aux variables $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe Database pour la connexion PDO (Pattern Singleton)
require_once 'Database.php';

// Configuration de l'affichage des erreurs pour faciliter le débogage en environnement de développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définition de l'URL racine pour garantir que les liens (CSS, images) fonctionnent partout
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure');

/**
 * VÉRIFICATION D'ACCÈS :
 * On vérifie si l'utilisateur est connecté, s'il a le rôle 'entreprise' (insensible à la casse) 
 * et si son identifiant entreprise est présent en session.
 */
if (!isset($_SESSION['user_id']) || 
    !isset($_SESSION['user_role']) || 
    strtolower(trim($_SESSION['user_role'])) !== 'entreprise' || 
    !isset($_SESSION['entreprise_id'])) {
    
    // Si l'utilisateur n'est pas autorisé, redirection forcée vers la page de connexion
    header("Location: SeConnecter.php");
    exit();
}

// Récupération de l'instance de connexion et sécurisation de l'ID entreprise (cast en entier)
$db = Database::getInstance();
$id_entreprise = intval($_SESSION['entreprise_id']);

// Initialisation des drapeaux pour les messages à l'utilisateur
$message_succes = false;
$erreur = "";

// =========================================================================
// 2. RÉCUPÉRATION DES INFOS DE L'ENTREPRISE
// =========================================================================

// On récupère les données de l'entreprise connectée pour pré-remplir le formulaire (ex: email)
$stmt_ent = $db->prepare("SELECT * FROM Entreprise WHERE Id_Entreprise = ?");
$stmt_ent->execute([$id_entreprise]);
$entreprise = $stmt_ent->fetch(PDO::FETCH_ASSOC);

// Cas de sécurité : Si l'ID en session ne correspond à rien en BDD
if (!$entreprise) {
    die("
        <div style='font-family: Arial, sans-serif; text-align:center; max-width: 500px; margin: 60px auto; padding: 20px; background: #fff; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05);'>
            <h2 style='color:#dc3545;'>❌ Entreprise introuvable</h2>
            <p style='color:#555; margin-bottom: 20px;'>Votre fiche entreprise n'a pas pu être retrouvée.</p>
            <a href='SeConnecter.php' style='display:inline-block; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:4px; font-weight:bold;'>Se reconnecter</a>
        </div>
    ");
}

/**
 * RÉCUPÉRATION DU SERVICE :
 * On cherche le type de service demandé. Priorité au paramètre URL (GET), 
 * puis au champ masqué (POST), sinon valeur par défaut.
 */
$type_service = trim($_GET['service'] ?? $_POST['user_type_service'] ?? 'Service divers');

// =========================================================================
// 3. LOGIQUE DE TRAITEMENT DU FORMULAIRE (RÉCEPTION POST)
// =========================================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Nettoyage des entrées utilisateurs (trim) pour supprimer les espaces inutiles
    $email_demandeur = trim($_POST['user_email'] ?? $entreprise['Email_Contact_Entreprise'] ?? '');
    $adresse_site     = trim($_POST['user_adresse_site'] ?? '');
    $duree_mission    = trim($_POST['user_duree_mission'] ?? '');
    $date_debut       = trim($_POST['user_date_debut'] ?? '');
    $date_fin         = trim($_POST['user_date_fin'] ?? '');
    $message_client   = trim($_POST['user_message'] ?? '');

    // --- VALIDATIONS ---
    if (empty($email_demandeur) || !filter_var($email_demandeur, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Merci de renseigner une adresse email valide.";
    } elseif (empty($adresse_site)) {
        $erreur = "L'adresse du site d'intervention est obligatoire.";
    } elseif (empty($message_client)) {
        $erreur = "Merci de préciser votre besoin dans le message.";
    } elseif (!empty($date_debut) && !empty($date_fin) && strtotime($date_fin) < strtotime($date_debut)) {
        // Sécurité logique : la fin ne peut pas être avant le début
        $erreur = "La date de fin ne peut pas être antérieure à la date de début.";
    }

    // --- ENREGISTREMENT ---
    if (empty($erreur)) {
        try {
            /**
             * COMPILATION DU MESSAGE :
             * On fusionne les détails spécifiques (adresse, dates) dans un seul champ texte 'message' 
             * pour simplifier la lecture par l'administrateur dans son back-office.
             */
            $message_compile  = "Type de service : " . $type_service . "\n";
            $message_compile .= "Adresse du site : " . $adresse_site . "\n";
            if (!empty($duree_mission)) { $message_compile .= "Durée de la mission : " . $duree_mission . "\n"; }
            if (!empty($date_debut))    { $message_compile .= "Date de début souhaitée : " . $date_debut . "\n"; }
            if (!empty($date_fin))      { $message_compile .= "Date de fin envisagée : " . $date_fin . "\n"; }
            $message_compile .= "--- Message client ---\n";
            $message_compile .= $message_client;

            // Préparation de la requête d'insertion sécurisée (PDO)
            $stmt_demande = $db->prepare("
                INSERT INTO Demande_service (
                    Email_Demandeur_Demande_Service, Message_Demande_Service,
                    Statut_Demande_Service, Date_Demande_Service, Id_Entreprise
                ) VALUES (
                    :email, :message, 'en attente', CURDATE(), :id_entreprise
                )
            ");

            // Exécution de la requête avec les paramètres liés (protection injection SQL)
            $stmt_demande->execute([
                ':email'         => $email_demandeur,
                ':message'       => $message_compile,
                ':id_entreprise' => $id_entreprise,
            ]);

            // Succès : Le drapeau passe à true pour masquer le formulaire et afficher le message de confirmation
            $message_succes = true;
        } catch (PDOException $e) {
            $erreur = "Erreur lors de l'enregistrement : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <title>Sollicitation de service | IBG FIRE ET SECURE</title>
</head>
<body>
    <header>
        <a href="<?= BASE_URL ?>/index.php">
            <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="<?= BASE_URL ?>/index.php">Accueil</a></li>
                <li><a href="<?= BASE_URL ?>/CompteEntreprise.php">Mon espace entreprise</a></li>
                <li><a href="<?= BASE_URL ?>/NousContacter.php">Nous contacter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Solliciter : <?= htmlspecialchars($type_service) ?></h1>
        
        <section>
            <?php if (!empty($erreur)): ?>
                <div style="background-color:#f8d7da; color:#721c24; padding:15px; margin-bottom:20px; border-radius:4px; font-weight:bold; text-align:center;">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($message_succes): ?>
                <section class="reponse" style="text-align:center; padding:40px; background:#e2f0d9; border-radius:8px; border:1px solid #385723;">
                    <img src="<?= BASE_URL ?>/assets/image/Logo_IBG_FS-removebg-preview.png" alt="LOGO IBG FIRE ET SECURE" style="max-width:150px;">
                    <p style="color:#385723; font-size:1.3em; font-weight:bold; margin-top:20px;">Votre demande de sollicitation a été envoyée avec succès !</p>
                    <p style="color:#666;">État actuel : <strong>En attente</strong> de traitement par notre équipe.</p>
                    <a href="<?= BASE_URL ?>/CompteEntreprise.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#385723; color:white; text-decoration:none; border-radius:4px;">Retour à mon espace entreprise</a>
                </section>
            <?php else: ?>

                <form action="?service=<?= urlencode($type_service) ?>" method="post" class="formulaire">

                    <h2>Vos coordonnées</h2>
                    <div class="form-group">
                        <label for="user_email">Email de contact pour cette demande :</label>
                        <input type="email" name="user_email" id="user_email" placeholder="Ex : contact@entreprise.fr" value="<?= htmlspecialchars($_POST['user_email'] ?? $entreprise['Email_Contact_Entreprise'] ?? '') ?>" required>
                    </div>

                    <h2>Détails de la mission</h2>
                    <div class="form-group">
                        <label for="user_adresse_site">Adresse du site d'intervention :</label>
                        <input type="text" name="user_adresse_site" id="user_adresse_site" placeholder="Ex : 23 rue Michel, 44600 Saint-Nazaire" value="<?= htmlspecialchars($_POST['user_adresse_site'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="user_duree_mission">Durée de la mission :</label>
                        <input type="text" name="user_duree_mission" id="user_duree_mission" placeholder="Ex : 3 mois, ponctuel, longue durée..." value="<?= htmlspecialchars($_POST['user_duree_mission'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="user_date_debut">Date de début souhaitée :</label>
                        <input type="date" name="user_date_debut" id="user_date_debut" value="<?= htmlspecialchars($_POST['user_date_debut'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="user_date_fin">Date de fin envisagée :</label>
                        <input type="date" name="user_date_fin" id="user_date_fin" value="<?= htmlspecialchars($_POST['user_date_fin'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="user_message">Message / précisions sur le besoin :</label>
                        <textarea name="user_message" id="user_message" rows="5" placeholder="Décrivez votre besoin (effectif souhaité, contraintes particulières, horaires...)" required><?= htmlspecialchars($_POST['user_message'] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn-submit">Envoyer la demande</button>
                </form>

            <?php endif; ?>
        </section>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>