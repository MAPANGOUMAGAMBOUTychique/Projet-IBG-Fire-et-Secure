<?php
// Démarrage de la session PHP pour stocker d'éventuelles informations de session
session_start();

// Inclusion de la classe de connexion à la base de données (Pattern Singleton)
require_once 'Database.php';

// Configuration de l'affichage des erreurs pour le débogage (à retirer en production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Initialisation des variables d'état du formulaire
$message_succes = false; // Passe à true si l'inscription réussit
$erreur = "";           // Contient le message d'erreur si un problème survient

// Traitement du formulaire uniquement si la page est appelée en méthode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération de l'instance unique de la base de données
    $db = Database::getInstance();

    // Récupération et nettoyage des données textuelles de l'identité
    $nom               = trim($_POST['user_nom']);
    $prenom            = trim($_POST['user_prenom']);
    $date_naissance    = trim($_POST['user_date_naissance']);
    $lieu_naissance    = trim($_POST['user_lieu_naissance']);
    $nationalite       = trim($_POST['user_nationalite']);
    $telephone         = trim($_POST['user_telephone']);
    
    // Récupération et nettoyage des informations professionnelles
    $numero_cnaps      = trim($_POST['user_numero_cnaps']);
    $expiration_cnaps  = trim($_POST['user_expiration_cnaps']);
    $visite_med        = trim($_POST['user_visite_med']);
    $permis_b          = trim($_POST['user_permis_b']);
    $vehicule          = trim($_POST['user_vehicule']);
    $aptitude_vue      = trim($_POST['user_aptitude_vue']);
    $type_contrat      = trim($_POST['user_type_contrat'] ?? '');
    
    // Gestion spécifique du rayon de mobilité : converti en entier, ou NULL s'il est vide
    $mobilite_rayon    = isset($_POST['user_mobilite_rayon']) && $_POST['user_mobilite_rayon'] !== '' ? intval($_POST['user_mobilite_rayon']) : null;
    
    $port_uniforme     = trim($_POST['user_port_uniforme']);
    $disponibilites    = trim($_POST['user_disponibilites'] ?? '');
    
    // Récupération des identifiants de connexion
    $email             = trim($_POST['user_email']);
    $password          = $_POST['user_mot_de_passe'];
    $password_conf     = $_POST['user_confirmation_mot_de_passe'];

    // Initialisation des chemins de fichiers à NULL (valeur par défaut en BDD)
    $cv_path   = null;
    $lm_path   = null;
    $cas_path  = null;

    // --- ÉTAPE 1 : VALIDATION DES MOTS DE PASSE ---
    if ($password !== $password_conf) {
        $erreur = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 8) {
        $erreur = "Le mot de passe doit contenir au moins 8 caractères.";
    } else {
        try {
            // --- ÉTAPE 2 : VÉRIFICATION DE L'EMAIL UNIQUE ---
            $check = $db->prepare("SELECT Id_Utilisateur FROM Utilisateur WHERE Email_Utilisateur = ?");
            $check->execute([$email]);
            if ($check->fetch()) {
                $erreur = "Cette adresse email est déjà utilisée.";
            } else {
                // Définition du dossier de destination pour les fichiers téléversés
                $dossier = __DIR__ . '/uploads/candidatures/';
                // Création du dossier s'il n'existe pas encore (droits d'écriture complets)
                if (!is_dir($dossier)) mkdir($dossier, 0777, true);

                $fichiers_valides = true;
                $allowed_extensions = ['pdf']; // Seuls les PDF sont autorisés

                // --- ÉTAPE 3 : TRAITEMENT ET SÉCURISATION DES FICHIERS ---
                // Utilisation d'un tableau associatif pour lier le nom de l'input à sa variable cible
                foreach ([
                    'user_cv'     => &$cv_path,
                    'user_lm'     => &$lm_path,
                    'user_casier' => &$cas_path,
                ] as $field => &$dest) {
                    // Si le fichier est présent et qu'aucune erreur PHP de téléversement n'est survenue
                    if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                        
                        // Extraction et sécurisation de l'extension du fichier (mise en minuscules)
                        $file_ext = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                        
                        // Vérification stricte de l'extension pour éviter l'exécution de scripts malveillants (.php, .sh, etc.)
                        if (in_array($file_ext, $allowed_extensions)) {
                            // Génération d'un nom de fichier unique basé sur le timestamp et une chaîne aléatoire
                            $nom_fichier = time() . '_' . bin2hex(random_bytes(4)) . '.' . $file_ext;
                            
                            // Déplacement du fichier temporaire vers le dossier final
                            if (move_uploaded_file($_FILES[$field]['tmp_name'], $dossier . $nom_fichier)) {
                                // Stockage du chemin relatif pour insertion en base de données
                                $dest = 'uploads/candidatures/' . $nom_fichier;
                            }
                        } else {
                            // Si un fichier n'est pas un PDF, on bloque l'inscription
                            $fichiers_valides = false;
                            $erreur = "Seuls les fichiers PDF sont autorisés pour vos pièces justificatives.";
                            break;
                        }
                    }
                }
                unset($dest); // Rupture de la référence par sécurité

                // --- ÉTAPE 4 : CRÉATION DU COMPTE ET DE LA CANDIDATURE ---
                if ($fichiers_valides) {
                    // Hachage sécurisé du mot de passe (Algorithme BCrypt)
                    $password_hashed = password_hash($password, PASSWORD_BCRYPT);

                    // Requete d'insertion dans la table générique Utilisateur
                    $stmt_user = $db->prepare("
                        INSERT INTO Utilisateur 
                            (Nom_Utilisateur, Email_Utilisateur, Mot_De_Passe_Utilisateur, Role, Statut_Compte_Utilisateur, Date_Creation_Utilisateur)
                        VALUES 
                            (:nom, :email, :mdp, 'employe', 'en attente', CURDATE())
                    ");
                    $stmt_user->execute([
                        ':nom'   => $nom . ' ' . $prenom, // Fusion du nom et prénom pour la table Utilisateur
                        ':email' => $email,
                        ':mdp'   => $password_hashed,
                    ]);
                    
                    // Récupération de l'ID généré automatiquement pour l'utilisateur
                    $id_utilisateur = $db->lastInsertId();

                    // Requête d'insertion des détails spécifiques de recrutement dans Candidature
                    $stmt_cand = $db->prepare("
                        INSERT INTO Candidature (
                            Statut_Candidature, Date_Candidature, Id_Utilisateur, Type_Candidature,
                            Nom_Candidature, Prenom_Candidature,
                            Date_Naissance_Candidature, Lieu_Naissance_Candidature, Nationalite_Candidature,
                            Telephone_Candidature, Numero_CNAPS_Candidature, Expiration_CNAPS_Candidature,
                            Date_Visite_Med_Candidature, Permis_b_Candidature, Vehicule_Candidature,
                            Aptitude_Vue_Candidature, Type_Contrat_Candidature, Mobilite_Rayon_Candidature,
                            Port_Uniforme_Candidature, Disponibilites_Candidature,
                            CV_Path_Candidature, Lettre_Motivation_Candidature, Casier_Path_Candidature
                        ) VALUES (
                            'en attente', CURDATE(), :id_utilisateur, 'employe',
                            :nom, :prenom,
                            :date_naissance, :lieu_naissance, :nationalite,
                            :telephone, :numero_cnaps, :expiration_cnaps,
                            :visite_med, :permis_b, :vehicule,
                            :aptitude_vue, :type_contrat, :mobilite_rayon,
                            :port_uniforme, :disponibilites,
                            :cv_path, :lm_path, :cas_path
                        )
                    ");
                    
                    // Exécution avec gestion fine des champs facultatifs (vide = null pour éviter les bugs SQL de formats)
                    $stmt_cand->execute([
                        ':id_utilisateur'   => $id_utilisateur,
                        ':nom'              => $nom,
                        ':prenom'           => $prenom,
                        ':date_naissance'   => !empty($date_naissance) ? $date_naissance : null,
                        ':lieu_naissance'   => $lieu_naissance,
                        ':nationalite'      => $nationalite,
                        ':telephone'        => $telephone,
                        ':numero_cnaps'     => $numero_cnaps,
                        ':expiration_cnaps' => !empty($expiration_cnaps) ? $expiration_cnaps : null,
                        ':visite_med'       => !empty($visite_med) ? $visite_med : null,
                        ':permis_b'         => $permis_b,
                        ':vehicule'         => $vehicule,
                        ':aptitude_vue'     => $aptitude_vue,
                        ':type_contrat'     => !empty($type_contrat) ? $type_contrat : null,
                        ':mobilite_rayon'   => $mobilite_rayon,
                        ':port_uniforme'    => $port_uniforme,
                        ':disponibilites'   => !empty($disponibilites) ? $disponibilites : null,
                        ':cv_path'          => $cv_path,
                        ':lm_path'          => $lm_path,
                        ':cas_path'         => $cas_path,
                    ]);

                    // Validation globale du processus
                    $message_succes = true;
                }
            }
        } catch (PDOException $e) {
            // Capturation de l'erreur SQL pour affichage dans le bandeau d'erreur
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
    <title>Création Compte Employé | IBG FIRE ET SECURE</title>
    <style>
        /* Styles spécifiques pour le fonctionnement du sélecteur personnalisé de nationalités */
        #nat-list li:hover { background: #f0f0f0; }
        #nat-list li { padding: 9px 12px; cursor: pointer; display: flex; align-items: center; gap: 10px; font-size: 0.93em; }
        #nat-list li.selected { background: #e8f4fd; font-weight: bold; }
        #nat-search:focus { border-color: #e67e22; box-shadow: 0 0 0 2px rgba(230,126,34,0.15); }
    </style>
</head>
<body>
    <!-- En-tête de page commun -->
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
        <h1>Création du Compte Employé</h1>
        <section>

            <!-- Bloc d'affichage dynamique des erreurs de traitement PHP -->
            <?php if (!empty($erreur)): ?>
                <div style="background-color:#f8d7da; color:#721c24; padding:15px; margin-bottom:20px; border-radius:4px; font-weight:bold; text-align:center;">
                    ⚠️ <?= htmlspecialchars($erreur) ?>
                </div>
            <?php endif; ?>

            <!-- Condition d'affichage : Si succès, on affiche le message de confirmation, sinon le formulaire -->
            <?php if ($message_succes): ?>
                <section class="reponse" style="text-align:center; padding:40px; background:#e2f0d9; border-radius:8px; border:1px solid #385723;">
                    <img src="assets/image/Logo_IBG_FS-removebg-preview.png" alt="LOGO IBG FIRE ET SECURE" style="max-width:150px;">
                    <p style="color:#385723; font-size:1.3em; font-weight:bold; margin-top:20px;">Votre demande de création de compte Employé a été soumise avec succès !</p>
                    <p style="color:#666;">Elle est actuellement en cours de révision par notre équipe d'administration.</p>
                    <a href="index.php" style="display:inline-block; margin-top:20px; padding:10px 20px; background:#385723; color:white; text-decoration:none; border-radius:4px;">Retour à l'accueil</a>
                </section>
            <?php else: ?>

                <!-- Notez bien l'enctype multipart/form-data indispensable pour pouvoir envoyer des fichiers (CV, LM...) -->
                <form action="" method="post" enctype="multipart/form-data" class="formulaire">

                    <!-- ===== IDENTITÉ ===== -->
                    <h2>Identité</h2>

                    <div class="form-group">
                        <label for="user_nom">Nom :</label>
                        <input type="text" name="user_nom" id="user_nom" placeholder="Ex : Dupont" required>
                    </div>
                    <div class="form-group">
                        <label for="user_prenom">Prénom :</label>
                        <input type="text" name="user_prenom" id="user_prenom" placeholder="Ex : Jean" required>
                    </div>
                    <div class="form-group">
                        <label for="user_date_naissance">Date de naissance :</label>
                        <input type="date" name="user_date_naissance" id="user_date_naissance" required>
                    </div>
                    <div class="form-group">
                        <label for="user_lieu_naissance">Lieu de naissance :</label>
                        <input type="text" name="user_lieu_naissance" id="user_lieu_naissance" placeholder="Ex : Angers" required>
                    </div>

                    <!-- NATIONALITÉ : Système Custom simulé en HTML/CSS, piloté par JS -->
                    <div class="form-group">
                        <label>Nationalité :</label>
                        <div style="position:relative;" id="nat-wrapper">
                            <!-- Input invisible qui transmettra réellement la valeur texte choisie au PHP -->
                            <input type="hidden" name="user_nationalite" id="selected-nat" value="Française" required>
                            
                            <!-- Déclencheur visuel (Façon Balise <select>) -->
                            <div id="nat-display" tabindex="0"
                                style="border:1px solid #ccc; padding:10px 12px; background:white; border-radius:4px; display:flex; align-items:center; gap:8px; cursor:pointer; user-select:none;">
                                <img id="nat-flag-preview" src="https://flagcdn.com/16x12/fr.png" alt="">
                                <span id="nat-label-preview">Française</span>
                                <span style="margin-left:auto; color:#999; font-size:0.75em;">▼</span>
                            </div>
                            
                            <!-- Menu déroulant caché par défaut -->
                            <div id="nat-dropdown"
                                style="display:none; position:absolute; width:100%; background:white; border:1px solid #ccc; border-top:none; border-radius:0 0 6px 6px; box-shadow:0 6px 16px rgba(0,0,0,0.13); z-index:1000;">
                                <div style="padding:8px 8px 4px;">
                                    <!-- Barre de filtre de recherche dynamique -->
                                    <input type="text" id="nat-search" placeholder="🔍 Rechercher une nationalité..."
                                        autocomplete="off"
                                        style="width:100%; box-sizing:border-box; padding:8px 10px; border:1px solid #ddd; border-radius:4px; font-size:0.92em; outline:none;">
                                </div>
                                <!-- Liste injectée par JavaScript -->
                                <ul id="nat-list" style="list-style:none; padding:0; margin:0; max-height:230px; overflow-y:auto;"></ul>
                                <div id="nat-no-result" style="display:none; padding:12px; color:#999; font-style:italic; font-size:0.9em; text-align:center;">Aucun résultat</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="user_telephone">Téléphone :</label>
                        <input type="tel" name="user_telephone" id="user_telephone" placeholder="Ex : 06 95 82 78 59" required>
                    </div>

                    <!-- ===== INFORMATIONS PROFESSIONNELLES ===== -->
                    <h2>Informations professionnelles</h2>

                    <div class="form-group">
                        <label for="user_numero_cnaps">N° CNAPS :</label>
                        <input type="text" name="user_numero_cnaps" id="user_numero_cnaps" placeholder="Ex : CAR-000-2026-05-12-A987F65D" required>
                    </div>
                    <div class="form-group">
                        <label for="user_expiration_cnaps">Date d'expiration CNAPS :</label>
                        <input type="date" name="user_expiration_cnaps" id="user_expiration_cnaps" required>
                    </div>
                    <div class="form-group">
                        <label for="user_visite_med">Date de dernière visite médicale :</label>
                        <input type="date" name="user_visite_med" id="user_visite_med" required>
                    </div>
                    <div class="form-group">
                        <label for="user_aptitude_vue">Aptitude visuelle :</label>
                        <select name="user_aptitude_vue" id="user_aptitude_vue" required>
                            <option value="" disabled selected>-- Sélectionner --</option>
                            <option value="conforme">Conforme</option>
                            <option value="non conforme">Non conforme</option>
                            <option value="avec correction">Avec correction</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_permis_b">Permis B :</label>
                        <select name="user_permis_b" id="user_permis_b" required>
                            <option value="" disabled selected>-- Sélectionner --</option>
                            <option value="oui">Oui</option>
                            <option value="non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_vehicule">Véhicule personnel :</label>
                        <select name="user_vehicule" id="user_vehicule" required>
                            <option value="" disabled selected>-- Sélectionner --</option>
                            <option value="oui">Oui</option>
                            <option value="non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_port_uniforme">Port de l'uniforme accepté :</label>
                        <select name="user_port_uniforme" id="user_port_uniforme" required>
                            <option value="" disabled selected>-- Sélectionner --</option>
                            <option value="oui">Oui</option>
                            <option value="non">Non</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_type_contrat">Type de contrat souhaité :</label>
                        <select name="user_type_contrat" id="user_type_contrat">
                            <option value="">-- Non renseigné --</option>
                            <option value="CDI">CDI</option>
                            <option value="CDD">CDD</option>
                            <option value="Intérim">Intérim</option>
                            <option value="Vacation">Vacation</option>
                            <option value="Temps partiel">Temps partiel</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="user_mobilite_rayon">Rayon de mobilité (en km) :</label>
                        <input type="number" name="user_mobilite_rayon" id="user_mobilite_rayon" placeholder="Ex : 50" min="0" max="999">
                    </div>
                    <div class="form-group">
                        <label for="user_disponibilites">Disponibilités :</label>
                        <textarea name="user_disponibilites" id="user_disponibilites" rows="4" placeholder="Ex : Lundi au vendredi, disponible le week-end..."></textarea>
                    </div>

                    <!-- ===== PIÈCES JUSTIFICATIVES ===== -->
                    <h2>Pièces justificatives</h2>

                    <div class="form-group">
                        <label for="user_cv">CV (PDF) :</label>
                        <!-- Restreint visuellement le choix aux fichiers .pdf dans l'explorateur -->
                        <input type="file" name="user_cv" id="user_cv" accept=".pdf" required>
                    </div>
                    <div class="form-group">
                        <label for="user_lm">Lettre de motivation (PDF) :</label>
                        <input type="file" name="user_lm" id="user_lm" accept=".pdf" required>
                    </div>
                    <div class="form-group">
                        <label for="user_casier">Casier judiciaire (PDF) :</label>
                        <input type="file" name="user_casier" id="user_casier" accept=".pdf" required>
                    </div>

                    <!-- ===== SÉCURITÉ DU COMPTE ===== -->
                    <h2>Sécurité du compte</h2>

                    <div class="form-group">
                        <label for="user_email">Adresse email :</label>
                        <input type="email" name="user_email" id="user_email" placeholder="votre@email.fr" required>
                    </div>
                    <div class="form-group">
                        <label for="user_mot_de_passe">Mot de passe :</label>
                        <input type="password" name="user_mot_de_passe" id="user_mot_de_passe" placeholder="8 caractères minimum" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label for="user_confirmation_mot_de_passe">Confirmation du mot de passe :</label>
                        <input type="password" name="user_confirmation_mot_de_passe" id="user_confirmation_mot_de_passe" required>
                    </div>

                    <button type="submit" class="btn-submit">Envoyer la demande</button>
                </form>

            <?php endif; ?>
        </section>
    </main>

    <script>
    // Base de données des nationalités du monde avec les codes ISO correspondants de FlagCDN
    const NATIONALITES = [
        {label:"Afghane",flag:"af"},{label:"Albanaise",flag:"al"},{label:"Algérienne",flag:"dz"},
        {label:"Allemande",flag:"de"},{label:"Andorrane",flag:"ad"},{label:"Angolaise",flag:"ao"},
        {label:"Antiguaise",flag:"ag"},{label:"Argentine",flag:"ar"},{label:"Arménienne",flag:"am"},
        {label:"Australienne",flag:"au"},{label:"Autrichienne",flag:"at"},{label:"Azerbaïdjanaise",flag:"az"},
        {label:"Bahamienne",flag:"bs"},{label:"Bahreïnie",flag:"bh"},{label:"Bangladaise",flag:"bd"},
        {label:"Barbadienne",flag:"bb"},{label:"Bélarusse",flag:"by"},{label:"Belge",flag:"be"},
        {label:"Bélizéenne",flag:"bz"},{label:"Béninoise",flag:"bj"},{label:"Bhoutanaise",flag:"bt"},
        {label:"Bolivienne",flag:"bo"},{label:"Bosnienne",flag:"ba"},{label:"Botswanaise",flag:"bw"},
        {label:"Brésilienne",flag:"br"},{label:"Britannique",flag:"gb"},{label:"Brunéienne",flag:"bn"},
        {label:"Bulgare",flag:"bg"},{label:"Burkinabè",flag:"bf"},{label:"Burundaise",flag:"bi"},
        {label:"Cambodgienne",flag:"kh"},{label:"Camerounaise",flag:"cm"},{label:"Canadienne",flag:"ca"},
        {label:"Cap-verdienne",flag:"cv"},{label:"Centrafricaine",flag:"cf"},{label:"Chilienne",flag:"cl"},
        {label:"Chinoise",flag:"cn"},{label:"Chypriote",flag:"cy"},{label:"Colombienne",flag:"co"},
        {label:"Comorienne",flag:"km"},{label:"Congolaise",flag:"cg"},{label:"Congolaise (RDC)",flag:"cd"},
        {label:"Coréenne du Nord",flag:"kp"},{label:"Coréenne du Sud",flag:"kr"},{label:"Costaricaine",flag:"cr"},
        {label:"Croate",flag:"hr"},{label:"Cubaine",flag:"cu"},{label:"Danoise",flag:"dk"},
        {label:"Djiboutienne",flag:"dj"},{label:"Dominicaine",flag:"do"},{label:"Dominiquaise",flag:"dm"},
        {label:"Égyptienne",flag:"eg"},{label:"Émiratie",flag:"ae"},{label:"Équatoriale-guinéenne",flag:"gq"},
        {label:"Équatorienne",flag:"ec"},{label:"Érythréenne",flag:"er"},{label:"Espagnole",flag:"es"},
        {label:"Est-timoraise",flag:"tl"},{label:"Estonienne",flag:"ee"},{label:"Éthiopienne",flag:"et"},
        {label:"Fidjienne",flag:"fj"},{label:"Finlandaise",flag:"fi"},{label:"Française",flag:"fr"},
        {label:"Gabonaise",flag:"ga"},{label:"Gambienne",flag:"gm"},{label:"Géorgienne",flag:"ge"},
        {label:"Ghanéenne",flag:"gh"},{label:"Grecque",flag:"gr"},{label:"Grenadine",flag:"gd"},
        {label:"Guatémaltèque",flag:"gt"},{label:"Guinéenne",flag:"gn"},{label:"Guinéenne-bissauane",flag:"gw"},
        {label:"Guyanienne",flag:"gy"},{label:"Haïtienne",flag:"ht"},{label:"Hondurienne",flag:"hn"},
        {label:"Hongroise",flag:"hu"},{label:"Indienne",flag:"in"},{label:"Indonésienne",flag:"id"},
        {label:"Irakienne",flag:"iq"},{label:"Iranienne",flag:"ir"},{label:"Irlandaise",flag:"ie"},
        {label:"Islandaise",flag:"is"},{label:"Israélienne",flag:"il"},{label:"Italienne",flag:"it"},
        {label:"Ivoirienne",flag:"ci"},{label:"Jamaïcaine",flag:"jm"},{label:"Japonaise",flag:"jp"},
        {label:"Jordanienne",flag:"jo"},{label:"Kazakhstanaise",flag:"kz"},{label:"Kényane",flag:"ke"},
        {label:"Kirghize",flag:"kg"},{label:"Kiribatienne",flag:"ki"},{label:"Koweïtienne",flag:"kw"},
        {label:"Laotienne",flag:"la"},{label:"Lesothane",flag:"ls"},{label:"Lettone",flag:"lv"},
        {label:"Libanaise",flag:"lb"},{label:"Libérienne",flag:"lr"},{label:"Libyenne",flag:"ly"},
        {label:"Liechtensteinoise",flag:"li"},{label:"Lituanienne",flag:"lt"},{label:"Luxembourgeoise",flag:"lu"},
        {label:"Macédonienne",flag:"mk"},{label:"Malgache",flag:"mg"},{label:"Malaisienne",flag:"my"},
        {label:"Malawienne",flag:"mw"},{label:"Maldivienne",flag:"mv"},{label:"Malienne",flag:"ml"},
        {label:"Maltaise",flag:"mt"},{label:"Marocaine",flag:"ma"},{label:"Marshallaise",flag:"mh"},
        {label:"Mauritanienne",flag:"mr"},{label:"Mauricienne",flag:"mu"},{label:"Mexicaine",flag:"mx"},
        {label:"Micronésienne",flag:"fm"},{label:"Moldave",flag:"md"},{label:"Monégasque",flag:"mc"},
        {label:"Mongole",flag:"mn"},{label:"Monténégrine",flag:"me"},{label:"Mozambicaine",flag:"mz"},
        {label:"Namibienne",flag:"na"},{label:"Nauruane",flag:"nr"},{label:"Népalaise",flag:"np"},
        {label:"Nicaraguayenne",flag:"ni"},{label:"Nigériane",flag:"ng"},{label:"Nigérienne",flag:"ne"},
        {label:"Niouéane",flag:"nu"},{label:"Norvégienne",flag:"no"},{label:"Néo-zélandaise",flag:"nz"},
        {label:"Omanaise",flag:"om"},{label:"Ougandaise",flag:"ug"},{label:"Ouzbèke",flag:"uz"},
        {label:"Pakistanaise",flag:"pk"},{label:"Palaosienne",flag:"pw"},{label:"Palestinienne",flag:"ps"},
        {label:"Panaméenne",flag:"pa"},{label:"Papouasienne",flag:"pg"},{label:"Paraguayenne",flag:"py"},
        {label:"Péruvienne",flag:"pe"},{label:"Philippine",flag:"ph"},{label:"Polonaise",flag:"pl"},
        {label:"Portugaise",flag:"pt"},{label:"Qatarienne",flag:"qa"},{label:"Roumaine",flag:"ro"},
        {label:"Ruandaise",flag:"rw"},{label:"Russe",flag:"ru"},{label:"Saint-Kitts-et-Névicienne",flag:"kn"},
        {label:"Saint-Lucienne",flag:"lc"},{label:"Saint-Marinaise",flag:"sm"},{label:"Saint-Vincentaise",flag:"vc"},
        {label:"Salomonaise",flag:"sb"},{label:"Salvadorienne",flag:"sv"},{label:"Samoane",flag:"ws"},
        {label:"Santoméenne",flag:"st"},{label:"Saoudienne",flag:"sa"},{label:"Sénégalaise",flag:"sn"},
        {label:"Serbe",flag:"rs"},{label:"Seychelloise",flag:"sc"},{label:"Sierra-léonaise",flag:"sl"},
        {label:"Singapourienne",flag:"sg"},{label:"Slovaque",flag:"sk"},{label:"Slovène",flag:"si"},
        {label:"Somalienne",flag:"so"},{label:"Soudanaise",flag:"sd"},{label:"Soudanaise du Sud",flag:"ss"},
        {label:"Sri-lankaise",flag:"lk"},{label:"Suédoise",flag:"se"},{label:"Suisse",flag:"ch"},
        {label:"Surinamaise",flag:"sr"},{label:"Swazilandaise",flag:"sz"},{label:"Syrienne",flag:"sy"},
        {label:"Tadjike",flag:"tj"},{label:"Tanzanienne",flag:"tz"},{label:"Tchadienne",flag:"td"},
        {label:"Tchèque",flag:"cz"},{label:"Thaïlandaise",flag:"th"},{label:"Togolaise",flag:"tg"},
        {label:"Tongane",flag:"to"},{label:"Trinidadienne",tt:"tt"},{label:"Tunisienne",flag:"tn"},
        {label:"Turkmène",flag:"tm"},{label:"Turque",flag:"tr"},{label:"Tuvaluane",flag:"tv"},
        {label:"Ukrainienne",flag:"ua"},{label:"Uruguayenne",flag:"uy"},{label:"Vanuatuane",flag:"vu"},
        {label:"Vaticanaise",flag:"va"},{label:"Vénézuélienne",flag:"ve"},{label:"Vietnamienne",flag:"vn"},
        {label:"Yéménite",flag:"ye"},{label:"Zambienne",flag:"zm"},{label:"Zimbabwéenne",flag:"zw"}
    ];

    // Liaison des éléments DOM
    const natDisplay  = document.getElementById('nat-display');
    const natDropdown = document.getElementById('nat-dropdown');
    const natSearch   = document.getElementById('nat-search');
    const natList     = document.getElementById('nat-list');
    const natHidden   = document.getElementById('selected-nat');
    const natFlag     = document.getElementById('nat-flag-preview');
    const natLabel    = document.getElementById('nat-label-preview');
    const natNoResult = document.getElementById('nat-no-result');

    /**
     * Génère et filtre les éléments HTML de la liste de nationalités
     * @param {string} query - Terme recherché par l'utilisateur
     */
    function renderList(query) {
        const q = query.toLowerCase().trim();
        // Filtrage du tableau d'objets selon la chaîne recherchée
        const filtered = q
            ? NATIONALITES.filter(n => n.label.toLowerCase().includes(q))
            : NATIONALITES;

        // Reset visuel du menu déroulant
        natList.innerHTML = '';
        // Gestion de l'affichage du message de non-résultat
        natNoResult.style.display = filtered.length === 0 ? 'block' : 'none';

        // Boucle d'injection des éléments de liste <li>
        filtered.forEach(n => {
            const li = document.createElement('li');
            if (n.label === natHidden.value) li.classList.add('selected'); // Marquer l'élément actuellement choisi
            li.innerHTML = `<img src="https://flagcdn.com/16x12/${n.flag}.png" alt="${n.label}"> ${n.label}`;
            
            // Événement au clic sur une nationalité : mise à jour des champs et fermeture
            li.addEventListener('click', () => {
                natHidden.value  = n.label; // Assigne la valeur à l'input caché posté au PHP
                natFlag.src      = `https://flagcdn.com/16x12/${n.flag}.png`;
                natFlag.alt      = n.label;
                natLabel.textContent = n.label;
                closeDropdown();
            });
            natList.appendChild(li);
        });
    }

    // Ouvre le menu déroulant et focus l'input text de recherche
    function openDropdown() {
        natDropdown.style.display = 'block';
        natSearch.value = '';
        renderList('');
        natSearch.focus();
        setTimeout(() => {
            // Fait défiler automatiquement le menu interne jusqu'à l'option déjà sélectionnée
            const sel = natList.querySelector('.selected');
            if (sel) sel.scrollIntoView({ block: 'nearest' });
        }, 50);
    }

    // Ferme le menu déroulant
    function closeDropdown() {
        natDropdown.style.display = 'none';
    }

    // Toggle d'ouverture/fermeture au clic sur le sélecteur
    natDisplay.addEventListener('click', () => {
        natDropdown.style.display === 'none' ? openDropdown() : closeDropdown();
    });

    // Écouteur de saisie dans l'input pour filtrer en temps réel
    natSearch.addEventListener('input', () => renderList(natSearch.value));

    // Fermeture automatique du menu déroulant si l'utilisateur clique en dehors du composant
    document.addEventListener('click', (e) => {
        if (!document.getElementById('nat-wrapper').contains(e.target)) closeDropdown();
    });

    // Gestion de l'accessibilité au clavier depuis l'input recherche (Touche Bas et Échap)
    natSearch.addEventListener('keydown', (e) => {
        const items = [...natList.querySelectorAll('li')];
        const focused = natList.querySelector('li:focus');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            (focused ? focused.nextElementSibling || items[0] : items[0])?.focus();
        } else if (e.key === 'Escape') {
            closeDropdown();
        }
    });

    // Navigation clavier complète au sein de la liste des pays (Bas, Haut, Entrée, Échap)
    natList.addEventListener('keydown', (e) => {
        const items = [...natList.querySelectorAll('li')];
        const idx   = items.indexOf(document.activeElement);
        if (e.key === 'ArrowDown')  { e.preventDefault(); items[(idx + 1) % items.length]?.focus(); }
        if (e.key === 'ArrowUp')    { e.preventDefault(); idx > 0 ? items[idx - 1].focus() : natSearch.focus(); }
        if (e.key === 'Enter')      { e.preventDefault(); document.activeElement.click(); }
        if (e.key === 'Escape')     { closeDropdown(); }
    });

    // Initialisation par défaut de la liste au chargement initial
    renderList('');
    </script>

    <!-- Pied de page commun -->
    <footer>
        <div class="footer-bottom">
            <p>&copy; 2026 IBG FIRE ET SECURE. Tous droits réservés.</p>
        </div>
    </footer>
</body>
</html>