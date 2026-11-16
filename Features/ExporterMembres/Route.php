<?php
return [
    'export/membres' => [
        'role' => 'admin',
        'menu' => ['label' => 'Export membres', 'ordre' => 61],
        'run' => function (Context $c): Response {
            $r = (new ExporterMembresHandler($c->db))->handle($c->now);
            return Response::fichier($r['csv'], $r['nom']);
        },
    ],
];
