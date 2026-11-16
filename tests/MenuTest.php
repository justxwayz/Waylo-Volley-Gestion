<?php
Router::reinitialiser(true);
$libelles = fn(?array $u) => array_column(Menu::pour($u), 'label');

$membre = ['id' => 1, 'nom' => 'Alice', 'role' => 'membre'];
$coach = ['id' => 2, 'nom' => 'Coach', 'role' => 'coach'];
$admin = ['id' => 3, 'nom' => 'Admin', 'role' => 'admin'];

T::check($libelles(null) === [], 'visiteur : aucun menu');
$m = $libelles($membre);
T::check(in_array('Calendrier', $m) && in_array('Mes présences', $m) && in_array('Alice', $m), 'membre : Calendrier, Mes présences, son profil (nom)');
T::check(!in_array('Membres', $m) && !in_array('Nouvelle séance', $m), 'membre : aucune entrée admin');
$a = $libelles($admin);
T::check(in_array('Membres', $a) && in_array('Nouvelle séance', $a) && in_array('Séances récurrentes', $a) && in_array('Calendrier', $a), 'admin : voit tout (hérite membre + coach)');
T::check(!in_array('Membres', $libelles($coach)), 'coach : pas les entrées admin');
T::check($a[0] === 'Calendrier' && array_search('Événements', $a) < array_search('Mes présences', $a) && end($a) === 'Admin', 'ordre respecté (Calendrier d\'abord, profil en dernier)');

// entrées de test injectées : filtrage par rôle et tri
$faux = [
    'z' => ['file' => 'x', 'role' => 'coach', 'menu' => ['label' => 'Z coach', 'ordre' => 5]],
    'y' => ['file' => 'x', 'role' => 'admin', 'menu' => ['label' => 'Y admin', 'ordre' => 1]],
    'w' => ['file' => 'x', 'role' => 'membre', 'menu' => null],
];
T::check(array_column(Menu::pour($coach, $faux), 'label') === ['Z coach'], 'coach voit Z (coach) mais pas Y (admin), et une route sans menu est ignorée');
T::check(array_column(Menu::pour($admin, $faux), 'label') === ['Y admin', 'Z coach'], 'admin : trié par ordre');

$db = TestDb::make();
$html = Router::dispatch(TestDb::ctx($db, $admin, get: ['m' => '2026-03']), 'calendrier')->body;
T::check(str_contains($html, '?p=membres') && str_contains($html, 'aria-current="page"') && str_contains($html, 'Déconnexion'), 'layout : menu admin + lien actif + déconnexion');
$htmlM = Router::dispatch(TestDb::ctx($db, $membre, get: ['m' => '2026-03']), 'calendrier')->body;
T::check(!str_contains($htmlM, '?p=membres'), 'layout : le membre ne voit pas le lien Membres');
