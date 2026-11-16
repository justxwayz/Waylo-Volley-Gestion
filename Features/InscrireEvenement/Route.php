<?php
// Action POST uniquement (boutons de la fiche événement).
return [
    'evenement/inscrire' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            $id = (int)($c->get['id'] ?? 0);
            if ($c->isPost()) {
                $r = (new InscrireEvenementHandler($c->db))->handle($c->user()['id'], $id, $c->post['action'] ?? '', $c->now);
                $c->session->flash($r['ok'] ? $r['message'] : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
            }
            return Response::redirect('?p=evenement&id=' . $id);
        },
    ],
];
