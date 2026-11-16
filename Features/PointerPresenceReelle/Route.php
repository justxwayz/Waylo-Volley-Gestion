<?php
return [
    'seance/pointage' => [
        'role' => 'coach',
        'run' => function (Context $c): Response {
            $h = new PointerPresenceReelleHandler($c->db);
            $id = (int)($c->get['id'] ?? 0);
            $f = $h->formulaire($id);
            if (!$f) {
                return Response::html(Layout::page($c, 'Séance introuvable', '<p>Cette séance n\'existe pas.</p>'), 404);
            }
            if ($c->isPost()) {
                $r = $h->handle($id, (array)($c->post['venus'] ?? []), $c->now);
                $c->session->flash($r['ok'] ? 'Pointage enregistré.' : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
                return Response::redirect('?p=seance/pointage&id=' . $id);
            }
            return Response::html(Layout::page($c, 'Pointage', PointerPresenceReelleView::form($c, $f)));
        },
    ],
];
