<?php
$db = TestDb::make();
TestDb::membre($db, 'Alice');
TestDb::membre($db, 'Bob', 'membre', 0);
$h = new ConnexionHandler($db);
$t0 = new DateTimeImmutable('2026-03-10 12:00:00');

T::check($h->handle('alice@test.local', 'secret123', $t0)['ok'] === true, 'identifiants valides => connecté');
T::check($h->handle('ALICE@test.local ', 'secret123', $t0)['ok'] === true, 'email insensible à la casse et aux espaces');
T::check($h->handle('alice@test.local', 'mauvais', $t0)['ok'] === false, 'mauvais mot de passe => refusé');
T::check($h->handle('inconnu@test.local', 'secret123', $t0)['ok'] === false, 'email inconnu => refusé');
$r = $h->handle('bob@test.local', 'secret123', $t0);
T::check($r['ok'] === false && str_contains($r['erreur'], 'désactivé'), 'compte désactivé => refusé avec message dédié');

// --- limitation des tentatives (compte "carl", repartir de zéro)
TestDb::membre($db, 'Carl');
$echec = fn(int $n, DateTimeImmutable $t) => $h->handle('carl@test.local', 'faux', $t);
$r1 = $echec(1, $t0); $r2 = $echec(2, $t0);
T::check($r1['avertissement'] === null && $r2['avertissement'] === null, 'échecs 1 et 2 : pas d\'avertissement');
$r3 = $echec(3, $t0);
T::check($r3['avertissement'] === 'Attention : il vous reste 2 tentatives avant blocage.', 'échec 3 : avertissement "2 tentatives restantes"');
$r4 = $echec(4, $t0);
T::check($r4['avertissement'] === 'Attention : il vous reste 1 tentative avant blocage.', 'échec 4 : "1 tentative restante"');
$r5 = $echec(5, $t0);
T::check($r5['bloque'] && str_contains($r5['erreur'], '1 minute'), 'échec 5 : blocage de 1 minute');
$ok = $h->handle('carl@test.local', 'secret123', $t0->modify('+30 seconds'));
T::check($ok['ok'] === false && $ok['bloque'] && str_contains($ok['erreur'], '30 secondes'), 'pendant le blocage : refusé même avec le bon mot de passe');

// palier 2 : 5 minutes
$t1 = $t0->modify('+61 seconds');
T::check($h->handle('carl@test.local', 'secret123', $t1)['ok'] === true, 'après 1 minute : connexion possible (et remise à zéro)');
for ($i = 0; $i < 5; $i++) { $h->handle('carl@test.local', 'faux', $t1); }   // 1er palier
$t2 = $t1->modify('+61 seconds');
for ($i = 0; $i < 4; $i++) { $h->handle('carl@test.local', 'faux', $t2); }
$r = $h->handle('carl@test.local', 'faux', $t2);                              // 5e échec du 2e cycle
T::check($r['bloque'] && str_contains($r['erreur'], '5 minutes'), 'palier 2 : blocage de 5 minutes');

// palier 3 : définitif
$t3 = $t2->modify('+301 seconds');
for ($i = 0; $i < 4; $i++) { $h->handle('carl@test.local', 'faux', $t3); }
$r = $h->handle('carl@test.local', 'faux', $t3);
T::check($r['bloque'] && $r['erreur'] === 'Trop de tentatives, contactez un admin.', 'palier 3 : "Trop de tentatives, contactez un admin."');
$tard = $h->handle('carl@test.local', 'secret123', $t3->modify('+1 day'));
T::check($tard['ok'] === false && $tard['erreur'] === 'Trop de tentatives, contactez un admin.', 'palier 3 : toujours bloqué le lendemain, même avec le bon mot de passe');

// emails inconnus : mêmes compteurs (pas de fuite sur l'existence du compte)
for ($i = 0; $i < 3; $i++) { $r = $h->handle('fantome@test.local', 'x', $t0); }
T::check($r['avertissement'] !== null, 'email inconnu : même avertissement (aucune fuite)');
// un succès remet les échecs à zéro
TestDb::membre($db, 'Dana');
$h->handle('dana@test.local', 'faux', $t0); $h->handle('dana@test.local', 'faux', $t0);
$h->handle('dana@test.local', 'secret123', $t0);
T::check((new LimiteTentatives($db))->etat('dana@test.local')['echecs'] === 0, 'connexion réussie => compteur remis à zéro');

$ctx = TestDb::ctx($db, null, post: ['email' => 'alice@test.local', 'mot_de_passe' => 'secret123', 'csrf' => 'x']);
$ctx->post['csrf'] = $ctx->session->csrf();
$resp = Router::dispatch($ctx, 'connexion');
T::check($resp->redirect === '?p=calendrier' && $ctx->user()['nom'] === 'Alice', 'route POST : connexion + redirection calendrier');
$ctx2 = TestDb::ctx($db, null, post: ['email' => 'carl@test.local', 'mot_de_passe' => 'x', 'csrf' => 'x']);
$ctx2->post['csrf'] = $ctx2->session->csrf();
T::check(Router::dispatch($ctx2, 'connexion')->status === 429, 'route : compte bloqué => HTTP 429');
