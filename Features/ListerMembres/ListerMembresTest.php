<?php
$db = TestDb::make();
TestDb::membre($db, 'Alice');
TestDb::membre($db, 'Bob', 'membre', 0);
TestDb::membre($db, 'Chloe', 'admin');
$h = new ListerMembresHandler($db);

T::check(array_column($h->handle(), 'nom') === ['Alice', 'Chloe', 'Bob'], 'tri : actifs d\'abord, puis par nom');
T::check(array_column($h->handle('lic'), 'nom') === ['Alice'], 'recherche par nom (partielle)');
T::check(array_column($h->handle('bob@test'), 'nom') === ['Bob'], 'recherche par email');
T::check($h->handle('zzz') === [], 'aucun résultat => liste vide');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'membre']), 'membres')->status === 403, 'réservé aux admins (403)');
