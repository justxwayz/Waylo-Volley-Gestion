<?php
// SLICE ModifierMonProfil : changer son nom et, si besoin, son mot de passe (ancien mot de passe exigé).
final class ModifierMonProfilHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string} */
    public function handle(int $membreId, string $nom, string $ancien, string $nouveau): array
    {
        $nom = trim($nom);
        if ($nom === '' || mb_strlen($nom) > 100) {
            return ['ok' => false, 'erreur' => 'Le nom est obligatoire (100 caractères maximum).'];
        }
        if ($nouveau !== '') {
            if (strlen($nouveau) < 8) {
                return ['ok' => false, 'erreur' => 'Le nouveau mot de passe doit faire au moins 8 caractères.'];
            }
            $stmt = $this->db->prepare('SELECT mot_de_passe FROM membre WHERE id = :id');
            $stmt->execute([':id' => $membreId]);
            $hash = $stmt->fetchColumn();
            if (!$hash || !password_verify($ancien, $hash)) {
                return ['ok' => false, 'erreur' => 'Ancien mot de passe incorrect.'];
            }
            $this->db->prepare('UPDATE membre SET nom = :n, mot_de_passe = :p WHERE id = :id')
                ->execute([':n' => $nom, ':p' => password_hash($nouveau, PASSWORD_DEFAULT), ':id' => $membreId]);
        } else {
            $this->db->prepare('UPDATE membre SET nom = :n WHERE id = :id')->execute([':n' => $nom, ':id' => $membreId]);
        }
        return ['ok' => true, 'erreur' => null];
    }

    public function find(int $membreId): array
    {
        $stmt = $this->db->prepare('SELECT id, nom, email FROM membre WHERE id = :id');
        $stmt->execute([':id' => $membreId]);
        return $stmt->fetch();
    }
}
