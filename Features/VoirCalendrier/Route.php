<?php
return [
    'calendrier' => [
        'role' => 'membre',
        'menu' => ['label' => 'Calendrier', 'ordre' => 10],
        'run' => function (Context $c): Response {
            $mois = VoirCalendrierHandler::mois($c->get['m'] ?? null, $c->now);
            $seances = (new VoirCalendrierHandler($c->db))->handle($c->user()['id'], $mois);
            return Response::html(Layout::page($c, 'Calendrier', VoirCalendrierView::render($seances, $mois)));
        },
    ],
];
