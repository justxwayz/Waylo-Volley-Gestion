<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$mars1 = TestDb::seance($db, '2026-03-12 20:00:00');
$mars2 = TestDb::seance($db, '2026-03-31 20:00:00', 'annulee');
$avril = TestDb::seance($db, '2026-04-01 20:00:00');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($alice,$mars1,'present','2026-03-01 10:00:00'), ($bob,$mars1,'present','2026-03-01 10:00:00'), ($bob,$mars2,'absent','2026-03-01 10:00:00')");

$h = new VoirCalendrierHandler($db);
$mars = VoirCalendrierHandler::mois('2026-03', new DateTimeImmutable('2026-03-10'));
$rows = $h->handle($alice, $mars);
T::check(count($rows) === 2, 'seules les séances du mois sont listées (borne fin de mois incluse, 1er du mois suivant exclu)');
T::check($rows[0]['ma_reponse'] === 'present' && (int)$rows[0]['nb_presents'] === 2, 'ma réponse et nombre de présents');
T::check($rows[1]['ma_reponse'] === null && $rows[1]['statut'] === 'annulee', 'séance annulée visible, sans réponse de ma part');
T::check(VoirCalendrierHandler::mois('nimporte', new DateTimeImmutable('2026-03-10'))->format('Y-m') === '2026-03', 'mois invalide => mois courant');

$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], get: ['m' => '2026-03']);
$html = Router::dispatch($ctx, 'calendrier')->body;
T::check(str_contains($html, 'Entrainement') && str_contains($html, 'Annulée'), 'page calendrier rendue');
T::check(Router::dispatch(TestDb::ctx($db), 'calendrier')->redirect === '?p=connexion', 'non connecté => redirigé vers la connexion');
