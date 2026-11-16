<?php
$db = TestDb::make();
TestDb::membre($db, 'Alice');
TestDb::membre($db, 'Bob', 'membre', 0);
TestDb::membre($db, 'Chloe', 'admin');
$h = new ListerMembresHandler($db);
$noms = fn(array $d) => array_column($d['membres'], 'nom');

T::check($noms($h->handle()) === ['Alice', 'Chloe', 'Bob'], 'tri : actifs d\'abord, puis par nom');
T::check($noms($h->handle('lic')) === ['Alice'], 'recherche par nom (partielle)');
T::check($noms($h->handle('bob@test')) === ['Bob'], 'recherche par email');
T::check($h->handle('zzz')['membres'] === [] && $h->handle('zzz')['pages'] === 1, 'aucun résultat => liste vide, 1 page');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'membre']), 'membres')->status === 403, 'réservé aux admins (403)');

// --- pagination : 45 membres supplémentaires (48 au total), 20 par page
for ($i = 1; $i <= 45; $i++) {
    $db->prepare('INSERT INTO membre (nom,email,mot_de_passe,role,actif,cree_le) VALUES (?,?,?,?,?,?)')
       ->execute([sprintf('Membre %02d', $i), "m$i@test.local", 'x', 'membre', 1, '2026-01-01']);
}
$p1 = $h->handle('', 1);
T::check($p1['total'] === 48 && $p1['pages'] === 3 && count($p1['membres']) === 20 && $p1['page'] === 1, '48 membres => 3 pages, 20 sur la première');
$p3 = $h->handle('', 3);
T::check(count($p3['membres']) === 8 && end($p3['membres'])['nom'] === 'Bob', 'dernière page : 8 membres (les désactivés en dernier)');
T::check($noms($h->handle('', 2))[0] !== $noms($p1)[0] && !array_intersect($noms($p1), $noms($h->handle('', 2))), 'aucun doublon entre deux pages');
T::check($h->handle('', 99)['page'] === 3 && $h->handle('', 0)['page'] === 1 && $h->handle('', -5)['page'] === 1, 'page hors limites ramenée dans [1, 3]');
$r = $h->handle('Membre', 2, 10);
T::check($r['total'] === 45 && $r['pages'] === 5 && count($r['membres']) === 10, 'pagination + recherche + taille de page personnalisée');

$ctx = TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'admin'], get: ['page' => 2, 'q' => 'Membre']);
$html = Router::dispatch($ctx, 'membres')->body;
T::check(str_contains($html, 'page 2 sur 3') && str_contains($html, 'page=1&amp;q=Membre') && str_contains($html, 'page=3&amp;q=Membre'), 'page rendue : compteur, liens précédente/suivante avec la recherche conservée');
$un = Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'admin'], get: ['q' => 'Alice']), 'membres')->body;
T::check(!str_contains($un, 'Suivante') && str_contains($un, 'page 1 sur 1'), 'une seule page : pas de liens de navigation');
