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

    /** Inscription possible : événement prévu, avant la date limite (et avant le début). */
    public static function inscriptionOuverte(array $evenement, DateTimeImmutable $now): bool
    {
        return $evenement['statut'] === 'prevu'
            && $now < new DateTimeImmutable($evenement['date_limite'])
            && $now < new DateTimeImmutable($evenement['debut']);
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
