<?php
// ==========================================
// 1. INITIALISATION ET CONFIGURATION
// ==========================================

// Démarrage de la session pour suivre l'état de l'utilisateur
session_start();

// Inclusion de la classe Singleton pour la connexion à la base de données
require_once 'Database.php';

// Configuration de l'affichage des erreurs pour faciliter le débogage en développement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialisation des variables d'état pour la vue HTML
$message_succes = false;
$erreur = "";

// ==========================================
// 2. TRAITEMENT DU FORMULAIRE D'INSCRIPTION (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération de l'instance PDO unique
    $db = Database::getInstance();

    // Récupération, nettoyage des espaces blancs (trim) et normalisation des données reçues
    $nom_entreprise = trim($_POST['user_nom_entreprise']);
    $siret          = str_replace(' ', '', trim($_POST['user_siret_entreprise'])); // Supprime les espaces dans le SIRET
    $code_naf       = trim($_POST['user_code_naf']);
    $tva            = str_replace(' ', '', trim($_POST['user_numero_de_tva']));    // Supprime les espaces dans la TVA
    $telephone      = trim($_POST['user_telephone_entreprise']);
    $numero_voie    = trim($_POST['user_numero_voie']);
    $nom_voie       = trim($_POST['user_nom_voie']);
    $complement     = trim($_POST['user_complement'] ?? '');
    $ville          = trim($_POST['user_ville']);
    $pays           = trim($_POST['user_pays']);
    $referent       = trim($_POST['user_nom_prenom']);
    $fonction       = trim($_POST['user_fonction']);
    $email_contact  = trim($_POST['user_email_contact']);
    $password       = $_POST['user_mot_de_passe'];
    $password_conf  = $_POST['user_conmimation_mot_de_passe'];

    // --- VALIDATION DES MOTS DE PASSE ---
    if ($password !== $password_conf) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        try {
            // --- ÉTAPE 1 : VÉRIFICATION DE L'UNICITÉ DU SIRET ---
            // On vérifie si le SIRET existe déjà dans les candidatures d'entreprises
            $check_cand = $db->prepare("SELECT Id_Candidature FROM Candidature WHERE Siret_Candidature = ? AND Type_Candidature = 'entreprise'");
            $check_cand->execute([$siret]);

            // On vérifie si le SIRET existe déjà parmi les entreprises validées et actives
            $check_active = $db->prepare("SELECT Id_Entreprise FROM Entreprise WHERE Siret_Entreprise = ?");
            $check_active->execute([$siret]);

            if ($check_cand->fetch() || $check_active->fetch()) {
                $erreur = "Ce numéro SIRET est déjà enregistré ou en cours de validation.";
            } else {
                // Hachage sécurisé du mot de passe avec l'algorithme standard BCrypt
                $password_hashed = password_hash($password, PASSWORD_BCRYPT);

                // ------------------------------------------------
                // ÉTAPE 2 : INSERTION DANS LA TABLE 'Utilisateur'
                // ------------------------------------------------
                $stmt_user = $db->prepare("
                    INSERT INTO Utilisateur 
                        (Nom_Utilisateur, Email_Utilisateur, Mot_De_Passe_Utilisateur, Role, Statut_Compte_Utilisateur, Date_Creation_Utilisateur)
                    VALUES 
                        (:nom, :email, :mdp, 'entreprise', 'en attente', CURDATE())
                ");
                $stmt_user->execute([
                    ':nom'   => $referent,
                    ':email' => $email_contact,
                    ':mdp'   => $password_hashed,
                ]);

                // Récupération de l'ID utilisateur généré automatiquement (Clé primaire)
                $id_utilisateur = $db->lastInsertId();

                // ------------------------------------------------
                // ÉTAPE 3 : INSERTION DANS LA TABLE 'Candidature'
                // ------------------------------------------------
                $stmt_cand = $db->prepare("
                    INSERT INTO Candidature (
                        Statut_Candidature, Date_Candidature, Id_Utilisateur, Type_Candidature,
                        Nom_Entreprise_Candidature, Siret_Candidature, Code_NAF_Candidature,
                        Numero_TVA_Candidature, Telephone_Entreprise_Candidature, Numero_Voie_Candidature,
                        Nom_Voie_Candidature, Complement_Candidature, Ville_Candidature,
                        Pays_Entreprise_Candidature, Nom_Referent_Candidature, Fonction_Referent_Candidature,
                        Email_Contact_Candidature, Mot_De_Passe_Entreprise_Candidature
                    ) VALUES (
                        'en attente', CURDATE(), :id_utilisateur, 'entreprise',
                        :nom_entreprise, :siret, :code_naf, :tva, :telephone, :numero_voie,
                        :nom_voie, :complement, :ville, :pays, :referent, :fonction, :email_contact, :mdp
                    )
                ");

                $stmt_cand->execute([
                    ':id_utilisateur' => $id_utilisateur,
                    ':nom_entreprise' => $nom_entreprise,
                    ':siret'          => $siret,
                    ':code_naf'       => $code_naf,
                    ':tva'            => $tva,
                    ':telephone'      => $telephone,
                    ':numero_voie'    => $numero_voie,
                    ':nom_voie'       => $nom_voie,
                    ':complement'     => $complement ?: null, // Force la valeur NULL si le champ est laissé vide
                    ':ville'          => $ville,
                    ':pays'           => $pays,
                    ':referent'       => $referent,
                    ':fonction'       => $fonction,
                    ':email_contact'  => $email_contact,
                    ':mdp'            => $password_hashed,
                ]);

                // Changement d'état pour masquer le formulaire et afficher le message de félicitations
                $message_succes = true;
            }
        } catch (PDOException $e) {
            // Capture des éventuelles exceptions SQL ou problèmes réseau liés à la base de données
            $erreur = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/index.css">
    <link rel="stylesheet" href="assets/style.css">
    <title>Postulation Entreprise | Site IBG FIRE ET SECURE</title>
</head>
<body>
    <header>
        <a href="index.php"><img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="logo IBG FIRE ET SECURE" class="logo"></a>
        <nav class="navbar">
            <ul>
                <li><a href="index.php">Accueil</a></li>
                <li><a href="NosServices.php">Nos services</a></li>
                <li><a href="NousContacter.php">Nous contacter</a></li>
                <li><a href="SeConnecter.php">Se connecter</a></li>
                <li><a href="CreerUnCompte.php">Créer un compte</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h1>Création du Compte Entreprise</h1>
        <section>
            <?php if (!empty($erreur)): ?>
                <div style="background-color:#f8d7da; color:#721c24; padding:15px; margin-bottom:20px; border-radius:4px; font-weight:bold; text-align:center;">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <?php if ($message_succes): ?>
                <section class="reponse" style="text-align:center; padding:40px; background:#e2f0d9; border-radius:8px; border:1px solid #385723;">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="LOGO IBG FIRE ET SECURE" style="max-width:150px;">
                    <p style="color:#385723; font-size:1.3em; font-weight:bold; margin-top:20px;">Votre demande de création de compte Entreprise a été soumise avec succès !</p>
                    <p style="color:#666;">Elle est actuellement en cours de révision par notre équipe d'administration.</p>
                    <a href="index.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#385723; color:white; text-decoration:none; border-radius:4px;">Retour à l'accueil</a>
                </section>
            <?php else: ?>
                
                <form action="" method="post" class="formulaire">

                    <h2>Identité de l'entreprise</h2>
                    <div class="form-group">
                        <label for="Raison_social">Raison sociale :</label>
                        <input type="text" name="user_nom_entreprise" id="Raison_social" placeholder="Nom officiel de l'entreprise" required>
                    </div>
                    <div class="form-group">
                        <label for="Numero_siret">SIRET :</label>
                        <input type="text" name="user_siret_entreprise" id="Numero_siret" placeholder="12345678900012" pattern="[0-9\s]{14,18}" title="Le numéro SIRET doit être composé de 14 chiffres" required>
                    </div>
                    <div class="form-group">
                        <label for="code_naf">Code NAF / APE (Optionnel) :</label>
                        <input type="text" name="user_code_naf" id="code_naf" placeholder="Ex: 8010Z" pattern="[0-9]{4}[A-Z]" title="4 chiffres suivis d'une lettre majuscule" maxlength="5">
                    </div>
                    <div class="form-group">
                        <label for="Numero_de_tva">Numéro de TVA intracommunautaire :</label>
                        <input type="text" name="user_numero_de_tva" id="Numero_de_tva" placeholder="FR00123456789" pattern="FR[0-9]{11}" title="FR suivi de 11 chiffres" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone_entreprise">Téléphone de l'entreprise :</label>
                        <input type="tel" name="user_telephone_entreprise" id="telephone_entreprise" placeholder="01 00 00 00 00" required>
                    </div>

                    <h2>Adresse du siège social</h2>
                    <div class="form-group">
                        <label for="numero_voie">Numéro de voie :</label>
                        <input type="text" name="user_numero_voie" id="numero_voie" placeholder="Ex: 24" required>
                    </div>
                    <div class="form-group">
                        <label for="nom_voie">Nom de la voie :</label>
                        <input type="text" name="user_nom_voie" id="nom_voie" placeholder="Ex: allée de la mer d'iroise" required>
                    </div>
                    <div class="form-group">
                        <label for="complement">Complément d'adresse (optionnel) :</label>
                        <input type="text" name="user_complement" id="complement" placeholder="Ex: Bâtiment B, Appartement 3...">
                    </div>
                    <div class="form-group">
                        <label for="ville">Code postal et ville :</label>
                        <input type="text" name="user_ville" id="ville" placeholder="Ex: 44600 Saint-Nazaire" required>
                    </div>
                    <div class="form-group">
                        <label for="Pays">Pays du siège social :</label>
                        <div class="custom-select-container" style="position:relative;">
                            <input type="hidden" name="user_pays" id="selected-nat" value="France" required>
                            
                            <div class="select-display" id="select-trigger" tabindex="0" style="border:1px solid #ccc; padding:10px; cursor:pointer; background:white;">
                                <img src="https://flagcdn.com/16x12/fr.png" alt=""> France
                            </div>
                            
                            <ul class="options-list" id="options-list" style="display:none; border:1px solid #ccc; list-style:none; padding:0; max-height:200px; overflow-y:auto; background:white; position:absolute; width:100%; z-index:1000;">
                                <li data-value="Allemagne" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/de.png" alt=""> Allemagne</li>
                                <li data-value="Belgique" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/be.png" alt=""> Belgique</li>
                                <li data-value="Espagne" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/es.png" alt=""> Espagne</li>
                                <li data-value="France" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/fr.png" alt=""> France</li>
                                <li data-value="Italie" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/it.png" alt=""> Italie</li>
                                <li data-value="Luxembourg" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/lu.png" alt=""> Luxembourg</li>
                                <li data-value="Suisse" style="padding:10px; cursor:pointer;"><img src="https://flagcdn.com/16x12/ch.png" alt=""> Suisse</li>
                            </ul>
                        </div>
                    </div>

                    <h2>Contact référent (L'administrateur du compte)</h2>
                    <div class="form-group">
                        <label for="Nom_et_prenom">Nom et Prénom du responsable :</label>
                        <input type="text" name="user_nom_prenom" id="Nom_et_prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="Fonction_entreprise">Fonction dans l'entreprise :</label>
                        <input type="text" name="user_fonction" id="Fonction_entreprise" placeholder="ex: Gérant, Responsable Sécurité..." required>
                    </div>
                    <div class="form-group">
                        <label for="email_contact">Email de contact :</label>
                        <input type="email" name="user_email_contact" id="email_contact" placeholder="contact@entreprise.fr" required>
                    </div>

                    <h2>Sécurité du Compte</h2>
                    <div class="form-group">
                        <label for="Mot_de_passe">Mot de passe :</label>
                        <input type="password" name="user_mot_de_passe" id="Mot_de_passe" placeholder="8 caractères minimum" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="Confirmation_mot-de-passe">Confirmation du mot de passe :</label>
                        <input type="password" name="user_conmimation_mot_de_passe" id="Confirmation_mot-de-passe" required>
                    </div>

                    <button type="submit" class="btn-submit">Envoyer la demande</button>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script>
        const trigger = document.getElementById('select-trigger');
        const list    = document.getElementById('options-list');
        const hidden  = document.getElementById('selected-nat');

        // Basculer l'affichage (afficher/masquer) de la liste au clic sur le sélecteur
        trigger.addEventListener('click', () => {
            list.style.display = list.style.display === 'none' ? 'block' : 'none';
        });

        // Assigner les événements de clic sur chaque option (chaque pays de la liste)
        document.querySelectorAll('#options-list li').forEach(item => {
            item.addEventListener('click', function() {
                // Met à jour l'affichage visible avec le texte et l'image sélectionnés
                trigger.innerHTML = this.innerHTML;
                // Assigne la valeur textuelle brute (ex: "France") à l'input masqué
                hidden.value = this.getAttribute('data-value');
                // Masque la liste déroulante après sélection
                list.style.display = 'none';
            });
        });

        // Fermer automatiquement le menu déroulant si l'utilisateur clique en dehors du composant
        document.addEventListener('click', function(e) {
            if (!trigger.contains(e.target) && !list.contains(e.target)) {
                list.style.display = 'none';
            }
        });
    </script>

    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>