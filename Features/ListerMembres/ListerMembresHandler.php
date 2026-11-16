<?php
// SLICE ListerMembres (admin) : liste + recherche par nom ou email.
final class ListerMembresHandler
{
    public function __construct(private PDO $db) {}

    /** @return array<int,array<string,mixed>> */
    public function handle(string $recherche = ''): array
    {
        $recherche = trim($recherche);
        if ($recherche === '') {
            return $this->db->query('SELECT id, nom, email, role, actif FROM membre ORDER BY actif DESC, nom ASC')->fetchAll();
        }
        $stmt = $this->db->prepare(
            'SELECT id, nom, email, role, actif FROM membre WHERE nom LIKE :q OR email LIKE :q ORDER BY actif DESC, nom ASC'
        );
        $stmt->execute([':q' => '%' . $recherche . '%']);
        return $stmt->fetchAll();
    }
}
