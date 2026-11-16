<?php
// SLICE Deconnexion : POST uniquement (protégé par le jeton CSRF du routeur).
return [
    'deconnexion' => [
        'role' => 'membre',
        'run' => function (Context $c): Response {
            if ($c->isPost()) {
                $c->session->logout();
            }
            return Response::redirect('?p=connexion');
        },
    ],
];
