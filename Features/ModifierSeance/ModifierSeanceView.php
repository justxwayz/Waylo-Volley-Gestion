<?php
final class ModifierSeanceView
{
    public static function form(Context $c, array $seance, ?string $erreur = null): string
    {
        $id = (int)$seance['id'];
        return SeanceFormulaire::render($c, '?p=seance/modifier&id=' . $id, 'Enregistrer', $seance, $erreur, '?p=seance&id=' . $id);
    }
}
