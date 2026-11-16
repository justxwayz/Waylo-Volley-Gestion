<?php
return [
    'export/presences' => [
        'role' => 'coach',
        'menu' => ['label' => 'Export présences', 'ordre' => 60],
        'run' => function (Context $c): Response {
            $seance = isset($c->get['seance']) && $c->get['seance'] !== '' ? (int)$c->get['seance'] : null;
            $r = (new ExporterPresencesHandler($c->db))->handle($seance, $c->get['mois'] ?? null);
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Export impossible', '<p>' . Html::e($r['erreur']) . '</p>'), 422);
            }
            return Response::fichier($r['csv'], $r['nom']);
        },
    ],
];
