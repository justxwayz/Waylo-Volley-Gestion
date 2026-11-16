<?php
$db = TestDb::make();
$id = TestDb::membre($db, 'Alice');
$ctx = TestDb::ctx($db, ['id' => $id, 'nom' => 'Alice', 'role' => 'membre'], post: ['x' => 1]);
$ctx->post['csrf'] = $ctx->session->csrf();
$resp = Router::dispatch($ctx, 'deconnexion');
T::check($ctx->user() === null && $resp->redirect === '?p=connexion', 'POST deconnexion => session vidée');

$anon = TestDb::ctx($db);
T::check(Router::dispatch($anon, 'deconnexion')->redirect === '?p=connexion', 'non connecté => renvoyé vers la connexion');
