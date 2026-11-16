<?php
return [
    'connexion' => [
        'role' => 'public',
        'run' => function (Context $c): Response {
            if ($c->user()) {
                return Response::redirect('?p=calendrier');
            }
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Connexion', ConnexionView::form($c, null)));
            }
            $r = (new ConnexionHandler($c->db))->handle($c->post['email'] ?? '', $c->post['mot_de_passe'] ?? '', $c->now);
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Connexion', ConnexionView::form($c, $r['erreur'], $c->post['email'] ?? '', $r['avertissement'])), $r['bloque'] ? 429 : 401);
            }
            $c->session->login($r['membre']);
            return Response::redirect('?p=calendrier');
        },
    ],
];
