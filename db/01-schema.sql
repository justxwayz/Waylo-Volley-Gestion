CREATE DATABASE IF NOT EXISTS volley CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE volley;

CREATE TABLE membre (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nom VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  mot_de_passe VARCHAR(255) NOT NULL,
  role ENUM('membre','admin') NOT NULL DEFAULT 'membre',
  actif TINYINT(1) NOT NULL DEFAULT 1,
  cree_le DATETIME NOT NULL
) ENGINE=InnoDB;

CREATE TABLE seance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  debut DATETIME NOT NULL,
  fin DATETIME NOT NULL,
  lieu VARCHAR(150) NOT NULL,
  statut ENUM('prevue','annulee') NOT NULL DEFAULT 'prevue',
  cree_le DATETIME NOT NULL,
  INDEX idx_seance_debut (debut)
) ENGINE=InnoDB;

CREATE TABLE presence (
  id INT AUTO_INCREMENT PRIMARY KEY,
  membre_id INT NOT NULL,
  seance_id INT NOT NULL,
  reponse ENUM('present','absent','peut_etre') NULL,
  reponse_le DATETIME NULL,
  venu TINYINT(1) NULL,
  UNIQUE KEY uniq_presence (membre_id, seance_id),
  FOREIGN KEY (membre_id) REFERENCES membre(id),
  FOREIGN KEY (seance_id) REFERENCES seance(id)
) ENGINE=InnoDB;
