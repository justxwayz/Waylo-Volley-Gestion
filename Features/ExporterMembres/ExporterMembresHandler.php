<?php
// SLICE ExporterMembres (admin) : liste des membres en CSV. Jamais de mot de passe (ni de hash) dans l'export.
final class ExporterMembresHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{csv:string,nom:string} */
    public function handle(DateTimeImmutable $now): array
    {
        $rows = $this->db->query('SELECT nom, email, role, actif, cree_le FROM membre ORDER BY nom ASC')->fetchAll();
        $lignes = array_map(fn($m) => [$m['nom'], $m['email'], Roles::libelle($m['role']), (int)$m['actif'] === 1 ? 'Actif' : 'Désactivé', Html::dateFr($m['cree_le'])], $rows);
        return ['csv' => Csv::encoder(['Nom', 'Email', 'Rôle', 'État', 'Créé le'], $lignes), 'nom' => Csv::nomFichier('membres', $now->format('Y-m-d'))];
    }
}
