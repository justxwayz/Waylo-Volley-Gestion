<?php
return [
    'evenement/annuler' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            $id = (int)($c->get['id'] ?? 0);
            if ($c->isPost()) {
                $r = (new AnnulerEvenementHandler($c->db))->handle($id, $c->now);
                $c->session->flash($r['ok'] ? 'Événement annulé.' : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
            }
            return Response::redirect('?p=evenement&id=' . $id);
        },
    ],
];
