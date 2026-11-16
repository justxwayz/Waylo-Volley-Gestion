<?php
// SLICE VoirListeParticipants (admin) : qui a répondu quoi, et qui n'a PAS répondu (membres actifs).
final class VoirListeParticipantsHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{seance:array,reponses:array,sans_reponse:array}|null */
    public function handle(int $seanceId): ?array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $seanceId]);
        $seance = $s->fetch();
        if (!$seance) {
            return null;
        }
        $r = $this->db->prepare(
            'SELECT m.id, m.nom, p.reponse, p.reponse_le
             FROM presence p JOIN membre m ON m.id = p.membre_id
             WHERE p.seance_id = :s ORDER BY m.nom ASC'
        );
        $r->execute([':s' => $seanceId]);

        $n = $this->db->prepare(
            'SELECT m.id, m.nom FROM membre m
             WHERE m.actif = 1 AND m.id NOT IN (SELECT membre_id FROM presence WHERE seance_id = :s)
             ORDER BY m.nom ASC'
        );
        $n->execute([':s' => $seanceId]);

        return ['seance' => $seance, 'reponses' => $r->fetchAll(), 'sans_reponse' => $n->fetchAll()];
    }
}
