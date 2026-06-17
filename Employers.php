<?php
// 1. GESTION DE LA SESSION ET SÉCURITÉ D'ACCÈS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'Database.php';

// Affichage des erreurs pour le développement local
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('BASE_URL', 'http://localhost/StageTychique/SiteIbgFireEtSecure'); 

// Vérification stricte du rôle Admin
if (!isset($_SESSION['user_id']) || strtolower($_SESSION['user_role']) !== 'admin') {
    header("Location: " . BASE_URL . "/SeConnecter.php");
    exit();
}

// 2. RÉCUPÉRATION DES EMPLOYÉS ET DU NOMBRE DE MISSIONS POSTULÉES
$employes = [];

try {
    $bdd = Database::getInstance();
    
    // Requête SQL liant l'employé à la table pivot 'Postuler'
    // Note : On concatène le Nom et le Prénom pour l'affichage
    $sql = "SELECT e.Id_Employe, e.Nom_Employe, e.Prenom_Employe, COUNT(p.Id_Employe) AS nb_postulations 
            FROM Employe e
            LEFT JOIN Postuler p ON e.Id_Employe = p.Id_Employe
            GROUP BY e.Id_Employe, e.Nom_Employe, e.Prenom_Employe
            ORDER BY e.Nom_Employe ASC, e.Prenom_Employe ASC";
            
    $stmt = $bdd->query($sql);
    $employes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $erreur_sql = "Erreur de base de données : " . $e->getMessage();
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
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/Entreprises.css">
    <title>Employés | Espace Administrateur</title>
    <style>
        .link-detail {
            color: #007bff;
            text-decoration: none;
            font-weight: bold;
        }
        .link-detail:hover {
            text-decoration: underline;
            color: #0056b3;
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
        <h1 class="main-title">Comptes Employés</h1>

        <?php if (isset($erreur_sql)): ?>
            <p style="color:red; text-align:center; background:#fff; padding:10px; border:1px solid red; max-width:600px; margin:20px auto;">
                ⚠️ <?= htmlspecialchars($erreur_sql) ?>
            </p>
        <?php endif; ?>

        <div class="content-box">
            <table>
                <thead>
                    <tr>
                        <th>Nom Complet de l'Employé (Cliquer pour voir les détails)</th> 
                        <th class="header-count">Nombre de missions<br>postulées</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($employes)): ?>
                        <?php foreach ($employes as $emp): ?>
                            <tr>
                                <td class="company-name">
                                    <a class="link-detail" href="<?= BASE_URL ?>/DetailEmploye.php?id=<?= $emp['Id_Employe'] ?>">
                                        👤 <?= htmlspecialchars($emp['Nom_Employe'] . ' ' . $emp['Prenom_Employe']) ?>
                                    </a>
                                </td>
                                <td class="count"><?= htmlspecialchars($emp['nb_postulations']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align: center; padding: 20px; color: #666;">
                                Aucun employé enregistré dans la base de données.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="footer-action">
            <a href="<?= BASE_URL ?>/Deconnexion.php" class="logout-link">Se déconnecter</a>
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