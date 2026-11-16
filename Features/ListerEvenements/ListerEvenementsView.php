<?php
final class ListerEvenementsView
{
    public static function render(array $evenements, bool $passes): string
    {
        $lien = $passes ? '<a href="?p=evenements">Voir les événements à venir</a>' : '<a href="?p=evenements&passes=1">Voir les événements passés</a>';
        $html = '<p>' . $lien . '</p>';
        if (!$evenements) {
            return $html . '<p class="muted">Aucun événement.</p>';
        }
        foreach ($evenements as $e) {
            $tags = ($e['statut'] === 'annule' ? ' <span class="tag annulee">Annulé</span>' : '')
                  . ((int)$e['moi'] > 0 ? ' <span class="tag present">Inscrit</span>' : '');
            $html .= '<div class="card"><a href="?p=evenement&id=' . (int)$e['id'] . '"><strong>' . Html::e($e['titre']) . '</strong></a>' . $tags
                   . '<br><span class="muted">' . Html::dateFr($e['debut']) . ' à ' . Html::heure($e['debut']) . ' - ' . Html::e($e['lieu'])
                   . ' - ' . (int)$e['nb_inscrits'] . ' inscrit(s) - inscriptions jusqu\'au ' . Html::dateFr($e['date_limite']) . '</span></div>';
        }
        return $html;
    }
}
