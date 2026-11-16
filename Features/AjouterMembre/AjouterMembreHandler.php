<?php
// SLICE AjouterMembre (admin) : création d'un compte avec mot de passe temporaire.
final class AjouterMembreHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,mot_de_passe:?string} */
    public function handle(string $nom, string $email, string $role, string $motDePasse, DateTimeImmutable $now): array
    {
        $nom = trim($nom);
        $email = strtolower(trim($email));
        if ($nom === '' || mb_strlen($nom) > 100) {
            return self::ko('Le nom est obligatoire (100 caractères maximum).');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return self::ko('Email invalide.');
        }
        if (!in_array($role, Roles::valides(), true)) {
            return self::ko('Rôle invalide.');
        }
        if ($motDePasse !== '' && strlen($motDePasse) < 8) {
            return self::ko('Le mot de passe doit faire au moins 8 caractères.');
        }
        $existe = $this->db->prepare('SELECT COUNT(*) FROM membre WHERE email = :e');
        $existe->execute([':e' => $email]);
        if ((int)$existe->fetchColumn() > 0) {
            return self::ko('Un membre avec cet email existe déjà.');
        }

        $mdp = $motDePasse !== '' ? $motDePasse : substr(bin2hex(random_bytes(6)), 0, 10);
        $this->db->prepare('INSERT INTO membre (nom, email, mot_de_passe, role, actif, cree_le) VALUES (:n,:e,:p,:r,1,:c)')
            ->execute([
                ':n' => $nom, ':e' => $email, ':p' => password_hash($mdp, PASSWORD_DEFAULT),
                ':r' => $role, ':c' => $now->format('Y-m-d H:i:s'),
            ]);
        return ['ok' => true, 'erreur' => null, 'mot_de_passe' => $mdp];
    }

    private static function ko(string $e): array
    {
        return ['ok' => false, 'erreur' => $e, 'mot_de_passe' => null];
    }
}
