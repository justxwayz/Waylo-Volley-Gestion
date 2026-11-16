<?php
// Test "fumée" : toutes les pages se rendent sans erreur pour un admin, et un visiteur est renvoyé vers la connexion.
$db = TestDb::make();
$admin = TestDb::membre($db, 'Admin', 'admin');
$alice = TestDb::membre($db, 'Alice');
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$db->exec("INSERT INTO presence (membre_id,seance_id,reponse,reponse_le) VALUES ($alice,$s,'present','x')");
$u = ['id' => $admin, 'nom' => 'Admin', 'role' => 'admin'];

$routes = Router::routes();
T::check(count($routes) === 23, 'les 23 routes des slices sont découvertes (trouvé : ' . count($routes) . ')');

foreach (array_keys($routes) as $path) {
    $ctx = TestDb::ctx($db, $u, '2026-03-10 12:00:00', [], ['id' => $s, 'm' => '2026-03']);
    $resp = Router::dispatch($ctx, $path);
    T::check($resp->status < 500, "admin GET $path => " . ($resp->redirect ? 'redirection' : 'HTTP ' . $resp->status));
}
foreach (array_keys($routes) as $path) {
    if ($routes[$path]['role'] === 'public') continue;
    T::check(Router::dispatch(TestDb::ctx($db), $path)->redirect === '?p=connexion', "visiteur sur $path => connexion");
}
