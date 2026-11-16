<?php
// Hiérarchie des rôles : membre < coach < admin. Un rôle supérieur hérite des droits des rôles inférieurs.
final class Roles
{
    public const NIVEAUX = ['membre' => 1, 'coach' => 2, 'admin' => 3];

    public static function valides(): array
    {
        return array_keys(self::NIVEAUX);
    }

    public static function atteint(?string $roleUtilisateur, string $roleRequis): bool
    {
        if ($roleRequis === 'public') {
            return true;
        }
        return (self::NIVEAUX[$roleUtilisateur ?? ''] ?? 0) >= (self::NIVEAUX[$roleRequis] ?? PHP_INT_MAX);
    }

    public static function libelle(string $role): string
    {
        return ['membre' => 'Membre', 'coach' => 'Coach', 'admin' => 'Administrateur'][$role] ?? $role;
    }
}
