<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
TestDb::membre($db, 'Inactif', 'membre', 0);
$s1 = TestDb::seance($db, '2026-03-12 20:00:00', 'prevue', 'Entrainement');
$s2 = TestDb::seance($db, '2026-04-02 20:00:00', 'prevue', '=Formule');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le,venu) VALUES ($alice,$s1,'present','x',1), ($alice,$s2,'absent','x',NULL)");
$h = new ExporterPresencesHandler($db);
$lignes = fn(string $csv) => array_values(array_filter(explode("\r\n", ltrim($csv, "\xEF\xBB\xBF"))));

$r = $h->handle($s1, null);
$l = $lignes($r['csv']);
T::check($r['ok'] && count($l) === 3 && $r['nom'] === 'presences-seance-' . $s1 . '.csv', 'une séance : en-tête + 2 membres actifs (inactif exclu)');
T::check(str_contains($l[1], 'Alice') && str_contains($l[1], 'Présent') && str_contains($l[1], ';Oui'), 'Alice : présente et venue');
T::check(str_contains($l[2], 'Bob') && str_contains($l[2], 'Pas de réponse'), 'Bob sans réponse : quand même listé');

$mars = $lignes($h->handle(null, '2026-03')['csv']);
T::check(count($mars) === 2 && str_contains($mars[1], '12/03/2026'), 'filtre par mois : seulement mars');
$tout = $lignes($h->handle(null, null)['csv']);
T::check(count($tout) === 3 && str_contains($tout[2], "'=Formule"), 'historique complet, injection de formule neutralisée');
T::check($h->handle(null, 'pas-un-mois')['ok'] === false, 'mois invalide refusé');
T::check($h->handle(999, null)['ok'] === false, 'séance inconnue refusée');

$coach = TestDb::ctx($db, ['id' => 9, 'nom' => 'Coach', 'role' => 'coach'], get: ['seance' => $s1]);
$resp = Router::dispatch($coach, 'export/presences');
T::check($resp->filename === 'presences-seance-' . $s1 . '.csv' && str_starts_with($resp->contentType, 'text/csv'), 'route coach : téléchargement CSV');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre']), 'export/presences')->status === 403, 'un membre n\'a pas accès (403)');
