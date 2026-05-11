-- Création de la table Entreprise en mode Root
CREATE TABLE Entreprise(
   Id_Entreprise INT AUTO_INCREMENT,
   Nom_Entreprise VARCHAR(100),
   Siret_Entreprise VARCHAR(14),
   Code_NAF_Entreprise VARCHAR(5),
   Numero_TVA_Entreprise VARCHAR(15),
   Telephone_Entreprise VARCHAR(50),
   Numero_voie_Entreprise VARCHAR(50),
   Nom_Voie_Entreprise VARCHAR(100),
   Complement_ VARCHAR(100),
   Ville_Entreprise VARCHAR(50),
   Pays_Entreprise VARCHAR(50),
   Nom_Referent_Entreprise VARCHAR(100),
   Fonction_Referent_Entreprise VARCHAR(100),
   Email_Contact_Entreprise VARCHAR(100),
   Date_Creation_Inscription_Entreprise DATE,
   PRIMARY KEY(Id_Entreprise)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;