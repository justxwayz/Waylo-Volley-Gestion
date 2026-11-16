<?php
$db = TestDb::make();
$id = TestDb::membre($db, 'Alice');
$h = new ReinitialiserMotDePasseHandler($db);
$t = new DateTimeImmutable('2026-03-10 12:00:00');
$conn = new ConnexionHandler($db);

// bloquer définitivement Alice (3 paliers)
for ($palier = 0; $palier < 3; $palier++) {
    for ($i = 0; $i < 5; $i++) { $conn->handle('alice@test.local', 'faux', $t); }
    $t = $t->modify('+6 minutes');
}
T::check($conn->handle('alice@test.local', 'secret123', $t)['erreur'] === 'Trop de tentatives, contactez un admin.', 'préalable : compte bloqué définitivement');

$r = $h->handle($id);
T::check($r['ok'] && strlen($r['mot_de_passe']) === 10, 'nouveau mot de passe temporaire généré');
T::check($conn->handle('alice@test.local', 'secret123', $t)['ok'] === false, 'l\'ancien mot de passe ne fonctionne plus');
T::check($conn->handle('alice@test.local', $r['mot_de_passe'], $t)['ok'] === true, 'le nouveau mot de passe fonctionne : compte débloqué');
T::check($h->handle(999)['ok'] === false, 'membre inconnu => refus');

$admin = TestDb::membre($db, 'Admin', 'admin');
$ctx = TestDb::ctx($db, ['id' => $admin, 'nom' => 'Admin', 'role' => 'admin'], post: ['x' => 1], get: ['id' => $id]);
$ctx->post['csrf'] = $ctx->session->csrf();
Router::dispatch($ctx, 'membre/reinitialiser');
T::check(str_contains(json_encode($ctx->session->takeFlash()), 'Nouveau mot de passe temporaire'), 'route POST : mot de passe affiché une fois à l\'admin');
