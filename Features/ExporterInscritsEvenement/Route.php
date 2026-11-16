<?php
return [
    'export/inscrits' => [
        'role' => 'coach',
        'run' => function (Context $c): Response {
            $r = (new ExporterInscritsEvenementHandler($c->db))->handle((int)($c->get['id'] ?? 0));
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Export impossible', '<p>' . Html::e($r['erreur']) . '</p>'), 404);
            }
            return Response::fichier($r['csv'], $r['nom']);
        },
    ],
];
