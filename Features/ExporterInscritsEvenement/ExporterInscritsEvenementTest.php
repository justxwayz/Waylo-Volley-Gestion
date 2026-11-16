<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$e = TestDb::evenement($db, '2026-04-11 09:00:00', '2026-04-01 23:59:00', 'prevu', 'Tournoi');
$db->exec("INSERT INTO inscription_evenement (membre_id, evenement_id, inscrit_le) VALUES ($alice, $e, '2026-03-02 10:00:00'), ($bob, $e, '2026-03-03 10:00:00')");
$h = new ExporterInscritsEvenementHandler($db);

$r = $h->handle($e);
T::check($r['ok'] && $r['nom'] === 'inscrits-evenement-' . $e . '.csv', 'export d\'un événement');
T::check(str_contains($r['csv'], 'Tournoi;11/04/2026;Prévu;Alice;alice@test.local;02/03/2026') && substr_count($r['csv'], "\r\n") === 3, 'une ligne par inscrit + en-tête');
T::check($h->handle(999)['ok'] === false, 'événement inconnu refusé');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], get: ['id' => $e]), 'export/inscrits')->status === 403, 'un membre n\'a pas accès (403)');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 9, 'nom' => 'Coach', 'role' => 'coach'], get: ['id' => $e]), 'export/inscrits')->filename === 'inscrits-evenement-' . $e . '.csv', 'route coach : téléchargement');
