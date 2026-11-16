<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$mars1 = TestDb::seance($db, '2026-03-12 20:00:00');
$mars2 = TestDb::seance($db, '2026-03-31 20:00:00', 'annulee');
$avril = TestDb::seance($db, '2026-04-01 20:00:00');
$evt = TestDb::evenement($db, '2026-03-21 09:00:00', '2026-03-15 23:59:00', 'prevu', 'Tournoi de printemps');
TestDb::evenement($db, '2026-04-04 09:00:00', '2026-04-01 23:59:00', 'prevu', 'Autre mois');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($alice,$mars1,'present','2026-03-01 10:00:00'), ($bob,$mars1,'present','2026-03-01 10:00:00'), ($bob,$mars2,'absent','2026-03-01 10:00:00')");
$db->exec("INSERT INTO inscription_evenement (membre_id, evenement_id, inscrit_le) VALUES ($alice, $evt, 'x')");

$h = new VoirCalendrierHandler($db);
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$mars = VoirCalendrierHandler::mois('2026-03', $now);
$d = $h->handle($alice, $mars);
$rows = $d['seances'];
T::check(count($rows) === 2, 'seules les séances du mois sont listées (1er du mois suivant exclu)');
T::check($rows[0]['ma_reponse'] === 'present' && (int)$rows[0]['nb_presents'] === 2, 'ma réponse et nombre de présents');
T::check($rows[1]['ma_reponse'] === null && $rows[1]['statut'] === 'annulee', 'séance annulée visible, sans réponse de ma part');
T::check(count($d['evenements']) === 1 && (int)$d['evenements'][0]['moi'] === 1, 'événements du mois, avec mon inscription');
T::check(VoirCalendrierHandler::mois('nimporte', $now)->format('Y-m') === '2026-03', 'mois invalide => mois courant');

$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], '2026-03-10 12:00:00', [], ['m' => '2026-03']);
$html = Router::dispatch($ctx, 'calendrier')->body;
T::check(str_contains($html, 'cal-grille') && str_contains($html, 'cal-liste'), 'page : grille + liste');
T::check(str_contains($html, 'Entrainement') && str_contains($html, 'Annulée') && str_contains($html, 'Tournoi de printemps'), 'séances, annulation et événement affichés');
T::check(substr_count($html, 'class="cal-case') === 42, 'mars 2026 (1er = dimanche, 31 jours) = 6 semaines x 7 = 42 cases (' . substr_count($html, 'class="cal-case') . ')');
T::check(substr_count($html, 'class="cal-case vide"') === 6 + 5, 'mars 2026 : 6 cases vides avant le 1er (dimanche) et 5 après le 31 (mardi)');
T::check(str_contains($html, 'chip present') && str_contains($html, 'chip annulee') && str_contains($html, 'chip evt'), 'puces colorées : réponse (présent), annulée, événement');
T::check(preg_match('/aujourdhui"><span class="cal-num">10</', $html) === 1, 'le jour courant est mis en évidence');
$fev = Router::dispatch(TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], '2026-03-10 12:00:00', [], ['m' => '2026-02']), 'calendrier')->body;
T::check(substr_count($fev, 'class="cal-case') === 35 && str_contains($fev, 'Aucune séance ni événement'), 'février 2026 (1er = dimanche, 28 jours) = 5 semaines = 35 cases, mois vide géré');
T::check(Router::dispatch(TestDb::ctx($db), 'calendrier')->redirect === '?p=connexion', 'non connecté => redirigé vers la connexion');
