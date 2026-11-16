<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$e1 = TestDb::evenement($db, '2026-04-11 09:00:00', '2026-04-01 23:59:00', 'prevu', 'Tournoi');
$e2 = TestDb::evenement($db, '2026-03-20 19:00:00', '2026-03-15 23:59:00', 'prevu', 'Soiree');
$e3 = TestDb::evenement($db, '2026-02-01 19:00:00', '2026-01-25 23:59:00', 'prevu', 'Ancien');
$db->exec("INSERT INTO inscription_evenement (membre_id, evenement_id, inscrit_le) VALUES ($alice, $e1, 'x'), ($bob, $e1, 'x')");
$h = new ListerEvenementsHandler($db);

$l = $h->handle($alice, $now);
T::check(array_column($l, 'titre') === ['Soiree', 'Tournoi'], 'à venir : triés par date, passés exclus');
T::check((int)$l[1]['nb_inscrits'] === 2 && (int)$l[1]['moi'] === 1 && (int)$l[0]['moi'] === 0, 'compteur d\'inscrits et mon inscription');
T::check(array_column($h->handle($alice, $now, true), 'titre') === ['Ancien'], 'passés : seulement les événements terminés');
$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre']);
T::check(str_contains(Router::dispatch($ctx, 'evenements')->body, 'Inscrit'), 'page rendue avec le tag "Inscrit"');
