<?php
return [
    'seance' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            $d = (new VoirDetailSeanceHandler($c->db))->handle((int)($c->get['id'] ?? 0), $c->user()['id'], $c->now);
            if (!$d) {
                return Response::html(Layout::page($c, 'Séance introuvable', '<p>Cette séance n\'existe pas.</p>'), 404);
            }
            return Response::html(Layout::page($c, 'Séance', VoirDetailSeanceView::render($c, $d)));
        },
    ],
];
