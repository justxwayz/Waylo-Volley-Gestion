<?php
return [
    'evenement/creer' => [
        'role' => 'admin',
        'menu' => ['label' => 'Nouvel événement', 'ordre' => 32],
        'run' => function (Context $c): Response {
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Nouvel événement', CreerEvenementView::form($c)));
            }
            $p = $c->post;
            $r = (new CreerEvenementHandler($c->db))->handle(
                $p['titre'] ?? '', $p['description'] ?? '', $p['debut'] ?? '', $p['fin'] ?? '', $p['lieu'] ?? '', $p['date_limite'] ?? '', $c->now
            );
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Nouvel événement', CreerEvenementView::form($c, $r['erreur'], $p)), 422);
            }
            $c->session->flash('Événement créé.');
            return Response::redirect('?p=evenement&id=' . $r['id']);
        },
    ],
];
