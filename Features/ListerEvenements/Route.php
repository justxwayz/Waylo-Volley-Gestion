<?php
return [
    'evenements' => [
        'role' => 'membre',
        'menu' => ['label' => 'Événements', 'ordre' => 15],
        'run' => function (Context $c): Response {
            $passes = !empty($c->get['passes']);
            $liste = (new ListerEvenementsHandler($c->db))->handle($c->user()['id'], $c->now, $passes);
            return Response::html(Layout::page($c, $passes ? 'Événements passés' : 'Événements à venir', ListerEvenementsView::render($liste, $passes)));
        },
    ],
];
