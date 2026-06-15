<?php
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

        // Redirection pour éviter le renvoi de formulaire en boucle
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
        exit();
    } catch (PDOException $e) {
        $erreur_global = "Erreur lors de la mise à jour : " . $e->getMessage();
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
    echo "Erreur : " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compte Employé | Site IBG FIRE ET SECURE</title>
    <link rel="stylesheet" href="assets/style.css?v=1.1">
    <link rel="stylesheet" href="assets/index.css?v=1.1">
    <link rel="stylesheet" href="assets/CompteEmployer.css?v=1.1">
    <style>
        #Modification_compte,
        #Message_Succes {
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

        <?php if(isset($erreur_global)): ?>
            <p style="color: red; text-align:center;"><?= htmlspecialchars($erreur_global) ?></p>
        <?php endif; ?>

        <?php if(isset($_GET['success'])): ?>
            <div id="Message_Succes_PHP" class="reponse" style="display: block;">
                <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="image logo">
                <p class="logo_text" style="color: green; font-weight: bold;">Vos informations ont été modifiées avec succès !</p>
            </div>
        <?php endif; ?>

        <section id="Compte_entreprise">
            <form action="" method="post" class="formulaire" id="formulaire_employe">
                <div class="form-group">
                    <label for="nom">Nom :</label>
                    <input type="text" name="user_name" id="nom" value="<?= htmlspecialchars($nom) ?>" placeholder="Votre nom" required readonly>
                </div>
                <div class="form-group">
                    <label for="prenom">Prénom :</label>
                    <input type="text" name="user_prenom" id="prenom" value="<?= htmlspecialchars($prenom) ?>" placeholder="Votre prénom" required readonly>
                </div>
                <div class="form-group">
                    <label for="Email_entreprise">E-mail :</label>
                    <input type="email" name="user_email_entreprise" id="Email_entreprise" value="<?= htmlspecialchars($email) ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="Mot_de_passe_entreprise">Mot de passe (laisser vide si inchangé) :</label>
                    <input type="password" name="user_mot_de_passe_entreprise" id="Mot_de_passe_entreprise" placeholder="••••••••" readonly>
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
 
        <section id="services">
            <h2>Liste des offres</h2>
        </section>

        <section id="Supression_compte_employer">
            <button type="button" class="btn-suprimer">Supprimer le compte</button>
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
        const btnModifier    = document.getElementById("boutton_modifier");
        const btnEnregistrer = document.getElementById("boutton_enregistrer");
        const sectionConfirmation = document.getElementById("Modification_compte");
        const messageSucces  = document.getElementById("Message_Succes");
        const btnOui = document.getElementById("btn_oui");
        const btnNon = document.getElementById("btn_non");
        const form   = document.getElementById("formulaire_employe");
        
        // On cible uniquement les champs modifiables (Exclure l'email)
        const champsModifiables = form.querySelectorAll("input:not(#Email_entreprise)");

        btnModifier.addEventListener("click", function() {
            // Suppression de readonly pour permettre l'édition
            champsModifiables.forEach(c => c.removeAttribute("readonly"));
            btnModifier.style.display = "none";
            btnEnregistrer.style.display = "inline-block";
        });

        btnEnregistrer.addEventListener("click", function() {
            sectionConfirmation.style.display = "block";
            messageSucces.style.display = "none";
            if(document.getElementById("Message_Succes_PHP")) {
                document.getElementById("Message_Succes_PHP").style.display = "none";
            }
        });

        btnOui.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            messageSucces.style.display = "block";
            // On laisse le temps à l'animation de succès de s'afficher avant de soumettre
            setTimeout(() => { form.submit(); }, 1200);
        });

        btnNon.addEventListener("click", function() {
            sectionConfirmation.style.display = "none";
            champsModifiables.forEach(c => c.setAttribute("readonly", "true"));
            btnModifier.style.display = "inline-block";
            btnEnregistrer.style.display = "none";
            window.location.reload(); // Recharge la page proprement si annulation
        });
    });
    </script>
</body>
</html>