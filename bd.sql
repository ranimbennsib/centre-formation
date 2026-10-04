-- 1. SUPPRIMER TOUT
DROP TABLE IF EXISTS inscription;
DROP TABLE IF EXISTS session;
DROP TABLE IF EXISTS formateur;
DROP TABLE IF EXISTS formation;
DROP TABLE IF EXISTS utilisateur;

-- 2. CRÉER LES TABLES
CREATE TABLE utilisateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe_hash VARCHAR(255) NOT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    role ENUM('EMPLOYE', 'FORMATEUR', 'ADMIN') DEFAULT 'EMPLOYE',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE formation (
    id INT PRIMARY KEY AUTO_INCREMENT,
    titre VARCHAR(200) NOT NULL,
    description TEXT,
    duree_heures INT DEFAULT 20,
    competences_visees TEXT,
    prix DECIMAL(10,2) DEFAULT 0.00,
    est_actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE formateur (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT UNIQUE NOT NULL,
    specialite VARCHAR(100),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id)
);
CREATE TABLE employe (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_utilisateur INT NOT NULL,
    poste VARCHAR(100),
    departement VARCHAR(100),
    date_embauche DATE,
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id) ON DELETE CASCADE
);

CREATE TABLE session (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_formation INT NOT NULL,
    id_formateur INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    salle VARCHAR(50),
    places_max INT DEFAULT 20,
    places_restantes INT DEFAULT 20,
    statut_formateur ENUM('EN_ATTENTE', 'ACCEPTEE', 'REFUSEE') DEFAULT 'EN_ATTENTE',
    raison_refus_formateur TEXT,
    FOREIGN KEY (id_formation) REFERENCES formation(id),
    FOREIGN KEY (id_formateur) REFERENCES formateur(id)
);

CREATE TABLE inscription (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_employe INT NOT NULL,
    id_session INT NOT NULL,
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('EN_ATTENTE', 'VALIDEE', 'REFUSEE', 'ANNULEE') DEFAULT 'EN_ATTENTE',
    raison_refus TEXT,
    FOREIGN KEY (id_employe) REFERENCES utilisateur(id),
    FOREIGN KEY (id_session) REFERENCES session(id)
);

--- 3. Insérer les utilisateurs (MÊME QUE L'AUTRE PROJET)
INSERT INTO utilisateur (email, mot_de_passe_hash, nom, prenom, role) VALUES
-- Mots de passe : tous = "password" (comme test simple)
('admin@centre.tn', '$2y$10$2Nm.GwaB8E8K6enpZyp7xesve93SZy5D5fv0TqhqG4772bOSa1Kou', 'Dupont', 'Jean', 'ADMIN'),--admin123
('formateur@centre.tn', '$2y$10$IPESmDrnrHcoHNsZk0edQ.ULapyNRGApSFsnZ9hdH7exMHYtlylza', 'Martin', 'Sophie', 'FORMATEUR'),--formateur1123
('employe@centre.tn', '$2y$10$UyBYMoUKj.j5SWYgvzIVc.hVmEZ2phDlc8jM5lVk9c5jKr8kb2sWG', 'Bernard', 'Alice', 'EMPLOYE');--employe123
