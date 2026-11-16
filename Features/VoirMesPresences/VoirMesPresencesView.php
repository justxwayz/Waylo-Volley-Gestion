<?php
final class VoirMesPresencesView
{
    public static function render(array $d): string
    {
        $taux = $d['taux'] === null ? 'pas encore de séance pointée' : $d['taux'] . ' % (' . $d['venues'] . ' sur ' . $d['pointees'] . ')';
        $html = '<div class="card"><h2>Taux de présence</h2><p>' . Html::e($taux) . '</p></div>';
        if (!$d['lignes']) {
            return $html . '<p class="muted">Vous n\'avez encore répondu à aucune séance.</p>';
        }
        $html .= '<table><tr><th>Séance</th><th>Date</th><th>Ma réponse</th><th>Présence réelle</th></tr>';
        foreach ($d['lignes'] as $l) {
            $reel = $l['venu'] === null ? '-' : ((int)$l['venu'] === 1 ? 'Venu' : 'Absent');
            $html .= '<tr><td><a href="?p=seance&id=' . (int)$l['id'] . '">' . Html::e($l['titre']) . '</a></td><td>'
                   . Html::dateFr($l['debut']) . '</td><td>' . Html::e(Regles::libelleReponse($l['reponse'])) . '</td><td>' . $reel . '</td></tr>';
        }
        return $html . '</table>';
    }
}
