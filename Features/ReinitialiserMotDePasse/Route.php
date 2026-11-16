<?php
// Action POST uniquement (bouton dans la liste des membres).
return [
    'membre/reinitialiser' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            if ($c->isPost()) {
                $r = (new ReinitialiserMotDePasseHandler($c->db))->handle((int)($c->get['id'] ?? 0));
                $c->session->flash(
                    $r['ok'] ? 'Mot de passe de ' . $r['nom'] . ' réinitialisé et compte débloqué. Nouveau mot de passe temporaire : ' . $r['mot_de_passe'] : $r['erreur'],
                    $r['ok'] ? 'ok' : 'ko'
                );
            }
            return Response::redirect('?p=membres');
        },
    ],
];
