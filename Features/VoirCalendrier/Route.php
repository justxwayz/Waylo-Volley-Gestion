<?php
return [
    'calendrier' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            $mois = VoirCalendrierHandler::mois($c->get['m'] ?? null, $c->now);
            $seances = (new VoirCalendrierHandler($c->db))->handle($c->user()['id'], $mois);
            return Response::html(Layout::page($c, 'Calendrier', VoirCalendrierView::render($seances, $mois)));
        },
    ],
];
