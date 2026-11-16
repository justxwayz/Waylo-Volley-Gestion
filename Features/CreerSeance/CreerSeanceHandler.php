<?php
// SLICE CreerSeance (admin) : validation (commune, voir Shared/SeanceDonnees) puis insertion.
final class CreerSeanceHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,id:?int} */
    public function handle(string $titre, string $debut, string $fin, string $lieu, DateTimeImmutable $now): array
    {
        $v = SeanceDonnees::valider($titre, $debut, $fin, $lieu);
        if (!$v['ok']) {
            return ['ok' => false, 'erreur' => $v['erreur'], 'id' => null];
        }
        $this->db->prepare('INSERT INTO seance (titre, debut, fin, lieu, statut, cree_le) VALUES (:t,:d,:f,:l,\'prevue\',:c)')
            ->execute([':t' => $v['titre'], ':d' => $v['debut'], ':f' => $v['fin'], ':l' => $v['lieu'], ':c' => $now->format('Y-m-d H:i:s')]);
        return ['ok' => true, 'erreur' => null, 'id' => (int)$this->db->lastInsertId()];
    }
}
