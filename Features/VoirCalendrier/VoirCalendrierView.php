<?php
final class VoirCalendrierView
{
    private const MOIS = ['', 'janvier', 'février', 'mars', 'avril', 'mai', 'juin', 'juillet', 'août', 'septembre', 'octobre', 'novembre', 'décembre'];

    public static function render(array $seances, DateTimeImmutable $mois): string
    {
        $prev = $mois->modify('-1 month')->format('Y-m');
        $next = $mois->modify('+1 month')->format('Y-m');
        $titre = self::MOIS[(int)$mois->format('n')] . ' ' . $mois->format('Y');
        $html = '<p><a href="?p=calendrier&m=' . $prev . '">&larr; Mois précédent</a> &nbsp; <strong>' . Html::e($titre)
              . '</strong> &nbsp; <a href="?p=calendrier&m=' . $next . '">Mois suivant &rarr;</a></p>';
        if (!$seances) {
            return $html . '<p class="muted">Aucune séance ce mois-ci.</p>';
        }
        foreach ($seances as $s) {
            $statut = $s['statut'] === 'annulee' ? ' <span class="tag annulee">Annulée</span>' : '';
            $ma = $s['ma_reponse'] ? ' <span class="tag ' . Html::e($s['ma_reponse']) . '">' . Html::e(Regles::libelleReponse($s['ma_reponse'])) . '</span>' : '';
            $html .= '<div class="card"><a href="?p=seance&id=' . (int)$s['id'] . '"><strong>' . Html::e($s['titre']) . '</strong></a>' . $statut . $ma
                   . '<br><span class="muted">' . Html::dateFr($s['debut']) . ' de ' . Html::heure($s['debut']) . ' à ' . Html::heure($s['fin'])
                   . ' - ' . Html::e($s['lieu']) . ' - ' . (int)$s['nb_presents'] . ' présent(s)</span></div>';
        }
        return $html;
    }
}
