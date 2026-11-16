<?php
final class Html
{
    public static function e(mixed $v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }

    public static function dateFr(string $dt): string
    {
        return (new DateTimeImmutable($dt))->format('d/m/Y');
    }

    public static function heure(string $dt): string
    {
        return (new DateTimeImmutable($dt))->format('H:i');
    }

    public static function csrfField(Session $s): string
    {
        return '<input type="hidden" name="csrf" value="' . self::e($s->csrf()) . '">';
    }
}
