<?php
return [
    'seance/participants' => [
        'role' => 'coach',
        'run' => function (Context $c): Response {
            $d = (new VoirListeParticipantsHandler($c->db))->handle((int)($c->get['id'] ?? 0));
            if (!$d) {
                return Response::html(Layout::page($c, 'Séance introuvable', '<p>Cette séance n\'existe pas.</p>'), 404);
            }
            return Response::html(Layout::page($c, 'Participants', VoirListeParticipantsView::render($d)));
        },
    ],
];
