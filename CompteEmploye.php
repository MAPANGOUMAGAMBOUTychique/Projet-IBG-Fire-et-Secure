<?php
// 1. CONFIGURATION SÉCURISÉE DES COOKIES DE SESSION
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => 'localhost',
    'secure' => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();
require_once 'Database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: SeConnecter.php");
    exit();
}

$bdd = Database::getInstance();
$id_utilisateur = $_SESSION['user_id'];

$message_erreur_suppression = "";
$erreur_global = "";

// --- TRAITEMENT DE LA MODIFICATION DU PROFIL (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_name'])) {
    try {
        $nom_saisi    = trim($_POST['user_name']);
        $prenom_saisi = trim($_POST['user_prenom']);
        $mdp_saisi    = trim($_POST['user_mot_de_passe_entreprise']);

        // 1. Mettre à jour la table Employe
        $stmt_up_emp = $bdd->prepare("UPDATE Employe SET Nom_Employe = ?, Prenom_Employe = ? WHERE Id_Employe = ?");
        $stmt_up_emp->execute([$nom_saisi, $prenom_saisi, $id_utilisateur]);

        // 2. Mettre à jour la table Utilisateur (Nom et potentiellement le mot de passe)
        if (!empty($mdp_saisi)) {
            $mdp_hache = password_hash($mdp_saisi, PASSWORD_BCRYPT);
            $stmt_up_user = $bdd->prepare("UPDATE Utilisateur SET Nom_Utilisateur = ?, Mot_De_Passe_Utilisateur = ? WHERE Id_Utilisateur = ?");
            $stmt_up_user->execute([$nom_saisi, $mdp_hache, $id_utilisateur]);
        } else {
            $stmt_up_user = $bdd->prepare("UPDATE Utilisateur SET Nom_Utilisateur = ? WHERE Id_Utilisateur = ?");
            $stmt_up_user->execute([$nom_saisi, $id_utilisateur]);
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } catch (PDOException $e) {
        $erreur_global = "Erreur lors de la mise à jour : " . $e->getMessage();
    }
}

// --- TRAITEMENT DE LA SUPPRESSION DU COMPTE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmer_suppression'])) {
    try {
        $stmt_del_emp = $bdd->prepare("DELETE FROM Employe WHERE Id_Employe = ?");
        $stmt_del_emp->execute([$id_utilisateur]);

        $stmt_del_user = $bdd->prepare("DELETE FROM Utilisateur WHERE Id_Utilisateur = ?");
        $stmt_del_user->execute([$id_utilisateur]);

        session_unset();
        session_destroy();

        header("Location: index.php?statut=compte_supprime");
        exit();

    } catch (PDOException $e) {
        $message_erreur_suppression = "Erreur lors de la suppression du compte : " . $e->getMessage();
    }
}

// --- RÉCUPÉRATION DES INFOS DE L'EMPLOYÉ ---
$nom_complet = "Employé";
$nom = "";
$prenom = "";
$email = "";

try {
    $stmt = $bdd->prepare("SELECT Nom_Utilisateur, Email_Utilisateur FROM Utilisateur WHERE Id_Utilisateur = ?");
    $stmt->execute([$id_utilisateur]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $email = $user['Email_Utilisateur'];

        $stmt_emp = $bdd->prepare("SELECT Nom_Employe, Prenom_Employe FROM Employe WHERE Id_Employe = ?");
        $stmt_emp->execute([$id_utilisateur]);
        $employe = $stmt_emp->fetch(PDO::FETCH_ASSOC);

        if ($employe) {
            $nom = $employe['Nom_Employe'];
            $prenom = $employe['Prenom_Employe'];
            $nom_complet = $prenom . " " . $nom;
        } else {
            $nom = $user['Nom_Utilisateur'];
            $nom_complet = $nom;
        }
    }
} catch (PDOException $e) {
    $erreur_global = "Erreur : " . $e->getMessage();
}

