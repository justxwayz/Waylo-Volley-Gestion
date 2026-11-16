<?php
// Règles métier réellement partagées par plusieurs slices (à garder minimal).
final class Regles
{
    /** Les réponses de présence se ferment X heures avant le début de la séance. */
    public const DELAI_REPONSE_HEURES = 2;

    public static function reponsesOuvertes(array $seance, DateTimeImmutable $now): bool
    {
        if ($seance['statut'] !== 'prevue') {
            return false;
        }
        $limite = (new DateTimeImmutable($seance['debut']))->modify('-' . self::DELAI_REPONSE_HEURES . ' hours');
        return $now < $limite;
    }

    public static function libelleReponse(?string $r): string
    {
        return match ($r) {
            'present' => 'Présent',
            'absent' => 'Absent',
            'peut_etre' => 'Peut-être',
            default => 'Pas de réponse',
        };
    }
}
