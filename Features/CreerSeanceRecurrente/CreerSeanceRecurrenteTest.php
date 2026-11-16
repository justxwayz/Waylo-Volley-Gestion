<?php
$db = TestDb::make();
$h = new CreerSeanceRecurrenteHandler($db);
$now = new DateTimeImmutable('2026-03-02 09:00:00'); // lundi
$nb = fn() => (int)$db->query('SELECT COUNT(*) FROM seance')->fetchColumn();

// mardis (2) et jeudis (4) du 2 au 15 mars 2026 : 3, 5, 10, 12 mars
$r = $h->handle('Entrainement', 'Gymnase', [2, 4], '20:00', '22:00', '2026-03-02', '2026-03-15', $now);
T::check($r['ok'] && $r['creees'] === 4 && $nb() === 4, '4 séances générées (2 mardis + 2 jeudis)');
$dates = $db->query('SELECT debut FROM seance ORDER BY debut')->fetchAll(PDO::FETCH_COLUMN);
T::check($dates === ['2026-03-03 20:00:00', '2026-03-05 20:00:00', '2026-03-10 20:00:00', '2026-03-12 20:00:00'], 'bonnes dates et bonnes heures');
T::check($db->query("SELECT fin FROM seance ORDER BY debut LIMIT 1")->fetchColumn() === '2026-03-03 22:00:00', 'heure de fin correcte');

$r = $h->handle('Entrainement', 'Gymnase', [2, 4], '20:00', '22:00', '2026-03-02', '2026-03-15', $now);
T::check($r['ok'] && $r['creees'] === 0 && $r['ignorees'] === 4 && $nb() === 4, 'relancer ne crée aucun doublon (4 ignorées)');

T::check($h->handle('X', 'Y', [], '20:00', '22:00', '2026-03-02', '2026-03-15', $now)['ok'] === false, 'aucun jour choisi refusé');
T::check($h->handle('X', 'Y', [2], '22:00', '20:00', '2026-03-02', '2026-03-15', $now)['ok'] === false, 'heure de fin avant début refusée');
T::check($h->handle('X', 'Y', [2], '20:00', '22:00', '2026-03-15', '2026-03-02', $now)['ok'] === false, 'date de fin avant début refusée');
T::check($h->handle('X', 'Y', [2], '20:00', '22:00', '2026-03-01', '2026-03-15', $now)['ok'] === false, 'période commençant dans le passé refusée');
T::check($h->handle('X', 'Y', [1, 2, 3, 4, 5, 6, 7], '20:00', '22:00', '2026-03-02', '2026-09-30', $now)['ok'] === false && $nb() === 4, 'plus de 120 séances refusé, rien n\'est créé');
T::check($h->handle('X', 'Y', [3], '20:00', '22:00', '2026-03-09', '2026-03-09', $now)['ok'] === false, 'aucun jour correspondant sur la période => refusé');
T::check($h->handle('X', 'Y', [9, 'a'], '20:00', '22:00', '2026-03-02', '2026-03-15', $now)['ok'] === false, 'numéros de jour invalides ignorés => refusé');
T::check($h->handle('X', 'Y', [2], '25:00', '26:00', '2026-03-02', '2026-03-15', $now)['ok'] === false, 'heures invalides refusées');

$admin = ['id' => 1, 'nom' => 'A', 'role' => 'admin'];
$post = ['titre' => 'Match', 'lieu' => 'Salle', 'jours' => ['6'], 'heure_debut' => '15:00', 'heure_fin' => '17:00', 'date_debut' => '2026-04-01', 'date_fin' => '2026-04-30'];
$ctx = TestDb::ctx($db, $admin, '2026-03-02 09:00:00', $post);
$ctx->post['csrf'] = $ctx->session->csrf();
$resp = Router::dispatch($ctx, 'seance/recurrente');
T::check($resp->redirect === '?p=calendrier&m=2026-04' && $nb() === 8, 'route POST : 4 samedis créés, redirection vers le mois concerné');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 2, 'nom' => 'C', 'role' => 'coach']), 'seance/recurrente')->status === 403, 'réservé aux admins (403)');
