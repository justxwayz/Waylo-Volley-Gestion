<?php
// Validation et lecture des champs d'une séance, communes à CreerSeance et ModifierSeance.
final class SeanceDonnees
{
    /**
     * @return array{ok:bool,erreur:?string,titre:string,debut:?string,fin:?string,lieu:string}
     *   debut / fin au format "Y-m-d H:i:s" quand ok
     */
    public static function valider(string $titre, string $debut, string $fin, string $lieu): array
    {
        $titre = trim($titre);
        $lieu = trim($lieu);
        $base = ['ok' => false, 'erreur' => null, 'titre' => $titre, 'debut' => null, 'fin' => null, 'lieu' => $lieu];

        if ($titre === '' || $lieu === '') {
            return ['erreur' => 'Le titre et le lieu sont obligatoires.'] + $base;
        }
        if (mb_strlen($titre) > 150 || mb_strlen($lieu) > 150) {
            return ['erreur' => 'Titre et lieu : 150 caractères maximum.'] + $base;
        }
        $d = self::parse($debut);
        $f = self::parse($fin);
        if (!$d || !$f) {
            return ['erreur' => 'Dates invalides.'] + $base;
        }
        if ($f <= $d) {
            return ['erreur' => 'La fin doit être après le début.'] + $base;
        }
        return ['ok' => true, 'debut' => $d->format('Y-m-d H:i:s'), 'fin' => $f->format('Y-m-d H:i:s')] + $base;
    }

    /** Accepte "2026-03-10T20:00" (input datetime-local) ou "2026-03-10 20:00:00". */
    public static function parse(string $v): ?DateTimeImmutable
    {
        $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $v) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $v);
        return $dt ?: null;
    }

    /** "2026-03-10 20:00:00" -> "2026-03-10T20:00" (valeur d'un input datetime-local) */
    public static function pourInput(?string $dt): string
    {
        return $dt ? str_replace(' ', 'T', substr($dt, 0, 16)) : '';
    }
}
