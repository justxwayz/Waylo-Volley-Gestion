<?php
// Le menu est construit ICI, et seulement ici, à partir des routes.
// Chaque slice déclare son entrée dans son Route.php ('menu' => ['label' => ..., 'ordre' => ...]) ;
// le rôle requis de la route décide qui la voit (hiérarchie membre < coach < admin).
final class Menu
{
    /**
     * @param array|null $index index des routes (par défaut : Router::index())
     * @return array<int,array{path:string,label:string}>
     */
    public static function pour(?array $utilisateur, ?array $index = null): array
    {
        if (!$utilisateur) {
            return [];
        }
        $index ??= Router::index();
        $entrees = [];
        foreach ($index as $path => $def) {
            if (!$def['menu'] || !Roles::atteint($utilisateur['role'], $def['role'])) {
                continue;
            }
            $label = str_replace(':nom', $utilisateur['nom'], $def['menu']['label']);
            $entrees[] = ['path' => $path, 'label' => $label, 'ordre' => $def['menu']['ordre'] ?? 50];
        }
        usort($entrees, fn($a, $b) => [$a['ordre'], $a['label']] <=> [$b['ordre'], $b['label']]);
        return array_map(fn($e) => ['path' => $e['path'], 'label' => $e['label']], $entrees);
    }

    public static function html(Context $c): string
    {
        $u = $c->user();
        if (!$u) {
            return '';
        }
        $courant = (string)($c->get['p'] ?? 'calendrier');
        $html = '';
        foreach (self::pour($u) as $e) {
            $html .= '<a href="?p=' . Html::e($e['path']) . '"' . ($e['path'] === $courant ? ' aria-current="page" style="font-weight:700"' : '') . '>'
                   . Html::e($e['label']) . '</a>';
        }
        // La déconnexion est une action POST (pas un lien) : seul élément écrit en dur
        return $html . '<form method="post" action="?p=deconnexion" class="inline">' . Html::csrfField($c->session) . '<button>Déconnexion</button></form>';
    }
}
