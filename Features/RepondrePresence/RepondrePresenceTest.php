<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$annulee = TestDb::seance($db, '2026-03-13 20:00:00', 'annulee');
$h = new RepondrePresenceHandler($db);
$tot = new DateTimeImmutable('2026-03-10 12:00:00');
$trop_tard = new DateTimeImmutable('2026-03-12 18:30:00');
$compte = fn() => (int)$db->query('SELECT COUNT(*) FROM presence')->fetchColumn();

T::check($h->handle($alice, $s, 'present', $tot)['ok'] && $compte() === 1, 'première réponse enregistrée');
T::check($h->handle($alice, $s, 'absent', $tot)['ok'] && $compte() === 1, 'changer de réponse met à jour (pas de doublon)');
T::check($db->query('SELECT reponse FROM presence')->fetchColumn() === 'absent', 'la dernière réponse est conservée');
T::check($h->handle($alice, $s, 'present', $trop_tard)['ok'] === false, 'après la date limite => refusé');
T::check($db->query('SELECT reponse FROM presence')->fetchColumn() === 'absent', 'réponse inchangée après un refus');
T::check($h->handle($alice, $annulee, 'present', $tot)['ok'] === false, 'séance annulée => refusé');
T::check($h->handle($alice, $s, 'bof', $tot)['ok'] === false, 'valeur de réponse invalide => refusé');
T::check($h->handle($alice, 999, 'present', $tot)['ok'] === false, 'séance inconnue => refusé');

$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], '2026-03-10 12:00:00', ['reponse' => 'peut_etre'], ['id' => $s]);
$ctx->post['csrf'] = $ctx->session->csrf();
Router::dispatch($ctx, 'presence/repondre');
T::check($db->query('SELECT reponse FROM presence')->fetchColumn() === 'peut_etre', 'route POST : réponse enregistrée');
