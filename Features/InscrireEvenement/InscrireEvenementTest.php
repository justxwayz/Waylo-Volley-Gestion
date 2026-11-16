<?php
$db = TestDb::make();
$alice = TestDb::membre($db, 'Alice');
$e = TestDb::evenement($db, '2026-04-11 09:00:00', '2026-04-01 23:59:00');
$annule = TestDb::evenement($db, '2026-04-12 09:00:00', '2026-04-01 23:59:00', 'annule');
$h = new InscrireEvenementHandler($db);
$tot = new DateTimeImmutable('2026-03-10 12:00:00');
$tard = new DateTimeImmutable('2026-04-02 08:00:00');
$nb = fn() => (int)$db->query('SELECT COUNT(*) FROM inscription_evenement')->fetchColumn();

T::check($h->handle($alice, $e, 'inscrire', $tot)['ok'] && $nb() === 1, 'inscription enregistrée');
T::check($h->handle($alice, $e, 'inscrire', $tot)['ok'] === false && $nb() === 1, 'double inscription refusée');
T::check($h->handle($alice, $e, 'desinscrire', $tard)['ok'] === false && $nb() === 1, 'désinscription après la date limite refusée');
T::check($h->handle($alice, $e, 'desinscrire', $tot)['ok'] && $nb() === 0, 'désinscription avant la date limite');
T::check($h->handle($alice, $e, 'desinscrire', $tot)['ok'] === false, 'se désinscrire sans être inscrit refusé');
T::check($h->handle($alice, $e, 'inscrire', $tard)['ok'] === false, 'inscription après la date limite refusée');
T::check($h->handle($alice, $annule, 'inscrire', $tot)['ok'] === false, 'événement annulé : inscription refusée');
T::check($h->handle($alice, 999, 'inscrire', $tot)['ok'] === false, 'événement inconnu refusé');
T::check($h->handle($alice, $e, 'nimporte', $tot)['ok'] === false, 'action invalide refusée');

$ctx = TestDb::ctx($db, ['id' => $alice, 'nom' => 'Alice', 'role' => 'membre'], '2026-03-10 12:00:00', ['action' => 'inscrire'], ['id' => $e]);
$ctx->post['csrf'] = $ctx->session->csrf();
Router::dispatch($ctx, 'evenement/inscrire');
T::check($nb() === 1, 'route POST : inscription enregistrée');
