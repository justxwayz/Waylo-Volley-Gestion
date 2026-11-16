-- À exécuter sur une base existante
USE volley;
ALTER TABLE membre MODIFY role ENUM('membre','coach','admin') NOT NULL DEFAULT 'membre';
