<?php
return [
    'mes-presences' => [
        'role' => 'membre',
        'menu' => ['label' => 'Mes présences', 'ordre' => 20],
        'run' => function (Context $c): Response {
            $d = (new VoirMesPresencesHandler($c->db))->handle($c->user()['id'], $c->now);
            return Response::html(Layout::page($c, 'Mes présences', VoirMesPresencesView::render($d)));
        },
    ],
];
