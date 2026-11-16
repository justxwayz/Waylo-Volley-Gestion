<?php
final class VoirDetailSeanceView
{
    public static function render(Context $c, array $d): string
    {
        $s = $d['seance'];
        $id = (int)$s['id'];
        $statut = $s['statut'] === 'annulee' ? '<p><span class="tag annulee">Séance annulée</span></p>' : '';
        $html = '<div class="card">' . $statut
              . '<h2>' . Html::e($s['titre']) . '</h2>'
              . '<p>' . Html::dateFr($s['debut']) . ' de ' . Html::heure($s['debut']) . ' à ' . Html::heure($s['fin']) . '<br>' . Html::e($s['lieu']) . '</p>'
              . '<p class="muted">' . $d['compteurs']['present'] . ' présent(s), ' . $d['compteurs']['peut_etre'] . ' peut-être, ' . $d['compteurs']['absent'] . ' absent(s)</p></div>';

        $html .= '<div class="card"><h2>Ma réponse : ' . Html::e(Regles::libelleReponse($d['ma_reponse'])) . '</h2>';
        if ($d['ouvert']) {
            $csrf = Html::csrfField($c->session);
            $html .= '<form method="post" action="?p=presence/repondre&id=' . $id . '">' . $csrf
                   . '<button name="reponse" value="present">Je viens</button> '
                   . '<button name="reponse" value="peut_etre" class="sec">Peut-être</button> '
                   . '<button name="reponse" value="absent" class="ko">Je ne viens pas</button></form>';
        } else {
            $html .= '<p class="muted">Les réponses sont closes pour cette séance.</p>';
        }
        $html .= '</div>';

        if ($c->user()['role'] === 'admin') {
            $html .= '<div class="card"><h2>Administration</h2><p>'
                   . '<a class="btn" href="?p=seance/participants&id=' . $id . '">Participants</a> '
                   . '<a class="btn" href="?p=seance/pointage&id=' . $id . '">Pointage</a> '
                   . '<a class="btn" href="?p=seance/modifier&id=' . $id . '">Modifier</a> ';
            if ($s['statut'] === 'prevue') {
                $html .= '<form method="post" action="?p=seance/annuler&id=' . $id . '" class="inline">' . Html::csrfField($c->session)
                       . '<button class="ko">Annuler la séance</button></form>';
            }
            $html .= '</p></div>';
        }
        return $html;
    }
}
