<?php
// SLICE ListerMembres (admin) : liste paginée + recherche par nom ou email.
final class ListerMembresHandler
{
    public const PAR_PAGE = 20;

    public function __construct(private PDO $db) {}

    /** @return array{membres:array,page:int,pages:int,total:int,par_page:int} */
    public function handle(string $recherche = '', int $page = 1, int $parPage = self::PAR_PAGE): array
    {
        $recherche = trim($recherche);
        $parPage = max(1, $parPage);
        $where = $recherche === '' ? '' : ' WHERE nom LIKE :q OR email LIKE :q';
        $params = $recherche === '' ? [] : [':q' => '%' . $recherche . '%'];

        $c = $this->db->prepare('SELECT COUNT(*) FROM membre' . $where);
        $c->execute($params);
        $total = (int)$c->fetchColumn();

        $pages = max(1, (int)ceil($total / $parPage));
        $page = min(max(1, $page), $pages);          // page hors limites => ramenée dans [1, pages]
        $offset = ($page - 1) * $parPage;

        // LIMIT/OFFSET : entiers déjà contrôlés, injectés tels quels (pas de souci avec les requêtes préparées MySQL)
        $s = $this->db->prepare(
            'SELECT id, nom, email, role, actif FROM membre' . $where . ' ORDER BY actif DESC, nom ASC, id ASC LIMIT ' . $parPage . ' OFFSET ' . $offset
        );
        $s->execute($params);
        return ['membres' => $s->fetchAll(), 'page' => $page, 'pages' => $pages, 'total' => $total, 'par_page' => $parPage];
    }
}
