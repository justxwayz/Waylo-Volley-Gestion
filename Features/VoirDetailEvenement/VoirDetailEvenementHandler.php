<?php
// SLICE VoirDetailEvenement : fiche d'un événement, mon inscription, liste des inscrits.
final class VoirDetailEvenementHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{evenement:array,inscrit:bool,inscrits:array,ouvert:bool}|null */
    public function handle(int $evenementId, int $membreId, DateTimeImmutable $now): ?array
    {
        $s = $this->db->prepare('SELECT * FROM evenement WHERE id = :id');
        $s->execute([':id' => $evenementId]);
        $e = $s->fetch();
        if (!$e) {
            return null;
        }
        $i = $this->db->prepare(
            'SELECT m.id, m.nom, i.inscrit_le FROM inscription_evenement i JOIN membre m ON m.id = i.membre_id
             WHERE i.evenement_id = :id ORDER BY m.nom ASC'
        );
        $i->execute([':id' => $evenementId]);
        $inscrits = $i->fetchAll();
        $moi = in_array($membreId, array_map('intval', array_column($inscrits, 'id')), true);
        return ['evenement' => $e, 'inscrit' => $moi, 'inscrits' => $inscrits, 'ouvert' => Regles::inscriptionOuverte($e, $now)];
    }
}
