CREATE TABLE Entreprise(
   Id_Entreprise INT AUTO_INCREMENT,
   Nom_Entreprise VARCHAR(100) NOT NULL,
   Siret_Entreprise VARCHAR(14),
   Code_NAF_Entreprise VARCHAR(5),
   Numero_TVA_Entreprise VARCHAR(15),
   Telephone_Entreprise VARCHAR(50),
   Numero_voie_Entreprise VARCHAR(50),
   Nom_Voie_Entreprise VARCHAR(50),
   Complement_ VARCHAR(50),
   Ville_Entreprise VARCHAR(50),
   Pays_Entreprise VARCHAR(50),
   Nom_Referent_Entreprise VARCHAR(100),
   Fonction_Referent_Entreprise VARCHAR(100),
   Email_Contact_Entreprise VARCHAR(100),
   Date_Creation_Inscription_Entreprise DATE,
   PRIMARY KEY(Id_Entreprise)
) ENGINE=InnoDB;

CREATE TABLE Employe(
   Id_Employe INT AUTO_INCREMENT,
   Nom_Employe VARCHAR(50) NOT NULL,
   Prenom_Employe VARCHAR(50) NOT NULL,
   Date_Naissance_Employe DATE,
   Nationalite_Employe VARCHAR(50),
   Telephone_Employe VARCHAR(50),
   Lieu_Naissance_Employe VARCHAR(100),
   Numero_CNAPS_Employe VARCHAR(50),
   Expiration_CNAPS_Employe DATE,
   Casier_Path_Employe VARCHAR(255), -- Augmenté pour les chemins de fichiers longs
   Date_Visite_Med_Employe DATE,
   Permis_b_Employe VARCHAR(50),
   Vehicule_Employe VARCHAR(50),
   Aptitude_Vue_Employe VARCHAR(50),
   Type_De_Contrat_Employe VARCHAR(50),
   Disponibilites_Employe VARCHAR(255),
   Mobilite_Rayon_Employe INT,
   Port_Uniforme_Employe VARCHAR(50),
   CV_Path_Employe VARCHAR(255),
   Lettre_De_Motivation_Path_Employe VARCHAR(255),
   Date_Inscription_Employe DATE,
   PRIMARY KEY(Id_Employe)
) ENGINE=InnoDB;

CREATE TABLE Service(
   Id_Service INT AUTO_INCREMENT,
   Nom_Service VARCHAR(50) NOT NULL,
   Description_Service VARCHAR(255),
   Date_Creation_Service DATE,
   PRIMARY KEY(Id_Service)
) ENGINE=InnoDB;

CREATE TABLE Utilisateur(
   Id_Utilisateur INT AUTO_INCREMENT,
   Nom_Utilisateur VARCHAR(50) NOT NULL,
   Email_Utilisateur VARCHAR(100) NOT NULL, -- Augmenté à 100
   Mot_De_Passe_Utilisateur VARCHAR(255) NOT NULL, -- Augmenté à 255 pour password_hash()
   Role VARCHAR(50) NOT NULL, -- Ex: 'admin', 'entreprise', 'employe'
   Statut_Comopte_Utilisateur VARCHAR(50) DEFAULT 'en attente',
   reset_token VARCHAR(100),
   reset_expires DATETIME, -- Changé en DATETIME pour gérer l'heure d'expiration exacte
   Date_Creation_Utilisateur DATE,
   PRIMARY KEY(Id_Utilisateur),
   UNIQUE(Email_Utilisateur)
) ENGINE=InnoDB;

CREATE TABLE Demande_service(
   Id_Demande_Service INT AUTO_INCREMENT,
   Email_Demandeur_Demande_Service VARCHAR(100),
   Message_Demande_Service TEXT, -- Changé en TEXT pour éviter la coupure des longs messages
   Statut_Demande_Service VARCHAR(50) DEFAULT 'en attente',
   Date_Demande_Service DATE,
   Id_Entreprise INT NOT NULL,
   PRIMARY KEY(Id_Demande_Service),
   FOREIGN KEY(Id_Entreprise) REFERENCES Entreprise(Id_Entreprise) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Candidature(
   Id_Candidature INT AUTO_INCREMENT,
   Lettre_Motivation_Candidature TEXT, -- Passage en TEXT si c'est du texte, ou VARCHAR(255) si c'est un chemin de fichier
   Statut_Candidature VARCHAR(50) DEFAULT 'en attente',
   Date_Candidature DATE,
   Id_Utilisateur INT NOT NULL,
   PRIMARY KEY(Id_Candidature),
   FOREIGN KEY(Id_Utilisateur) REFERENCES Utilisateur(Id_Utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Mission(
   Id_Mission INT AUTO_INCREMENT,
   Titre_Mission VARCHAR(100) NOT NULL,
   Description_Mission TEXT,
   Statut_Mission VARCHAR(50),
   Date_Creation_Mission DATE,
   Id_Service INT NOT NULL,
   Id_Candidature INT NOT NULL,
   PRIMARY KEY(Id_Mission),
   FOREIGN KEY(Id_Service) REFERENCES Service(Id_Service),
   FOREIGN KEY(Id_Candidature) REFERENCES Candidature(Id_Candidature)
) ENGINE=InnoDB;

-- ==========================================
-- TABLES DE LIAISON (RELATIONS ENTRAÎNÉES)
-- ==========================================

CREATE TABLE Solliciter(
   Id_Entreprise INT,
   Id_Service INT,
   PRIMARY KEY(Id_Entreprise, Id_Service),
   FOREIGN KEY(Id_Entreprise) REFERENCES Entreprise(Id_Entreprise) ON DELETE CASCADE,
   FOREIGN KEY(Id_Service) REFERENCES Service(Id_Service) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Postuler(
   Id_Employe INT,
   Id_Mission INT,
   PRIMARY KEY(Id_Employe, Id_Mission),
   FOREIGN KEY(Id_Employe) REFERENCES Employe(Id_Employe) ON DELETE CASCADE,
   FOREIGN KEY(Id_Mission) REFERENCES Mission(Id_Mission) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Representer(
   Id_Entreprise INT,
   Id_Utilisateur INT,
   PRIMARY KEY(Id_Entreprise, Id_Utilisateur),
   FOREIGN KEY(Id_Entreprise) REFERENCES Entreprise(Id_Entreprise) ON DELETE CASCADE,
   FOREIGN KEY(Id_Utilisateur) REFERENCES Utilisateur(Id_Utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Incarner(
   Id_Employe INT,
   Id_Utilisateur INT,
   PRIMARY KEY(Id_Employe, Id_Utilisateur),
   FOREIGN KEY(Id_Employe) REFERENCES Employe(Id_Employe) ON DELETE CASCADE,
   FOREIGN KEY(Id_Utilisateur) REFERENCES Utilisateur(Id_Utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE Recu(
   Id_Service INT,
   Id_Demande_Service INT,
   PRIMARY KEY(Id_Service, Id_Demande_Service),
   FOREIGN KEY(Id_Service) REFERENCES Service(Id_Service) ON DELETE CASCADE,
   FOREIGN KEY(Id_Demande_Service) REFERENCES Demande_service(Id_Demande_Service) ON DELETE CASCADE
) ENGINE=InnoDB;