-- À exécuter sur une base existante (sinon : docker compose down -v puis up pour tout recréer)
USE volley;
CREATE TABLE IF NOT EXISTS tentative_connexion (
  email VARCHAR(150) NOT NULL PRIMARY KEY,
  echecs INT NOT NULL DEFAULT 0,
  niveau INT NOT NULL DEFAULT 0,
  bloque_jusqu DATETIME NULL,
  maj_le DATETIME NOT NULL
) ENGINE=InnoDB;
