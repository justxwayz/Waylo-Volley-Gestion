<?php
$db = TestDb::make();
$a = TestDb::membre($db, 'Alice');
$b = TestDb::membre($db, 'Bob');
$c = TestDb::membre($db, 'Chloe');
TestDb::membre($db, 'Inactif', 'membre', 0);
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($a,$s,'present','x'), ($b,$s,'absent','x')");

$h = new VoirListeParticipantsHandler($db);
$d = $h->handle($s);
T::check(count($d['reponses']) === 2, 'deux réponses listées');
T::check(array_column($d['sans_reponse'], 'nom') === ['Chloe'], 'sans réponse = membres ACTIFS qui n\'ont pas répondu (inactif exclu)');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le,venu) VALUES ($c,$s,NULL,NULL,0)");
T::check(array_column($h->handle($s)['sans_reponse'], 'nom') === ['Chloe'], 'ligne de pointage sans réponse => reste dans "sans réponse"');
T::check($h->handle(999) === null, 'séance inconnue => null');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => $a, 'nom' => 'Alice', 'role' => 'membre'], get: ['id' => $s]), 'seance/participants')->status === 403, 'un simple membre n\'a pas accès (403)');
$admin = TestDb::ctx($db, ['id' => 99, 'nom' => 'Admin', 'role' => 'admin'], get: ['id' => $s]);
T::check(str_contains(Router::dispatch($admin, 'seance/participants')->body, 'Sans réponse (1)'), 'page admin rendue');
