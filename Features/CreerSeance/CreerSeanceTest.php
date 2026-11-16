<?php
$db = TestDb::make();
$h = new CreerSeanceHandler($db);
$now = new DateTimeImmutable('2026-03-10 12:00:00');

$r = $h->handle('Entrainement', '2026-03-12T20:00', '2026-03-12T22:00', 'Gymnase', $now);
T::check($r['ok'] && $r['id'] === 1, 'création valide (format datetime-local)');
T::check($db->query('SELECT statut FROM seance')->fetchColumn() === 'prevue', 'statut initial = prevue');
T::check($h->handle('', '2026-03-12T20:00', '2026-03-12T22:00', 'Gymnase', $now)['ok'] === false, 'titre vide refusé');
T::check($h->handle('X', '2026-03-12T22:00', '2026-03-12T20:00', 'Gymnase', $now)['ok'] === false, 'fin avant début refusée');
T::check($h->handle('X', 'n/importe quoi', '2026-03-12T20:00', 'Gymnase', $now)['ok'] === false, 'date invalide refusée');

$membre = TestDb::ctx($db, ['id' => 9, 'nom' => 'M', 'role' => 'membre']);
T::check(Router::dispatch($membre, 'seance/creer')->status === 403, 'un simple membre ne peut pas accéder (403)');
