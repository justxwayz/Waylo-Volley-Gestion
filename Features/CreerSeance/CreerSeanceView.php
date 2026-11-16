<?php
final class CreerSeanceView
{
    public static function form(Context $c, ?string $erreur = null, array $valeurs = []): string
    {
        return SeanceFormulaire::render($c, '?p=seance/creer', 'Créer la séance', $valeurs, $erreur);
    }
}
