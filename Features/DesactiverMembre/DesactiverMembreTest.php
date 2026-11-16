<?php
$db = TestDb::make();
$admin1 = TestDb::membre($db, 'Admin1', 'admin');
$admin2 = TestDb::membre($db, 'Admin2', 'admin');
$alice = TestDb::membre($db, 'Alice');
$h = new DesactiverMembreHandler($db);
$actif = fn($id) => (int)$db->query("SELECT actif FROM membre WHERE id = $id")->fetchColumn();

T::check($h->desactiver($alice, $admin1)['ok'] && $actif($alice) === 0, 'désactivation d\'un membre');
T::check($h->desactiver($alice, $admin1)['ok'] === false, 'déjà désactivé => refus');
T::check($h->desactiver($admin1, $admin1)['ok'] === false, 'on ne se désactive pas soi-même');
T::check($h->desactiver($admin2, $admin1)['ok'] && $actif($admin2) === 0, 'un admin peut en désactiver un autre s\'il en reste un');
T::check($h->desactiver($admin1, $alice)['ok'] === false && $actif($admin1) === 1, 'jamais le dernier admin actif');
T::check($h->reactiver($alice)['ok'] && $actif($alice) === 1, 'réactivation');
T::check($h->reactiver($alice)['ok'] === false, 'déjà actif => refus');
T::check((int)$db->query('SELECT COUNT(*) FROM membre')->fetchColumn() === 3, 'aucune suppression en base');

$ctx = TestDb::ctx($db, ['id' => $admin1, 'nom' => 'Admin1', 'role' => 'admin'], post: ['x' => 1], get: ['id' => $alice]);
$ctx->post['csrf'] = $ctx->session->csrf();
Router::dispatch($ctx, 'membre/desactiver');
T::check($actif($alice) === 0, 'route POST : membre désactivé');
$get = TestDb::ctx($db, ['id' => $admin1, 'nom' => 'Admin1', 'role' => 'admin'], get: ['id' => $alice]);
Router::dispatch($get, 'membre/reactiver');
T::check($actif($alice) === 0, 'GET ne modifie rien (POST obligatoire)');
