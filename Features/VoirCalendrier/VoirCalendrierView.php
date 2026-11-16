<?php
final class VoirCalendrierView
{
    private const MOIS = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];
    private const JOURS = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];

    /** @param array{seances:array,evenements:array} $d */
    public static function render(array $d, DateTimeImmutable $mois, DateTimeImmutable $now): string
    {
        $prev = $mois->modify('-1 month')->format('Y-m');
        $next = $mois->modify('+1 month')->format('Y-m');
        $titre = self::MOIS[(int)$mois->format('n')] . ' ' . $mois->format('Y');
        $html = '<p><a href="?p=calendrier&m=' . $prev . '">&larr; Mois précédent</a> &nbsp; <strong>' . Html::e($titre)
              . '</strong> &nbsp; <a href="?p=calendrier&m=' . $next . '">Mois suivant &rarr;</a>'
              . ' &nbsp; <a href="?p=calendrier">Aujourd\'hui</a></p>';

        return $html . self::grille($d, $mois, $now) . self::liste($d);
    }

    /** Grille 7 colonnes (lundi en premier), affichée sur grand écran. */
    private static function grille(array $d, DateTimeImmutable $mois, DateTimeImmutable $now): string
    {
        $parJour = [];
        foreach ($d['seances'] as $s) {
            $parJour[(int)substr($s['debut'], 8, 2)][] = ['t' => 's', 'x' => $s];
        }
        foreach ($d['evenements'] as $e) {
            $parJour[(int)substr($e['debut'], 8, 2)][] = ['t' => 'e', 'x' => $e];
        }
        $decalage = (int)$mois->format('N') - 1;
        $nbJours = (int)$mois->format('t');
        $cases = (int)(ceil(($decalage + $nbJours) / 7) * 7);
        $aujourdhui = $now->format('Y-m-d');

        $h = '<div class="cal-grille">';
        foreach (self::JOURS as $j) {
            $h .= '<div class="cal-entete">' . $j . '</div>';
        }
        for ($i = 0; $i < $cases; $i++) {
            $jour = $i - $decalage + 1;
            if ($jour < 1 || $jour > $nbJours) {
                $h .= '<div class="cal-case vide"></div>';
                continue;
            }
            $date = $mois->format('Y-m-') . sprintf('%02d', $jour);
            $h .= '<div class="cal-case' . ($date === $aujourdhui ? ' aujourdhui' : '') . '"><span class="cal-num">' . $jour . '</span>';
            foreach ($parJour[$jour] ?? [] as $it) {
                $x = $it['x'];
                if ($it['t'] === 's') {
                    $classe = $x['statut'] === 'annulee' ? 'annulee' : ($x['ma_reponse'] ?: 'sans');
                    $h .= '<a class="chip ' . Html::e($classe) . '" href="?p=seance&id=' . (int)$x['id'] . '" title="' . Html::e($x['titre'] . ' - ' . $x['lieu']) . '">'
                        . Html::heure($x['debut']) . ' ' . Html::e($x['titre']) . '</a>';
                } else {
                    $h .= '<a class="chip evt' . ($x['statut'] === 'annule' ? ' annulee' : '') . '" href="?p=evenement&id=' . (int)$x['id'] . '" title="Événement - ' . Html::e($x['lieu']) . '">'
                        . 'Événement : ' . Html::e($x['titre']) . '</a>';
                }
            }
            $h .= '</div>';
        }
        return $h . '</div><p class="cal-legende muted">Vert : vous venez - Rouge : absent - Jaune : peut-être - Gris : sans réponse - Barré : annulé - Bleu : événement</p>';
    }

    /** Liste chronologique (petits écrans, et lecture détaillée). */
    private static function liste(array $d): string
    {
        $items = [];
        foreach ($d['seances'] as $s) {
            $items[] = [$s['debut'], 's', $s];
        }
        foreach ($d['evenements'] as $e) {
            $items[] = [$e['debut'], 'e', $e];
        }
        usort($items, fn($a, $b) => strcmp($a[0], $b[0]));
        if (!$items) {
            return '<div class="cal-liste"><p class="muted">Aucune séance ni événement ce mois-ci.</p></div>';
        }
        $h = '<div class="cal-liste">';
        foreach ($items as [, $type, $x]) {
            if ($type === 's') {
                $statut = $x['statut'] === 'annulee' ? ' <span class="tag annulee">Annulée</span>' : '';
                $ma = $x['ma_reponse'] ? ' <span class="tag ' . Html::e($x['ma_reponse']) . '">' . Html::e(Regles::libelleReponse($x['ma_reponse'])) . '</span>' : '';
                $h .= '<div class="card"><a href="?p=seance&id=' . (int)$x['id'] . '"><strong>' . Html::e($x['titre']) . '</strong></a>' . $statut . $ma
                    . '<br><span class="muted">' . Html::dateFr($x['debut']) . ' de ' . Html::heure($x['debut']) . ' à ' . Html::heure($x['fin'])
                    . ' - ' . Html::e($x['lieu']) . ' - ' . (int)$x['nb_presents'] . ' présent(s)</span></div>';
            } else {
                $statut = $x['statut'] === 'annule' ? ' <span class="tag annulee">Annulé</span>' : '';
                $moi = (int)$x['moi'] > 0 ? ' <span class="tag present">Inscrit</span>' : '';
                $h .= '<div class="card"><span class="tag evt">Événement</span> <a href="?p=evenement&id=' . (int)$x['id'] . '"><strong>' . Html::e($x['titre']) . '</strong></a>' . $statut . $moi
                    . '<br><span class="muted">' . Html::dateFr($x['debut']) . ' de ' . Html::heure($x['debut']) . ' à ' . Html::heure($x['fin'])
                    . ' - ' . Html::e($x['lieu']) . ' - ' . (int)$x['nb_inscrits'] . ' inscrit(s)</span></div>';
            }
        }
        return $h . '</div>';
    }
}
