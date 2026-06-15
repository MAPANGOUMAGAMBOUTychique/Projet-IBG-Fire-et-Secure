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
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/Administrateur.css">
    <link rel="stylesheet" href="assets/Entreprises.css">
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
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo">
        </a>
        <nav class="navbar">
            <ul>
                <li><a href="Administrateur.php">Accueil Admin</a></li> 
                <li><a href="Statistique.php">Statistiques</a></li> 
                <li><a href="Entreprises.php">Entreprises</a></li> 
                <li><a href="Employers.php">Employés</a></li> 
                <li><a href="Services.php">Services</a></li>
                <li><a href="Missions.php">Missions</a></li>   
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
                                    <a class="link-detail" href="DetailEmploye.php?id=<?= $emp['Id_Employe'] ?>">
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
            <a href="Deconnexion.php" class="logout-link">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>