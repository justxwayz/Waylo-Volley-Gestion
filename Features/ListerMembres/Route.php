<?php
return [
    'membres' => [
        'role' => 'admin',
        'menu' => ['label' => 'Membres', 'ordre' => 40],
        'run' => function (Context $c): Response {
            $q = (string)($c->get['q'] ?? '');
            $membres = (new ListerMembresHandler($c->db))->handle($q);
            return Response::html(Layout::page($c, 'Membres', ListerMembresView::render($c, $membres, $q)));
        },
    ],
];
