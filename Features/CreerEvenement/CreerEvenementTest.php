<?php
$db = TestDb::make();
$h = new CreerEvenementHandler($db);
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$ok = fn(array $o = []) => $h->handle($o['t'] ?? 'Tournoi', 'Un beau tournoi', $o['d'] ?? '2026-04-11T09:00', $o['f'] ?? '2026-04-11T18:00', $o['l'] ?? 'Salle des sports', $o['li'] ?? '2026-04-01T23:59', $now);

$r = $ok();
T::check($r['ok'] && $r['id'] === 1, 'création valide');
T::check($db->query('SELECT statut FROM evenement')->fetchColumn() === 'prevu', 'statut initial = prevu');
T::check($ok(['t' => ''])['ok'] === false, 'titre vide refusé');
T::check($ok(['f' => '2026-04-11T08:00'])['ok'] === false, 'fin avant début refusée');
T::check($ok(['li' => '2026-04-12T10:00'])['ok'] === false, 'date limite après le début refusée');
T::check($ok(['li' => '2026-04-11T09:00'])['ok'] === true, 'date limite = début acceptée');
T::check($ok(['d' => 'nimporte'])['ok'] === false, 'date invalide refusée');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'C', 'role' => 'coach']), 'evenement/creer')->status === 403, 'réservé aux admins (403)');
