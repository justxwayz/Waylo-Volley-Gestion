<?php
return [
    'evenement' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            $d = (new VoirDetailEvenementHandler($c->db))->handle((int)($c->get['id'] ?? 0), $c->user()['id'], $c->now);
            if (!$d) {
                return Response::html(Layout::page($c, 'Événement introuvable', '<p>Cet événement n\'existe pas.</p>'), 404);
            }
            return Response::html(Layout::page($c, 'Événement', VoirDetailEvenementView::render($c, $d)));
        },
    ],
];
