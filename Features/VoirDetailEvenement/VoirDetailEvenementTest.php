<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$e = TestDb::evenement($db, '2026-04-11 09:00:00', '2026-04-01 23:59:00');
$db->exec("INSERT INTO inscription_evenement (membre_id, evenement_id, inscrit_le) VALUES ($alice, $e, 'x'), ($bob, $e, 'x')");
$h = new VoirDetailEvenementHandler($db);

$d = $h->handle($e, $alice, new DateTimeImmutable('2026-03-10 12:00:00'));
T::check($d['inscrit'] === true && count($d['inscrits']) === 2 && $d['ouvert'] === true, 'inscrit, 2 inscrits, inscriptions ouvertes');
T::check($h->handle($e, 999, new DateTimeImmutable('2026-03-10'))['inscrit'] === false, 'un autre membre n\'est pas inscrit');
T::check($h->handle($e, $alice, new DateTimeImmutable('2026-04-02 00:00:00'))['ouvert'] === false, 'après la date limite : inscriptions closes');
T::check($h->handle(999, $alice, new DateTimeImmutable('2026-03-10')) === null, 'événement inconnu => null');

$get = ['id' => $e];
$membre = Router::dispatch(TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], '2026-03-10 12:00:00', [], $get), 'evenement')->body;
T::check(str_contains($membre, 'Me désinscrire') && !str_contains($membre, 'Inscrits (') && !str_contains($membre, 'Annuler l\'événement'), 'membre : bouton de désinscription, pas la liste ni l\'annulation');
$coach = Router::dispatch(TestDb::ctx($db, ['id' => 50, 'nom' => 'Coach', 'role' => 'coach'], '2026-03-10 12:00:00', [], $get), 'evenement')->body;
T::check(str_contains($coach, 'Inscrits (2)') && !str_contains($coach, 'Annuler l\'événement'), 'coach : voit la liste des inscrits');
$admin = Router::dispatch(TestDb::ctx($db, ['id' => 51, 'nom' => 'Admin', 'role' => 'admin'], '2026-03-10 12:00:00', [], $get), 'evenement')->body;
T::check(str_contains($admin, 'Annuler l\'événement'), 'admin : peut annuler');
