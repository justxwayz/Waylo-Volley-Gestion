<?php
return [
    'seance/recurrente' => [
        'role' => 'admin',
        'menu' => ['label' => 'Séances récurrentes', 'ordre' => 31],
        'run' => function (Context $c): Response {
            if (!$c->isPost()) {
                return Response::html(Layout::page($c, 'Séances récurrentes', CreerSeanceRecurrenteView::form($c)));
            }
            $p = $c->post;
            $r = (new CreerSeanceRecurrenteHandler($c->db))->handle(
                $p['titre'] ?? '', $p['lieu'] ?? '', (array)($p['jours'] ?? []),
                $p['heure_debut'] ?? '', $p['heure_fin'] ?? '', $p['date_debut'] ?? '', $p['date_fin'] ?? '', $c->now
            );
            if (!$r['ok']) {
                return Response::html(Layout::page($c, 'Séances récurrentes', CreerSeanceRecurrenteView::form($c, $r['erreur'], $p)), 422);
            }
            $msg = $r['creees'] . ' séance(s) créée(s)' . ($r['ignorees'] ? ' (' . $r['ignorees'] . ' déjà existante(s) ignorée(s))' : '') . '.';
            $c->session->flash($msg);
            return Response::redirect('?p=calendrier&m=' . substr($r['premiere'], 0, 7));
        },
    ],
];
