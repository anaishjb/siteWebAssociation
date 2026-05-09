-- ============================================================
-- LDS Association — base de données complète
-- Base cible : p27_anais
-- À importer dans phpMyAdmin — la base doit déjà exister
-- Cet import repart de zéro : les tables existantes sont supprimées
-- ============================================================

SET NAMES utf8mb4;

-- ============================================================
-- SUPPRESSION DES TABLES EXISTANTES (pour repartir proprement)
-- ============================================================

DROP TABLE IF EXISTS admin_utilisateurs;
DROP TABLE IF EXISTS contact_messages;
DROP TABLE IF EXISTS partenaires;
DROP TABLE IF EXISTS evenements;
DROP TABLE IF EXISTS poles;
DROP TABLE IF EXISTS poles_intro_textes;
DROP TABLE IF EXISTS missions_reve;
DROP TABLE IF EXISTS missions_besoins;
DROP TABLE IF EXISTS missions_objectifs;
DROP TABLE IF EXISTS missions_intro;
DROP TABLE IF EXISTS cartes_accueil;
DROP TABLE IF EXISTS hero;


-- ============================================================
-- PAGE ACCUEIL
-- ============================================================

CREATE TABLE hero (
  id           INT          PRIMARY KEY AUTO_INCREMENT,
  image_src    VARCHAR(255) NOT NULL,
  image_alt    VARCHAR(255) NOT NULL,
  titre        VARCHAR(255) NOT NULL,
  texte        TEXT         NOT NULL,
  bouton_href  VARCHAR(255) NOT NULL,
  bouton_texte VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cartes_accueil (
  id        INT          PRIMARY KEY AUTO_INCREMENT,
  section   ENUM('poles', 'evenements', 'actions') NOT NULL,
  titre     VARCHAR(255) NOT NULL,
  texte     TEXT         NOT NULL,
  lien_href VARCHAR(255) DEFAULT NULL,
  ordre     TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGE MISSIONS
-- ============================================================

CREATE TABLE missions_intro (
  id        INT          PRIMARY KEY AUTO_INCREMENT,
  image_src VARCHAR(255) NOT NULL,
  image_alt VARCHAR(255) NOT NULL,
  titre     VARCHAR(255) NOT NULL,
  texte1    TEXT         NOT NULL,
  texte2    TEXT         NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE missions_objectifs (
  id    INT          PRIMARY KEY AUTO_INCREMENT,
  titre VARCHAR(255) NOT NULL,
  texte TEXT         NOT NULL,
  ordre TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE missions_besoins (
  id    INT          PRIMARY KEY AUTO_INCREMENT,
  titre VARCHAR(255) NOT NULL,
  texte TEXT         NOT NULL,
  ordre TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE missions_reve (
  id        INT          PRIMARY KEY AUTO_INCREMENT,
  titre     VARCHAR(255) NOT NULL,
  texte1    TEXT         NOT NULL,
  texte2    TEXT         NOT NULL,
  image_src VARCHAR(255) NOT NULL,
  image_alt VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGE PÔLES
-- ============================================================

CREATE TABLE poles_intro_textes (
  id    INT  PRIMARY KEY AUTO_INCREMENT,
  texte TEXT NOT NULL,
  ordre TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE poles (
  id        INT          PRIMARY KEY AUTO_INCREMENT,
  titre     VARCHAR(255) NOT NULL,
  image_src VARCHAR(255) NOT NULL,
  image_alt VARCHAR(255) NOT NULL,
  texte1    TEXT         NOT NULL,
  texte2    TEXT         NOT NULL,
  ordre     TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGE ÉVÉNEMENTS
-- ============================================================

CREATE TABLE evenements (
  id          INT          PRIMARY KEY AUTO_INCREMENT,
  titre       VARCHAR(255) NOT NULL,
  description TEXT         NOT NULL,
  date_event  DATE         NOT NULL,
  lieu        VARCHAR(255) DEFAULT NULL,
  image_src   VARCHAR(255) DEFAULT NULL,
  image_alt   VARCHAR(255) DEFAULT NULL,
  statut      ENUM('prochain', 'passe') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGE PARTENAIRES
-- ============================================================

CREATE TABLE partenaires (
  id               INT          PRIMARY KEY AUTO_INCREMENT,
  nom              VARCHAR(255) NOT NULL,
  description      TEXT         NOT NULL,
  type_partenariat VARCHAR(100) NOT NULL,
  logo_src         VARCHAR(255) DEFAULT NULL,
  logo_alt         VARCHAR(255) DEFAULT NULL,
  lien_site        VARCHAR(255) DEFAULT NULL,
  ordre            TINYINT UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PAGE CONTACT — messages reçus
-- ============================================================

CREATE TABLE contact_messages (
  id         INT          PRIMARY KEY AUTO_INCREMENT,
  nom        VARCHAR(100) NOT NULL,
  prenom     VARCHAR(100) NOT NULL,
  email      VARCHAR(255) NOT NULL,
  sujet      VARCHAR(255) NOT NULL,
  message    TEXT         NOT NULL,
  date_envoi DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  lu         TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- ADMIN — compte du responsable
-- ============================================================

CREATE TABLE admin_utilisateurs (
  id                INT          PRIMARY KEY AUTO_INCREMENT,
  identifiant       VARCHAR(100) NOT NULL UNIQUE,
  mot_de_passe_hash VARCHAR(255) NOT NULL,
  cree_le           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Mot de passe par défaut : LDS2024
-- À changer dès la première connexion via la page admin
INSERT INTO admin_utilisateurs (identifiant, mot_de_passe_hash) VALUES (
  'admin',
  '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
);


-- ============================================================
-- DONNÉES INITIALES — Accueil
-- ============================================================

INSERT INTO hero (image_src, image_alt, titre, texte, bouton_href, bouton_texte) VALUES (
  'images/photo2lds.jpeg',
  'Membres de LDS Association lors d\'une action solidaire',
  'Sport & Solidarité',
  'Notre association organise des événements sportifs pour rassembler les personnes autour du sport, du partage et de la solidarité. Ces événements permettent de collecter des fonds qui servent ensuite à financer des actions solidaires. Grâce à cet engagement, nous pouvons aider les familles et les personnes en difficulté en leur apportant un soutien concret au quotidien.',
  'evenements.php',
  'Découvrez nos événements'
);

INSERT INTO cartes_accueil (section, titre, texte, lien_href, ordre) VALUES
  ('poles', 'Pôle Événement',
   'Nous organisons des événements sportifs pour rassembler les gens et soutenir des actions solidaires. Ces moments permettent de partager, s\'amuser et aider les autres en même temps.',
   NULL, 1),
  ('poles', 'Pôle Action Solidaire',
   'Nous mettons en place des actions pour aider les personnes qui en ont besoin. Grâce aux dons et aux événements, nous finançons et réalisons des projets solidaires.',
   NULL, 2),
  ('evenements', 'Prochain événement',
   'Découvrez nos prochains événements sportifs.',
   'prochain-evenement.php', 1),
  ('evenements', 'Événements passés',
   'Revivez nos événements précédents.',
   'evenements-passes.php', 2),
  ('actions', 'Nos missions',
   'Nos missions sont d\'aider les personnes qui en ont besoin et de créer des actions solidaires. Nous voulons aussi rassembler les gens autour du sport et du partage.',
   NULL, 1),
  ('actions', 'Nos partenaires',
   'Nos partenaires nous accompagnent et nous soutiennent dans nos projets. Grâce à leur aide, nous pouvons organiser des événements et réaliser nos actions solidaires.',
   NULL, 2);


-- ============================================================
-- DONNÉES INITIALES — Missions
-- ============================================================

INSERT INTO missions_intro (image_src, image_alt, titre, texte1, texte2) VALUES (
  'images/photo2lds.jpeg',
  'Membres de LDS Association lors d\'une action solidaire',
  'Nos missions',
  'Derrière chaque action de LDS, il y a une ambition claire : donner à la solidarité une forme réelle et utile.',
  'Nous voulons aider les familles, soutenir les personnes en difficulté et créer des moments de partage autour du sport.'
);

INSERT INTO missions_objectifs (titre, texte, ordre) VALUES
  ('Créer des événements qui rassemblent',
   'Organiser des moments sportifs et culturels accessibles à tous, pour permettre aux personnes de se rencontrer.', 1),
  ('Financer des actions solidaires',
   'Utiliser les fonds récoltés pour soutenir les familles, les enfants et les personnes qui en ont besoin.', 2),
  ('Soutenir les familles au quotidien',
   'Accompagner les personnes dans des besoins simples : aide alimentaire, vêtements, fournitures ou soutien moral.', 3),
  ('Devenir une référence locale',
   'Être connu sur notre territoire comme une association utile, humaine et proche des habitants.', 4);

INSERT INTO missions_besoins (titre, texte, ordre) VALUES
  ('Obtenir un local associatif',
   'Avoir un local nous permettrait de stocker les dons, préparer nos actions et accueillir les personnes dans de meilleures conditions.', 1),
  ('Présence dans le magazine municipal',
   'Être visibles dans les supports de la ville nous aiderait à faire connaître nos actions et à toucher plus de personnes.', 2);

INSERT INTO missions_reve (titre, texte1, texte2, image_src, image_alt) VALUES (
  'Notre rêve',
  'Construire, grâce à LDS, une association connue pour son engagement, ses événements et son aide concrète.',
  'Notre rêve est de faire grandir ce projet avec les habitants, les partenaires et les bénévoles.',
  'images/photo-mission.jpg',
  'Jeunes de l\'association lors d\'une activité en extérieur'
);


-- ============================================================
-- DONNÉES INITIALES — Pôles
-- ============================================================

INSERT INTO poles_intro_textes (texte, ordre) VALUES
  ('LDS Association s\'organise autour de deux pôles complémentaires, chacun ayant un rôle précis au service de la même mission. Pour mener nos actions, nous avons structuré l\'association de façon simple : un pôle s\'occupe de l\'organisation des événements sportifs, tandis que l\'autre se concentre sur les actions solidaires.', 1),
  ('Le premier permet de rassembler les personnes autour du sport, de créer du lien et de collecter des fonds. Le second utilise ces ressources pour aider les personnes qui en ont besoin à travers des actions concrètes. Ensemble, ces deux pôles fonctionnent comme un même cycle : le sport devient un moyen de solidarité et de partage.', 2);

INSERT INTO poles (titre, image_src, image_alt, texte1, texte2, ordre) VALUES
  ('Pôle Événement',
   'images/pole-evenement.jpg',
   'Membres de l\'association lors d\'un événement sportif',
   'Le Pôle Événement s\'occupe d\'organiser des activités sportives ouvertes à tous, comme des tournois, des journées sportives ou des défis collectifs. Chaque événement permet de rassembler les habitants du quartier dans une ambiance conviviale et solidaire.',
   'L\'objectif est double : créer du lien entre les personnes et collecter des fonds. Les inscriptions, les sponsors et les dons récoltés pendant ces événements servent ensuite à financer les actions du Pôle Action Solidaire sur le terrain.',
   1),
  ('Pôle Action solidaire',
   'images/pole-solidaire.jpg',
   'Bénévoles pendant une distribution solidaire',
   'Le Pôle Action Solidaire est au cœur de l\'engagement de l\'association. Il transforme les fonds collectés en aide concrète pour les familles et les personnes en difficulté à Tremblay-en-France et dans les communes voisines.',
   'Chaque mois, les bénévoles organisent des maraudes pour distribuer des repas chauds, des vêtements et des kits d\'hygiène aux personnes sans-abri. Deux fois par an, le Shopping Solidaire permet à des familles orientées par les services sociaux de recevoir gratuitement des vêtements, des produits d\'hygiène et des colis alimentaires.',
   2);


-- ============================================================
-- DONNÉES INITIALES — Événements
-- ============================================================

INSERT INTO evenements (titre, description, date_event, lieu, image_src, image_alt, statut) VALUES
  ('Tournoi de football solidaire',
   'Un tournoi ouvert à tous les habitants du quartier. Les inscriptions sont de 5€ par équipe, intégralement reversés au Pôle Action Solidaire. Venez nombreux jouer et soutenir une bonne cause !',
   '2025-06-21',
   'Stade municipal de Tremblay-en-France',
   'images/event1.jpeg',
   'Équipes en train de jouer lors du tournoi de football solidaire',
   'prochain'),
  ('Journée sportive de septembre',
   'Une journée de sports collectifs pour rassembler les familles du quartier autour du sport et du partage. Plusieurs activités étaient proposées : football, basketball et course à pied.',
   '2024-09-14',
   'Parc des sports de Tremblay-en-France',
   'images/event2.jpeg',
   'Participants lors de la journée sportive de septembre 2024',
   'passe'),
  ('Shopping Solidaire hivernal',
   'Distribution gratuite de vêtements chauds, produits d\'hygiène et colis alimentaires à des familles orientées par les services sociaux de Tremblay-en-France, Sevran et Aulnay-sous-Bois.',
   '2024-12-07',
   'Salle des fêtes de Tremblay-en-France',
   NULL, NULL,
   'passe');


-- ============================================================
-- DONNÉES INITIALES — Partenaires
-- ============================================================

INSERT INTO partenaires (nom, description, type_partenariat, logo_src, logo_alt, lien_site, ordre) VALUES
  ('Collège René Descartes',
   'Notre partenaire scolaire nous aide à fournir des fournitures scolaires aux élèves issus de familles en difficulté dans le cadre de notre action de rentrée.',
   'Partenaire associatif',
   NULL, NULL, NULL, 1),
  ('Mairie de Tremblay-en-France',
   'La mairie nous soutient en nous donnant accès à des salles pour nos événements et en relayant nos actions auprès des habitants.',
   'Partenaire institutionnel',
   NULL, NULL, NULL, 2);
