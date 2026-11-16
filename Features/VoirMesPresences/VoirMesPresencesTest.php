<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$bob = TestDb::membre($db, 'Bob');
$now = new DateTimeImmutable('2026-03-20 12:00:00');
$s1 = TestDb::seance($db, '2026-03-02 20:00:00');
$s2 = TestDb::seance($db, '2026-03-09 20:00:00');
$s3 = TestDb::seance($db, '2026-03-16 20:00:00', 'annulee');
$s4 = TestDb::seance($db, '2026-03-30 20:00:00');
$ins = $db->prepare('INSERT INTO presence (membre_id,seance_id,reponse,reponse_le,venu) VALUES (?,?,?,?,?)');
$ins->execute([$alice, $s1, 'present', 'x', 1]);
$ins->execute([$alice, $s2, 'present', 'x', 0]);
$ins->execute([$alice, $s3, 'present', 'x', null]);
$ins->execute([$alice, $s4, 'present', 'x', null]);
$ins->execute([$bob, $s1, 'present', 'x', 1]);

$h = new VoirMesPresencesHandler($db);
$d = $h->handle($alice, $now);
T::check(count($d['lignes']) === 4, 'historique : mes 4 lignes uniquement (pas celles de Bob)');
T::check($d['pointees'] === 2 && $d['venues'] === 1 && $d['taux'] === 50, 'taux = 1 venue sur 2 séances pointées = 50 %');
T::check($h->handle(999, $now)['taux'] === null, 'aucune séance pointée => taux inconnu (pas de division par zéro)');
$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre']);
T::check(str_contains(Router::dispatch($ctx, 'mes-presences')->body, 'Taux de présence'), 'page rendue');
