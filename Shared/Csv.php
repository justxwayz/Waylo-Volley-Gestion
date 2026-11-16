<?php
// Encodage CSV pour Excel/LibreOffice : séparateur ";", BOM UTF-8, fins de ligne CRLF.
// Protection contre l'injection de formules : une cellule commençant par = + - @ (ou tabulation) est préfixée par une apostrophe.
final class Csv
{
    /** @param array<int,array<int,mixed>> $lignes */
    public static function encoder(array $entetes, array $lignes): string
    {
        $out = "\xEF\xBB\xBF" . self::ligne($entetes);
        foreach ($lignes as $l) {
            $out .= self::ligne($l);
        }
        return $out;
    }

    private static function ligne(array $cellules): string
    {
        return implode(';', array_map([self::class, 'cellule'], $cellules)) . "\r\n";
    }

    public static function cellule(mixed $v): string
    {
        $s = $v === null ? '' : (string)$v;
        if ($s !== '' && in_array($s[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            $s = "'" . $s;
        }
        if (preg_match('/[;"\r\n]/', $s)) {
            $s = '"' . str_replace('"', '""', $s) . '"';
        }
        return $s;
    }

    public static function nomFichier(string $base, string $suffixe = ''): string
    {
        $slug = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($base . ' ' . $suffixe)), '-');
        return $slug . '.csv';
    }
}
