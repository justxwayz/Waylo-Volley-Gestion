<?php
// SLICE ListerEvenements : événements à venir (et récents), avec mon inscription et le nombre d'inscrits.
final class ListerEvenementsHandler
{
    public function __construct(private PDO $db) {}

    /** @return array<int,array<string,mixed>> */
    public function handle(int $membreId, DateTimeImmutable $now, bool $passes = false): array
    {
        $sql = 'SELECT e.id, e.titre, e.debut, e.fin, e.lieu, e.date_limite, e.statut,
                       (SELECT COUNT(*) FROM inscription_evenement i WHERE i.evenement_id = e.id) AS nb_inscrits,
                       (SELECT COUNT(*) FROM inscription_evenement i WHERE i.evenement_id = e.id AND i.membre_id = :m) AS moi
                FROM evenement e WHERE e.debut ' . ($passes ? '<' : '>=') . ' :now ORDER BY e.debut ' . ($passes ? 'DESC' : 'ASC');
        $s = $this->db->prepare($sql);
        $s->execute([':m' => $membreId, ':now' => $now->format('Y-m-d H:i:s')]);
        return $s->fetchAll();
    }
}
