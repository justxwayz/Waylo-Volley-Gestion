<?php
$db = TestDb::make();
TestDb::membre($db, 'Alice');
TestDb::membre($db, 'Bob', 'membre', 0);
$h = new ConnexionHandler($db);

T::check($h->handle('alice@test.local', 'secret123')['ok'] === true, 'identifiants valides => connecté');
T::check($h->handle('ALICE@test.local ', 'secret123')['ok'] === true, 'email insensible à la casse et aux espaces');
T::check($h->handle('alice@test.local', 'mauvais')['ok'] === false, 'mauvais mot de passe => refusé');
T::check($h->handle('inconnu@test.local', 'secret123')['ok'] === false, 'email inconnu => refusé');
$r = $h->handle('bob@test.local', 'secret123');
T::check($r['ok'] === false && str_contains($r['erreur'], 'désactivé'), 'compte désactivé => refusé avec message dédié');

$ctx = TestDb::ctx($db, null, post: ['email' => 'alice@test.local', 'mot_de_passe' => 'secret123', 'csrf' => 'x']);
$ctx->post['csrf'] = $ctx->session->csrf();
$resp = Router::dispatch($ctx, 'connexion');
T::check($resp->redirect === '?p=calendrier' && $ctx->user()['nom'] === 'Alice', 'route POST : connexion + redirection calendrier');