// --- MISSIONS DISPONIBLES (depuis la table Mission) ---
$liste_missions = [];
try {
    $stmt_missions = $bdd->query("
        SELECT m.*, e.Nom_Entreprise
        FROM Mission m
        JOIN Entreprise e ON m.Id_Entreprise = e.Id_Entreprise
        ORDER BY m.Date_Creation_Mission DESC
    ");
    $liste_missions = $stmt_missions->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $liste_missions = [];
}

// --- MES POSTULATIONS (statuts envoyés par l'admin) ---
$mes_postulations = [];
try {
    $stmt_mes_postulations = $bdd->prepare("
        SELECT p.*, m.Titre_Mission
        FROM Postuler p
        JOIN Mission m ON p.Id_Mission = m.Id_Mission
        WHERE p.Id_Employe = ?
        ORDER BY p.Date_Postuler DESC
    ");
    $stmt_mes_postulations->execute([$id_utilisateur]);
    $mes_postulations = $stmt_mes_postulations->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $mes_postulations = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Employé | Site IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/CompteEmployer.css">
    <style>
        #Modification_compte,
        #Message_Succes {
            display: none;
        }

        #Zone_Confirmation_Suppression {
            display: none;
        }

        .reponse, .oui-non {
            margin-top: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="NousContacter.php">Nous contacter</a></li>
                <li><a href="Deconnexion.php">Se déconnecter</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1><?= htmlspecialchars($nom_complet) ?></h1>

        <?php if (!empty($erreur_global)): ?>
            <p style="color: red; text-align:center;"><?= htmlspecialchars($erreur_global) ?></p>
        <?php endif; ?>

        <section id="Compte_entreprise">
            <form action="" method="post" class="formulaire" id="formulaire_employe">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" name="user_name" id="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Votre nom" required disabled>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="user_prenom" id="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Votre prénom" required disabled>
                </div>
                <div class="form-group">
                    <label for="Email_entreprise">E-mail :</label>
                    <input type="email" name="user_email_entreprise" id="Email_entreprise" value="<?= htmlspecialchars($email) ?>" disabled>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de passe (laisser vide si inchangé) :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" placeholder="••••••••" disabled>
                </div>

                <div class="conteneur_bouton">
                    <button type="button" class="btn-submit" id="boutton_modifier">Modifier</button>
                    <button type="button" class="btn-submit" id="boutton_enregistrer" style="display: none;">Enregistrer</button>
                </div>
            </form>
        </section>

        <section id="Modification_compte">
            <div class="reponse">
                <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE">
                <p id="confirmation_modification">Voulez-vous vraiment modifier vos informations ?</p>
            </div>
            <div class="oui-non">
                <button type="button" class="btn-solliciter" id="btn_oui">Oui</button>
                <button type="button" class="btn-solliciter" id="btn_non">Non</button>
            </div>
        </section>

        <div id="Message_Succes" class="reponse">
            <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo IBG FIRE ET SECURE">
            <p class="logo_text">Vos informations ont été modifiées avec succès !</p>
        </div>

        <?php if (!empty($mes_postulations)): ?>
        <section id="MesPostulations">
            <h2>Mes candidatures aux offres</h2>
            <?php foreach ($mes_postulations as $mp): ?>
                <?php
                    $statut = $mp['Statut_Postuler'];
                    if ($statut === 'Accepté') {
                        $couleur = '#d4edda'; $texte_couleur = '#155724';
                        $message = "✅ Félicitations ! Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" a été acceptée.";
                    } elseif ($statut === 'Refusé') {
                        $couleur = '#f8d7da'; $texte_couleur = '#721c24';
                        $message = "❌ Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" a été refusée.";
                    } else {
                        $couleur = '#fff3cd'; $texte_couleur = '#856404';
                        $message = "⏳ Votre candidature pour \"" . htmlspecialchars($mp['Titre_Mission']) . "\" est en attente de réponse.";
                    }
                ?>
                <div style="background:<?= $couleur ?>; color:<?= $texte_couleur ?>; padding:12px 18px; margin-bottom:10px; border-radius:5px; font-weight:bold;">
                    <?= $message ?>
                </div>
            <?php endforeach; ?>
        </section>
        <?php endif; ?>

        <section id="services">
            <h2>Liste des offres</h2>

            <section id="MissionsDisponibles">
                <h3>Missions Disponibles</h3>
                <ul class="service-grid">
                    <?php if (empty($liste_missions)): ?>
                        <li><p>Aucune mission disponible pour le moment.</p></li>
                    <?php else: ?>
                        <?php foreach ($liste_missions as $mission): ?>
                            <li>
                                <article>
                                    <h4><?= htmlspecialchars($mission['Titre_Mission']) ?></h4>
                                    <p style="font-size:0.9em; color:#555; font-weight:bold;">Entreprise : <?= htmlspecialchars($mission['Nom_Entreprise']) ?></p>
                                    <p><?= htmlspecialchars($mission['Description_Mission']) ?></p>
                                    <p style="font-size:0.85em; color:#666;">
                                        Publiée le : <?= htmlspecialchars(date('d/m/Y', strtotime($mission['Date_Creation_Mission']))) ?>
                                    </p>
                                    <a href="PostulationOffreEmployer.php?id=<?= $mission['Id_Mission'] ?>" class="btn-solliciter">Postuler</a>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </section>
        </section>

        <section id="Supression_compte_employer">
            <button type="button" class="btn-suprimer" id="btn_supprimer_compte">Supprimer le compte</button>

            <?php if (!empty($message_erreur_suppression)): ?>
                <p style="color: red; text-align: center; font-weight: bold;"><?= htmlspecialchars($message_erreur_suppression) ?></p>
            <?php endif; ?>

            <div id="Zone_Confirmation_Suppression">
                <div class="reponse" style="display: block; margin-bottom: 15px;">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo IBG FIRE ET SECURE">
                    <p class="logo_text" style="font-weight: bold; color: #d9534f;">Voulez-vous vraiment supprimer définitivement votre compte employé ?</p>
                </div>

                <form action="" method="post">
                    <div class="oui-non">
                        <button type="submit" name="confirmer_suppression" class="btn-solliciter" style="background: #d9534f; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Oui</button>
                        <button type="button" class="btn-solliciter" id="btn_annuler_suppression" style="background: #666; color: white; border: none; padding: 10px 20px; cursor: pointer; border-radius: 4px;">Non</button>
                    </div>
                </form>
            </div>
        </section>
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
                <h4>Liens</h4>
                <nav>
                    <ul>
                        <li><a href="MentionsLégales.php">Mentions légales</a></li>
                        <li><a href="PolitiquesDeConfidentialités.php">Politique de Confidentialité</a></li>
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="NosServices.php">Nos Services</a></li>
                        <li><a href="NousContacter.php">Nous contacter</a></li>
                        <li><a href="Postuler.php">Je postule</a></li>
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

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const btnModifier = document.getElementById("boutton_modifier");
        const btnEnregistrer = document.getElementById("boutton_enregistrer");
        const sectionConfirmation = document.getElementById("Modification_compte");
        const messageSucces = document.getElementById("Message_Succes");
        const btnOui = document.getElementById("btn_oui");
        const btnNon = document.getElementById("btn_non");
        const form = document.getElementById("formulaire_employe");
        const champs = form.querySelectorAll("input");

        btnModifier.addEventListener("click", function() {
            champs.forEach(champ => champ.removeAttribute("disabled"));
            btnModifier.style.display = "none";
            btnEnregistrer.style.display = "inline-block";
        });

        btnEnregistrer.addEventListener("click", function() {
            sectionConfirmation.style.display = "block";
            messageSucces.style.display = "none";
        });

        btnOui.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            messageSucces.style.display = "block";

            setTimeout(() => {
                form.submit();
            }, 1500);
        });

        btnNon.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            champs.forEach(champ => champ.setAttribute("disabled", "true"));
            btnModifier.style.display = "inline-block";
            btnEnregistrer.style.display = "none";
            window.location.reload();
        });

        // Suppression du compte
        const btnSupprimer = document.getElementById("btn_supprimer_compte");
        const zoneSuppression = document.getElementById("Zone_Confirmation_Suppression");
        const btnAnnulerSuppression = document.getElementById("btn_annuler_suppression");

        btnSupprimer.addEventListener("click", function() {
            zoneSuppression.style.display = "block";
        });

        btnAnnulerSuppression.addEventListener("click", function() {
            zoneSuppression.style.display = "none";
        });
    });
    </script>
</body>
</html>