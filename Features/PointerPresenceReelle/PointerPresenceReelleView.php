<?php
final class PointerPresenceReelleView
{
    public static function form(Context $c, array $f): string
    {
        $s = $f['seance'];
        $id = (int)$s['id'];
        $html = '<p><a href="?p=seance&id=' . $id . '">&larr; ' . Html::e($s['titre']) . ' (' . Html::dateFr($s['debut']) . ')</a></p>'
              . '<form method="post" action="?p=seance/pointage&id=' . $id . '" class="card">' . Html::csrfField($c->session)
              . '<table><tr><th>Venu ?</th><th>Membre</th><th>A répondu</th></tr>';
        foreach ($f['membres'] as $m) {
            // pré-coché : déjà pointé "venu", ou (jamais pointé) a répondu "présent"
            $coche = $m['venu'] !== null ? (int)$m['venu'] === 1 : $m['reponse'] === 'present';
            $html .= '<tr><td><input type="checkbox" name="venus[]" value="' . (int)$m['id'] . '"' . ($coche ? ' checked' : '') . '></td><td>'
                   . Html::e($m['nom']) . '</td><td>' . Html::e(Regles::libelleReponse($m['reponse'])) . '</td></tr>';
        }
        return $html . '</table><p><button>Enregistrer le pointage</button></p></form>';
    }
}
