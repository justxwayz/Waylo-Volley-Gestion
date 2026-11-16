<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($alice,$s,'present','x'), ($bob,$s,'absent','x')");
$h = new VoirDetailSeanceHandler($db);

$d = $h->handle($s, $alice, new DateTimeImmutable('2026-03-10 12:00:00'));
T::check($d['ma_reponse'] === 'present' && $d['compteurs'] === ['present' => 1, 'absent' => 1, 'peut_etre' => 0], 'ma réponse + compteurs');
T::check($d['ouvert'] === true, 'réponses ouvertes à 2 jours de la séance');
$tard = $h->handle($s, $alice, new DateTimeImmutable('2026-03-12 18:30:00'));
T::check($tard['ouvert'] === false, 'réponses closes à moins de 2 h du début');
T::check($h->handle(999, $alice, new DateTimeImmutable('2026-03-10')) === null, 'séance inconnue => null');

$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], get: ['id' => $s]);
$html = Router::dispatch($ctx, 'seance')->body;
T::check(str_contains($html, 'Je viens') && !str_contains($html, 'Administration'), 'membre : boutons de réponse, pas de bloc admin');
$ctxA = TestDb::ctx($db, ['id' => 99, 'nom' => 'Admin', 'role' => 'admin'], get: ['id' => $s]);
T::check(str_contains(Router::dispatch($ctxA, 'seance')->body, 'Administration'), 'admin : bloc administration visible');
