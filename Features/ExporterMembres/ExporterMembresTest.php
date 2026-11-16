<?php
$db = TestDb::make();
TestDb::membre($db, 'Alice');
TestDb::membre($db, 'Bob', 'coach', 0);
$r = (new ExporterMembresHandler($db))->handle(new DateTimeImmutable('2026-03-10'));
T::check($r['nom'] === 'membres-2026-03-10.csv', 'nom de fichier daté');
T::check(str_contains($r['csv'], 'Alice;alice@test.local;Membre;Actif') && str_contains($r['csv'], 'Bob;bob@test.local;Coach;Désactivé'), 'membres, rôles et états exportés');
T::check(!str_contains($r['csv'], 'password') && !str_contains($r['csv'], '$2y$') && !str_contains($r['csv'], 'mot_de_passe'), 'aucun mot de passe ni hash dans l\'export');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'C', 'role' => 'coach']), 'export/membres')->status === 403, 'réservé aux admins (403)');
T::check(Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'admin']), 'export/membres')->filename === 'membres-2026-03-10.csv', 'route admin : téléchargement');
