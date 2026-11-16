-- Données de démonstration (à supprimer en vrai usage)
-- Admin : admin@waylo.local / admin1234   -   Membres : prenom@waylo.local / membre1234
USE volley;
INSERT INTO membre (nom, email, mot_de_passe, role, actif, cree_le) VALUES
 ('Admin Club', 'admin@waylo.local', '$2y$10$IMa6XHF8Pjn0r7wkSHbl9OKO4/bnnoEcAaneR5V1wXwFw/7ytJ3Gm', 'admin', 1, NOW()),
 ('Alice Martin', 'alice@waylo.local', '$2y$10$b8xJcqv.vB6/GZ2qg4KNse3W2fCp9dyE1ANn0MRMtO/psE1UvXc06', 'membre', 1, NOW()),
 ('Bastien Leroy', 'bastien@waylo.local', '$2y$10$b8xJcqv.vB6/GZ2qg4KNse3W2fCp9dyE1ANn0MRMtO/psE1UvXc06', 'membre', 1, NOW()),
 ('Camille Dubois', 'camille@waylo.local', '$2y$10$b8xJcqv.vB6/GZ2qg4KNse3W2fCp9dyE1ANn0MRMtO/psE1UvXc06', 'membre', 1, NOW());

INSERT INTO seance (titre, debut, fin, lieu, statut, cree_le) VALUES
 ('Entrainement loisir', DATE_ADD(DATE(NOW()), INTERVAL '2 20:00' DAY_MINUTE), DATE_ADD(DATE(NOW()), INTERVAL '2 22:00' DAY_MINUTE), 'Gymnase Jean Moulin', 'prevue', NOW()),
 ('Entrainement technique', DATE_ADD(DATE(NOW()), INTERVAL '4 19:30' DAY_MINUTE), DATE_ADD(DATE(NOW()), INTERVAL '4 21:30' DAY_MINUTE), 'Gymnase Jean Moulin', 'prevue', NOW()),
 ('Match amical', DATE_ADD(DATE(NOW()), INTERVAL '9 15:00' DAY_MINUTE), DATE_ADD(DATE(NOW()), INTERVAL '9 17:00' DAY_MINUTE), 'Salle des sports', 'prevue', NOW());
