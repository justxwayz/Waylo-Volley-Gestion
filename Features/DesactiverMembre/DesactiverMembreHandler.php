<?php
// SLICE DesactiverMembre (admin) : on désactive, on ne supprime jamais (l'historique de présences est conservé).
// Garde-fous : pas d'auto-désactivation, et toujours au moins un admin actif.
final class DesactiverMembreHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string} */
    public function desactiver(int $id, int $acteurId): array
    {
        $m = $this->find($id);
        if (!$m) {
            return ['ok' => false, 'erreur' => 'Membre introuvable.'];
        }
        if ($id === $acteurId) {
            return ['ok' => false, 'erreur' => 'Vous ne pouvez pas désactiver votre propre compte.'];
        }
        if ((int)$m['actif'] === 0) {
            return ['ok' => false, 'erreur' => 'Ce membre est déjà désactivé.'];
        }
        if ($m['role'] === 'admin') {
            $n = (int)$this->db->query("SELECT COUNT(*) FROM membre WHERE role = 'admin' AND actif = 1")->fetchColumn();
            if ($n <= 1) {
                return ['ok' => false, 'erreur' => 'Impossible : c\'est le dernier administrateur actif.'];
            }
        }
        $this->db->prepare('UPDATE membre SET actif = 0 WHERE id = :id')->execute([':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }

    /** @return array{ok:bool,erreur:?string} */
    public function reactiver(int $id): array
    {
        $m = $this->find($id);
        if (!$m) {
            return ['ok' => false, 'erreur' => 'Membre introuvable.'];
        }
        if ((int)$m['actif'] === 1) {
            return ['ok' => false, 'erreur' => 'Ce membre est déjà actif.'];
        }
        $this->db->prepare('UPDATE membre SET actif = 1 WHERE id = :id')->execute([':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }

    private function find(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM membre WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }
}
