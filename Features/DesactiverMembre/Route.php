<?php
// Deux actions inverses dans la même slice : désactiver / réactiver (POST uniquement).
$action = function (string $nom) {
    return function (Context $c) use ($nom): Response {
        if ($c->isPost()) {
            $h = new DesactiverMembreHandler($c->db);
            $id = (int)($c->get['id'] ?? 0);
            $r = $nom === 'desactiver' ? $h->desactiver($id, $c->user()['id']) : $h->reactiver($id);
            $c->session->flash($r['ok'] ? ($nom === 'desactiver' ? 'Membre désactivé.' : 'Membre réactivé.') : $r['erreur'], $r['ok'] ? 'ok' : 'ko');
        }
        return Response::redirect('?p=membres');
    };
};

return [
    'membre/desactiver' => ['role' => 'admin', 'run' => $action('desactiver')],
    'membre/reactiver' => ['role' => 'admin', 'run' => $action('reactiver')],
];
