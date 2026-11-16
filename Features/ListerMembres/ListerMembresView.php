<?php
final class ListerMembresView
{
    public static function render(Context $c, array $membres, string $q): string
    {
        $qe = Html::e($q);
        $html = '<form method="get" class="card"><input type="hidden" name="p" value="membres">'
              . '<input name="q" value="' . $qe . '" placeholder="Nom ou email"> <button>Rechercher</button> '
              . '<a class="btn" href="?p=membre/ajouter">Ajouter un membre</a></form>';
        if (!$membres) {
            return $html . '<p class="muted">Aucun membre trouvé.</p>';
        }
        $html .= '<table><tr><th>Nom</th><th>Email</th><th>Rôle</th><th>État</th><th></th></tr>';
        foreach ($membres as $m) {
            $actif = (int)$m['actif'] === 1;
            $action = $actif ? 'desactiver' : 'reactiver';
            $label = $actif ? 'Désactiver' : 'Réactiver';
            $html .= '<tr><td>' . Html::e($m['nom']) . '</td><td>' . Html::e($m['email']) . '</td><td>' . Html::e($m['role']) . '</td>'
                   . '<td>' . ($actif ? 'Actif' : '<span class="tag annulee">Désactivé</span>') . '</td><td>'
                   . '<form method="post" action="?p=membre/' . $action . '&id=' . (int)$m['id'] . '" class="inline">' . Html::csrfField($c->session)
                   . '<button class="' . ($actif ? 'ko' : 'sec') . '">' . $label . '</button></form> '
                   . '<form method="post" action="?p=membre/reinitialiser&id=' . (int)$m['id'] . '" class="inline" onsubmit="return confirm(\'Réinitialiser le mot de passe ?\')">' . Html::csrfField($c->session)
                   . '<button class="sec">Réinitialiser le mot de passe</button></form></td></tr>';
        }
        return $html . '</table>';
    }
}
