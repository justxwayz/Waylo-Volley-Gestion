<?php
$db = TestDb::make();
$a = TestDb::membre($db, 'Alice');
$b = TestDb::membre($db, 'Bob');
$c = TestDb::membre($db, 'Chloe');
TestDb::membre($db, 'Inactif', 'membre', 0);
$now = new DateTimeImmutable('2026-03-12 23:00:00');
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$futur = TestDb::seance($db, '2026-03-30 20:00:00');
$annulee = TestDb::seance($db, '2026-03-11 20:00:00', 'annulee');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($a,$s,'present','x'), ($b,$s,'present','x')");
$h = new PointerPresenceReelleHandler($db);

T::check($h->handle($s, [$a, $c], $now)['ok'], 'pointage enregistré');
$venu = fn($m) => $db->query("SELECT venu FROM presence WHERE membre_id=$m AND seance_id=$s")->fetchColumn();
T::check((int)$venu($a) === 1, 'Alice (présente, venue) = 1');
T::check((int)$venu($b) === 0, 'Bob (avait répondu présent mais absent le jour J) = 0');
T::check((int)$venu($c) === 1, 'Chloé (sans réponse mais venue) : ligne créée, venu = 1');
T::check($db->query("SELECT reponse FROM presence WHERE membre_id=$c AND seance_id=$s")->fetchColumn() === null, 'sa réponse reste vide (non inventée)');
T::check((int)$db->query("SELECT COUNT(*) FROM presence WHERE seance_id=$s")->fetchColumn() === 3, 'membre inactif non pointé');
T::check($h->handle($s, [$b], $now)['ok'] && (int)$venu($a) === 0 && (int)$venu($b) === 1, 'on peut corriger un pointage');
T::check($h->handle($futur, [$a], $now)['ok'] === false, 'séance pas encore commencée => refusé');
T::check($h->handle($annulee, [$a], $now)['ok'] === false, 'séance annulée => refusé');
