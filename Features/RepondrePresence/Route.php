<?php
return [
    'presence/repondre' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            $id = (int)($c->get['id'] ?? 0);
            if ($c->isPost()) {
                $r = (new RepondrePresenceHandler($c->db))->handle($c->user()['id'], $id, $c->post['reponse'] ?? '', $c->now);
                $c->session->flash($r['ok'] ? 'Réponse enregistrée.' : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
            }
            return Response::redirect('?p=seance&id=' . $id);
        },
    ],
];
