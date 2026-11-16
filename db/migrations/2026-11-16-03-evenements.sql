-- À exécuter sur une base existante
USE volley;
CREATE TABLE IF NOT EXISTS evenement (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titre VARCHAR(150) NOT NULL,
  description TEXT NULL,
  debut DATETIME NOT NULL,
  fin DATETIME NOT NULL,
  lieu VARCHAR(150) NOT NULL,
  date_limite DATETIME NOT NULL,
  statut ENUM('prevu','annule') NOT NULL DEFAULT 'prevu',
  cree_le DATETIME NOT NULL,
  INDEX idx_evenement_debut (debut)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS inscription_evenement (
  id INT AUTO_INCREMENT PRIMARY KEY,
  membre_id INT NOT NULL,
  evenement_id INT NOT NULL,
  inscrit_le DATETIME NOT NULL,
  UNIQUE KEY uniq_inscription (membre_id, evenement_id),
  FOREIGN KEY (membre_id) REFERENCES membre(id),
  FOREIGN KEY (evenement_id) REFERENCES evenement(id)
) ENGINE=InnoDB;
