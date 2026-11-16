<?php
return [
    'membres' => [
        'role' => 'admin',
        'menu' => ['label' => 'Membres', 'ordre' => 40],
        'run' => function (Context $c): Response {
            $q = (string)($c->get['q'] ?? '');
            $d = (new ListerMembresHandler($c->db))->handle($q, (int)($c->get['page'] ?? 1));
            return Response::html(Layout::page($c, 'Membres', ListerMembresView::render($c, $d, $q)));
        },
    ],
];
