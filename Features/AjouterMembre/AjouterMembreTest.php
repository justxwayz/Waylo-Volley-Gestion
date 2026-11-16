<?php
$db = TestDb::make();
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$h = new AjouterMembreHandler($db);

$r = $h->handle('Dana', 'Dana@Test.local ', 'membre', '', $now);
T::check($r['ok'] && strlen($r['mot_de_passe']) === 10, 'création avec mot de passe généré (10 car.)');
$ligne = $db->query('SELECT * FROM membre')->fetch();
T::check($ligne['email'] === 'dana@test.local' && $ligne['actif'] == 1, 'email normalisé, compte actif');
T::check(password_verify($r['mot_de_passe'], $ligne['mot_de_passe']) && $ligne['mot_de_passe'] !== $r['mot_de_passe'], 'mot de passe stocké haché');
T::check($h->handle('Dana2', 'dana@test.local', 'membre', '', $now)['ok'] === false, 'email déjà utilisé refusé');
T::check($h->handle('X', 'pas-un-email', 'membre', '', $now)['ok'] === false, 'email invalide refusé');
T::check($h->handle('X', 'x@test.local', 'superadmin', '', $now)['ok'] === false, 'rôle inconnu refusé');
T::check($h->handle('X', 'x@test.local', 'membre', 'court', $now)['ok'] === false, 'mot de passe fourni trop court refusé');
T::check($h->handle('', 'y@test.local', 'membre', '', $now)['ok'] === false, 'nom vide refusé');
T::check($h->handle('Eve', 'eve@test.local', 'admin', 'motdepasse1', $now)['ok'] && $h->handle('Eve', 'eve@test.local', 'admin', '', $now)['ok'] === false, 'admin créable avec mot de passe choisi');
