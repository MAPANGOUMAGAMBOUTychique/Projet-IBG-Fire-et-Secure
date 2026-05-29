-- ==========================================
-- 1. CRÉATION DES TABLES INDÉPENDANTES
-- ==========================================

CREATE TABLE IF NOT EXISTS `entreprise` (
   `id_entreprise` INT AUTO_INCREMENT,
   `nom_entreprise` VARCHAR(50),
   `siret_entreprise` VARCHAR(14),
   `code_naf_entreprise` VARCHAR(5),
   `numero_tva_entreprise` VARCHAR(15),
   `telephone_entreprise` VARCHAR(50),
   `numero_voie_entreprise` VARCHAR(50),
   `nom_voie_entreprise` VARCHAR(50),
   `complement_adresse_entreprise` VARCHAR(50),
   `ville_entreprise` VARCHAR(50),
   `pays_entreprise` VARCHAR(50),
   `nom_referent_entreprise` VARCHAR(100),
   `fonction_referent_entreprise` VARCHAR(100),
   `email_contact_entreprise` VARCHAR(100),
   `date_creation_inscription_entreprise` DATE,
   PRIMARY KEY(`id_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `employe` (
   `id_employe` INT AUTO_INCREMENT,
   `nom_employe` VARCHAR(50),
   `prenom_employe` VARCHAR(50),
   `date_naissance_employe` DATE,
   `nationalite_employe` VARCHAR(50),
   `telephone_employe` VARCHAR(50),
   `lieu_naissance_employe` VARCHAR(100),
   `numero_cnaps_employe` VARCHAR(50),
   `expiration_cnaps_employe` DATE,
   `casier_path_employe` VARCHAR(255),
   `date_visite_med_employe` DATE,
   `permis_b_employe` VARCHAR(50),
   `vehicule_employe` VARCHAR(50),
   `aptitude_vue_employe` VARCHAR(50),
   `type_de_contrat_employe` VARCHAR(50),
   `disponibilites_employe` VARCHAR(255),
   `mobilite_rayon_employe` INT,
   `port_uniforme_employe` VARCHAR(50),
   `cv_path_employe` VARCHAR(255),
   `lettre_de_motivation_path_employe` VARCHAR(255),
   `date_inscription_employe` DATE,
   PRIMARY KEY(`id_employe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `utilisateur` (
    `id_utilisateur` INT AUTO_INCREMENT,
    `nom_utilisateur` VARCHAR(50),
    `email_utilisateur` VARCHAR(100),
    `mot_de_passe_utilisateur` VARCHAR(255),
    `role` VARCHAR(50),
    `statut_compte_utilisateur` VARCHAR(50),
    `reset_token` VARCHAR(255),
    `reset_expires` DATETIME,
    `date_creation_utilisateur` DATE,
    PRIMARY KEY(`id_utilisateur`),
    UNIQUE(`email_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `service` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `pole` VARCHAR(100) NOT NULL,
    `titre` VARCHAR(150) NOT NULL,
    `image` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ==========================================
-- 2. INSERTION DES DONNÉES DE LA TABLE SERVICE
-- ==========================================

INSERT INTO `service` (`pole`, `titre`, `image`, `description`) VALUES
('Gardiennage et Surveillance', 'Surveillance Statique et controle d''Accès', 'image/Jour4/Image gardinnage et sécurité-1682125948844-e2dc8996b0f0.avif', 'Nos agents de sécurité qualifiés assurent un contrôle rigoureux des flux de personnes, de véhicules et de marchandises. En combinant vigilance humaine et protocoles de vérification stricts, nous garantissons l''étanchéité de vos périmètres. Qu''il s''agisse de sites industriels sensibles ou de complexes tertiaires, notre mission est de prévenir toute intrusion tout en préservant la fluidité de vos accès quotidiens.'),
('Gardiennage et Surveillance', 'Rondes de Surveillance et Sécurité Mobile', 'image/Jour4/Image rpnde de surveillance et sécurité mobile-1661499169247-81649e8667d8.avif', 'Patrouilles mobiles, aléatoires ou programmées, effectuées en véhicule pour inspecter les points sensibles de vos sites. Nos agents assurent une présence dynamique et une surveillance étendue pour prévenir toute anomalie.'),
('Gardiennage et Surveillance', 'Protection Événementielle', 'image/Jour4/Image protection evenementielle-1760228604788-db8a36d5c1a3.avif', 'Garantissez le succès et la sérénité de vos événements de haut standing. De la sécurisation des salons internationaux aux rassemblements privés exclusifs, nos équipes gèrent avec diplomatie et fermeté le contrôle des accès et la fluidité des flux de foule. We allient discrétion et vigilance pour offer à vos invités un environnement sûr et prestigieux.'),
('Sécurité Incendie', 'Prévention et Intervention Incendie (SSIAP)', 'image/Jour4/Image prévention et intervention incendie-1482173989-612x612.webp', 'Nos agents certifiés SSIAP (niveaux 1, 2 et 3) assurent une veille constante contre les risques d''incendie. Experts en prévention, ils garantissent la conformité de vos installations par une vérification rigoureuse des équipements de secours et assurent une gestion exemplaire de l''évacuation et de la mise en sécurité des occupants en cas de sinistre.'),
('Sécurité Incendie', 'Maintenance des Systèmes de Sécurité Incendie', 'image/Jour4/Image maintenance des systèmes incendies-1482775856-612x612.webp', 'Garantissez l''opérationnalité de vos dispositifs de secours. Nous realizons des audits approfondis et la maintenance technique de vos systèmes de sécurité incendie (SSI) : alarmes, détecteurs de fumée, colonnes sèches et Robinets d''Incendie Armés (RIA). Nos interventions certifiées assurent la conformité de vos installations aux normes en vigueur et une réactivité optimale de vos équipements.'),
('Sécurité Incendie', 'Formation et Exercices d''Évacuation', 'image/Evacuation-1663075966038-6b37cc036924.avif', 'La préparation est la clé d''une gestion efficace des situations d''urgence. Nous formons vos équipes à adopter les bons réflexes et organisons des exercices d''évacuation grandeur nature pour tester vos procédures. En cas d''alerte, chaque seconde compte : nous vous aidons à bâtir une organisation fluide et sécurisée pour protéger toutes les personnes présentes dans vos locaux.'),
('Conseil et Expertise', 'Audit et Conseil en Ingénierie et Sûreté', 'image/Jour4/Image conseil et expertise-1661695279211-dfc3866380d1.avif', 'Anticipez les menaces par une approche analytique de votre sûreté. Nos experts réalisent un audit complet des vulnérabilités de vos infrastructures, englobant les risques humains, techniques et organisationnels. À l''issue de cette étude de terrain, nous concevons un plan de sécurité sur mesure, optimisant vos ressources pour garantir une protection maximale et pérenne de vos sites.'),
('Conseil et Expertise', 'Formation à la Gestion des Risques', 'image/Jour4/Image gestion des risques-1663089690804-1c6d97412b7a.avif', 'Développez une véritable culture de la prévention au sein de vos équipes. Nos experts certifiés forment vos collaborateurs aux gestes qui sauvent et à la manipulation des équipements d''extinction. En maîtrisant les réflexes de premiers secours et l’usage des extincteurs, votre personnel devient le premier maillon de votre chaîne de sécurité, garantissant une réactivité immédiate face à l''accident ou au début d''incendie'),
('Conseil et Expertise', 'Sécurisation des Sites Sensibles', 'image/Jour4/Image sécurisation des sites sensibles-2248999048-612x612.webp', 'Sécurisation des environnements critiques et sites à haut risque. Pour vos entrepôts de grande valeur, chantiers d''envergure ou zones industrielles isolées, nous déployons des dispositifs de protection renforcés. Alliant technologies de pointe et unités d''élite, notre approche garantit une surveillance hermétique de vos actifs les plus sensibles, même dans les conditions les plus exigeantes.');


-- ==========================================
-- 3. CRÉATION DES TABLES AVEC CLÉS ÉTRANGÈRES
-- ==========================================

CREATE TABLE IF NOT EXISTS `candidature` (
   `id_candidature` INT AUTO_INCREMENT,
   `lettre_motivation_candidature` VARCHAR(255),
   `statut_candidature` VARCHAR(50),
   `date_candidature` DATE,
   `id_employe` INT NOT NULL,
   PRIMARY KEY(`id_candidature`),
   FOREIGN KEY(`id_employe`) REFERENCES `employe`(`id_employe`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `demande_service` (
   `id_demande_service` INT AUTO_INCREMENT,
   `email_demandeur_demande_service` VARCHAR(100),
   `message_demande_service` VARCHAR(255),
   `statut_demande_service` VARCHAR(50),
   `date_demande_service` DATE,
   `id_entreprise` INT NOT NULL,
   PRIMARY KEY(`id_demande_service`),
   FOREIGN KEY(`id_entreprise`) REFERENCES `entreprise`(`id_entreprise`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `mission` (
   `id_mission` INT AUTO_INCREMENT,
   `titre_mission` VARCHAR(50),
   `description_mission` VARCHAR(255),
   `statut_mission` VARCHAR(50),
   `date_creation_mission` DATE,
   `id_service` INT NOT NULL,
   `id_candidature` INT NOT NULL,
   PRIMARY KEY(`id_mission`),
   FOREIGN KEY(`id_service`) REFERENCES `service`(`id`),
   FOREIGN KEY(`id_candidature`) REFERENCES `candidature`(`id_candidature`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `solliciter` (
   `id_entreprise` INT,
   `id_service` INT,
   PRIMARY KEY(`id_entreprise`, `id_service`),
   FOREIGN KEY(`id_entreprise`) REFERENCES `entreprise`(`id_entreprise`),
   FOREIGN KEY(`id_service`) REFERENCES `service`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `postuler` (
   `id_employe` INT,
   `id_mission` INT,
   PRIMARY KEY(`id_employe`, `id_mission`),
   FOREIGN KEY(`id_employe`) REFERENCES `employe`(`id_employe`),
   FOREIGN KEY(`id_mission`) REFERENCES `mission`(`id_mission`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `representer` (
   `id_entreprise` INT,
   `id_utilisateur` INT,
   PRIMARY KEY(`id_entreprise`, `id_utilisateur`),
   FOREIGN KEY(`id_entreprise`) REFERENCES `entreprise`(`id_entreprise`),
   FOREIGN KEY(`id_utilisateur`) REFERENCES `utilisateur`(`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `incarner` (
   `id_employe` INT,
   `id_utilisateur` INT,
   PRIMARY KEY(`id_employe`, `id_utilisateur`),
   FOREIGN KEY(`id_employe`) REFERENCES `employe`(`id_employe`),
   FOREIGN KEY(`id_utilisateur`) REFERENCES `utilisateur`(`id_utilisateur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `recu` (
   `id_service` INT,
   `id_demande_service` INT,
   PRIMARY KEY(`id_service`, `id_demande_service`),
   FOREIGN KEY(`id_service`) REFERENCES `service`(`id`),
   FOREIGN KEY(`id_demande_service`) REFERENCES `demande_service`(`id_demande_service`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;