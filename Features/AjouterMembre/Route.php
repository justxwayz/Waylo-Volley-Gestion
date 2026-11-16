<?php
return [
    'membre/ajouter' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Ajouter un membre', AjouterMembreView::form($c)));
            }
            $p = $c->post;
            $r = (new AjouterMembreHandler($c->db))->handle($p['nom'] ?? '', $p['email'] ?? '', $p['role'] ?? 'membre', $p['mot_de_passe'] ?? '', $c->now);
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Ajouter un membre', AjouterMembreView::form($c, $r['erreur'], $p)), 422);
            }
            $c->session->flash('Compte créé. Mot de passe temporaire à transmettre : ' . $r['mot_de_passe']);
            return Response::redirect('?p=membres');
        },
    ],
];
