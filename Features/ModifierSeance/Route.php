<?php
return [
    'seance/modifier' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            $h = new ModifierSeanceHandler($c->db);
            $id = (int)($c->get['id'] ?? 0);
            $s = $h->find($id);
            if (!$s) {
                return Response::html(Layout::page($c, 'Séance introuvable', '<p>Cette séance n\'existe pas.</p>'), 404);
            }
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Modifier la séance', ModifierSeanceView::form($c, $s)));
            }
            $p = $c->post;
            $r = $h->handle($id, $p['titre'] ?? '', $p['debut'] ?? '', $p['fin'] ?? '', $p['lieu'] ?? '', $c->now);
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Modifier la séance', ModifierSeanceView::form($c, $s, $r['erreur'])), 422);
            }
            $c->session->flash('Séance modifiée.');
            return Response::redirect('?p=seance&id=' . $id);
        },
    ],
];
