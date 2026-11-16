<?php
final class VoirDetailEvenementView
{
    public static function render(Context $c, array $d): string
    {
        $e = $d['evenement'];
        $id = (int)$e['id'];
        $statut = $e['statut'] === 'annule' ? '<p><span class="tag annulee">Événement annulé</span></p>' : '';
        $desc = $e['description'] !== null && $e['description'] !== '' ? '<p>' . nl2br(Html::e($e['description'])) . '</p>' : '';
        $html = '<div class="card">' . $statut . '<h2>' . Html::e($e['titre']) . '</h2>' . $desc
              . '<p>' . Html::dateFr($e['debut']) . ' de ' . Html::heure($e['debut']) . ' à ' . Html::heure($e['fin']) . '<br>' . Html::e($e['lieu']) . '</p>'
              . '<p class="muted">Inscriptions jusqu\'au ' . Html::dateFr($e['date_limite']) . ' à ' . Html::heure($e['date_limite']) . ' - '
              . count($d['inscrits']) . ' inscrit(s)</p></div>';

        $html .= '<div class="card"><h2>Mon inscription : ' . ($d['inscrit'] ? 'inscrit' : 'non inscrit') . '</h2>';
        if ($d['ouvert']) {
            $html .= '<form method="post" action="?p=evenement/inscrire&id=' . $id . '">' . Html::csrfField($c->session)
                   . ($d['inscrit']
                        ? '<button name="action" value="desinscrire" class="sec">Me désinscrire</button>'
                        : '<button name="action" value="inscrire">Je m\'inscris</button>')
                   . '</form>';
        } else {
            $html .= '<p class="muted">Les inscriptions sont closes.</p>';
        }
        $html .= '</div>';

        if (Roles::atteint($c->user()['role'], 'coach')) {
            $noms = array_map(fn($i) => Html::e($i['nom']), $d['inscrits']);
            $html .= '<div class="card"><h2>Inscrits (' . count($noms) . ')</h2><p>' . ($noms ? implode(', ', $noms) : '<span class="muted">Personne</span>') . '</p></div>';
        }
        if (Roles::atteint($c->user()['role'], 'admin') && $e['statut'] === 'prevu') {
            $html .= '<div class="card"><form method="post" action="?p=evenement/annuler&id=' . $id . '" class="inline">' . Html::csrfField($c->session)
                   . '<button class="ko">Annuler l\'événement</button></form></div>';
        }
        return $html;
    }
}
