<?php
// Action POST uniquement (le bouton se trouve sur la page détail d'une séance).
return [
    'seance/annuler' => [
        'role' => 'admin',
        'run' => function (Context $c): Response {
            $id = (int)($c->get['id'] ?? 0);
            if ($c->isPost()) {
                $r = (new AnnulerSeanceHandler($c->db))->handle($id, $c->now);
                $c->session->flash($r['ok'] ? 'Séance annulée.' : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
            }
            return Response::redirect('?p=seance&id=' . $id);
        },
    ],
];
