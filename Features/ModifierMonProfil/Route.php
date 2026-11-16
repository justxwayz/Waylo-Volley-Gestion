<?php
return [
    'profil' => [
        'role' => 'membre',
        'menu' => ['label' => ':nom', 'ordre' => 90],
        'run' => function (Context $c): Response {
            $h = new ModifierMonProfilHandler($c->db);
            $id = $c->user()['id'];
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Mon profil', ModifierMonProfilView::form($c, $h->find($id))));
            }
            $r = $h->handle($id, $c->post['nom'] ?? '', $c->post['ancien'] ?? '', $c->post['nouveau'] ?? '');
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Mon profil', ModifierMonProfilView::form($c, $h->find($id), $r['erreur'])), 422);
            }
            $c->session->flash('Profil mis à jour.');
            return Response::redirect('?p=profil');
        },
    ],
];
