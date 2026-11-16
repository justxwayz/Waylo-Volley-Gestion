<?php
return [
    'seance/creer' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Nouvelle séance', CreerSeanceView::form($c)));
            }
            $p = $c->post;
            $r = (new CreerSeanceHandler($c->db))->handle($p['titre'] ?? '', $p['debut'] ?? '', $p['fin'] ?? '', $p['lieu'] ?? '', $c->now);
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Nouvelle séance', CreerSeanceView::form($c, $r['erreur'], $p)), 422);
            }
            $c->session->flash('Séance créée.');
            return Response::redirect('?p=seance&id=' . $r['id']);
        },
    ],
];
