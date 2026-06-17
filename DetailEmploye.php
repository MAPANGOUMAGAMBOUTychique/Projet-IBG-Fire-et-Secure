<?php
// ==========================================
// 1. INITIALISATION DE LA SESSION & SÉCURITÉ
// ==========================================

// Démarrage de la session si elle n'est pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclusion de la classe de connexion Singleton à la base de données
require_once 'Database.php';

// Configuration de l'affichage des erreurs (Utile en développement)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Définition de la racine du site web pour centraliser les URLs
define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Contrôle d'accès : Redirection vers la page de connexion si l'utilisateur n'est pas un administrateur connecté
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit(); // Interruption immédiate du script après redirection
}

// Initialisation des variables de traitement
$employe = null;
$message_erreur = "";

// ==========================================
// 2. RÉCUPÉRATION ET TRAITEMENT DES DONNÉES
// ==========================================

// Vérification de l'existence et de la validité du paramètre "id" passé dans l'URL (ex: ?id=5)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    // Sécurisation de la variable en forçant le type ENTIER (protection contre les injections XSS/Paramètres invalides)
    $id_employe = intval($_GET['id']);
    
    try {
        // Récupération de l'instance de connexion PDO
        $bdd = Database::getInstance();
        
        // Préparation de la requête SQL pour extraire le profil de l'employé ciblé
        $stmt = $bdd->prepare("SELECT * FROM Employe WHERE Id_Employe = ?");
        $stmt->execute([$id_employe]);
        
        // Stockage du résultat sous forme de tableau associatif
        $employe = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si aucun enregistrement ne correspond à cet ID dans la base de données
        if (!$employe) {
            $message_erreur = "Aucun employé ne correspond à cet identifiant.";
        }
    } catch (PDOException $e) {
        // Capture et affichage d'une erreur propre en cas de défaillance SQL
        $message_erreur = "Erreur lors de la récupération des données : " . $e->getMessage();
    }
} else {
    // Cas où le paramètre ID est vide ou absent de l'URL
    $message_erreur = "Identifiant de l'employé manquant ou incorrect.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/index.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Administrateur.css">
    <title>Détails Employé | Espace Admin</title>
    
    <style>
        .detail-box {
            background: #fff;
            max-width: 700px;
            margin: 30px auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .detail-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .detail-section h2 {
            color: #c90000;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .grid-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #111;
        }
        .btn-back {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 4px;
        }
        .btn-back:hover {
            background-color: #555;
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

    <main style="padding: 20px;">
        <h1 style="text-align:center;">Fiche Individuelle de l'Employé</h1>

        <?php if (!empty($message_erreur)): ?>
            <p style="color:red; text-align:center; background:#f8d7da; padding:15px; border:1px solid #f5c6cb; max-width:600px; margin:20px auto; border-radius:4px;">
                ⚠️ <?= htmlspecialchars($message_erreur) ?>
            </p>
            <div style="text-align:center;">
                <a href="<?= BASE_URL ?>/Employers.php" class="btn-back">Retour à la liste</a>
            </div>
        <?php endif; ?>

        <?php if ($employe): ?>
            <div class="detail-box">
                <h1 style="color: #333; margin-bottom: 20px;">👤 <?= htmlspecialchars($employe['Nom_Employe'] . ' ' . $employe['Prenom_Employe']) ?></h1>
                
                <div class="detail-section">
                    <h2>Identité & Profil</h2>
                    <div class="grid-info">
                        <div class="info-label">ID Employé :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Id_Employe']) ?></div>

                        <div class="info-label">Nom :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Nom_Employe']) ?></div>
                        
                        <div class="info-label">Prénom :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Prenom_Employe']) ?></div>

                        <div class="info-label">Date de naissance :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Date_Naissance_Employe'] ?? 'Non renseignée') ?></div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Coordonnées de Contact</h2>
                    <div class="grid-info">
                        <div class="info-label">Téléphone Personnel :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Telephone_Employe'] ?? 'Non renseigné') ?></div>
                        
                        <div class="info-label">Adresse E-mail :</div>
                        <div class="info-value">
                            <a href="mailto:<?= htmlspecialchars($employe['Email_Employe'] ?? '') ?>">
                                <?= htmlspecialchars($employe['Email_Employe'] ?? 'Non renseigné') ?>
                            </a>
                        </div>

                        <div class="info-label">Adresse Postale :</div>
                        <div class="info-value">
                            <?= htmlspecialchars($employe['Numero_Voie_Employe'] ?? '') ?> 
                            <?= htmlspecialchars($employe['Nom_Voie_Employe'] ?? '') ?><br>
                            <?= htmlspecialchars($employe['Code_Postal_Employe'] ?? '') ?> 
                            <?= htmlspecialchars($employe['Ville_Employe'] ?? '') ?>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2>Données Contractuelles</h2>
                    <div class="grid-info">
                        <div class="info-label">Diplôme / Qualification :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Diplome_Employe'] ?? 'Non spécifié') ?></div>

                        <div class="info-label">Date d'embauche / Inscription :</div>
                        <div class="info-value"><?= htmlspecialchars($employe['Date_Inscription_Employe'] ?? 'Non renseignée') ?></div>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between;">
                    <a href="<?= BASE_URL ?>/Employers.php" class="btn-back">⬅️ Retour à la liste</a>
                    <a href="<?= BASE_URL ?>/Deconnexion.php" class="btn-back" style="background-color: #c90000;">Se déconnecter</a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>