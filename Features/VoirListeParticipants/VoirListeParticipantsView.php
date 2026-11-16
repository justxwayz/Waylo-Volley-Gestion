<?php
final class VoirListeParticipantsView
{
    public static function render(array $d): string
    {
        $s = $d['seance'];
        $html = '<p><a href="?p=seance&id=' . (int)$s['id'] . '">&larr; ' . Html::e($s['titre']) . ' (' . Html::dateFr($s['debut']) . ')</a></p>';
        $groupes = ['present' => [], 'peut_etre' => [], 'absent' => []];
        foreach ($d['reponses'] as $r) {
            $groupes[$r['reponse']][] = $r['nom'];
        }
        foreach ($groupes as $cle => $noms) {
            $html .= '<div class="card"><h2>' . Html::e(Regles::libelleReponse($cle)) . ' (' . count($noms) . ')</h2>'
                   . ($noms ? '<p>' . implode(', ', array_map([Html::class, 'e'], $noms)) . '</p>' : '<p class="muted">Personne</p>') . '</div>';
        }
        $sans = array_map(fn($m) => Html::e($m['nom']), $d['sans_reponse']);
        $html .= '<div class="card"><h2>Sans réponse (' . count($sans) . ')</h2>'
               . ($sans ? '<p>' . implode(', ', $sans) . '</p>' : '<p class="muted">Tout le monde a répondu</p>') . '</div>';
        return $html;
    }
}
