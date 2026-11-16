<?php
// SLICE RepondrePresence : le coeur du projet.
// Règles : séance prévue (non annulée), réponses ouvertes (> 2 h avant le début), réponse modifiable librement jusque-là.
final class RepondrePresenceHandler
{
    private const REPONSES = ['present', 'absent', 'peut_etre'];

    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string} */
    public function handle(int $membreId, int $seanceId, string $reponse, DateTimeImmutable $now): array
    {
        if (!in_array($reponse, self::REPONSES, true)) {
            return ['ok' => false, 'erreur' => 'Réponse invalide.'];
        }
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $seanceId]);
        $seance = $s->fetch();
        if (!$seance) {
            return ['ok' => false, 'erreur' => 'Séance introuvable.'];
        }
        if ($seance['statut'] === 'annulee') {
            return ['ok' => false, 'erreur' => 'Cette séance est annulée.'];
        }
        if (!Regles::reponsesOuvertes($seance, $now)) {
            return ['ok' => false, 'erreur' => 'Les réponses sont closes (moins de ' . Regles::DELAI_REPONSE_HEURES . ' h avant la séance).'];
        }

        $quand = $now->format('Y-m-d H:i:s');
        $existe = $this->db->prepare('SELECT id FROM presence WHERE membre_id = :m AND seance_id = :s');
        $existe->execute([':m' => $membreId, ':s' => $seanceId]);
        if ($existe->fetchColumn()) {
            $this->db->prepare('UPDATE presence SET reponse = :r, reponse_le = :q WHERE membre_id = :m AND seance_id = :s')
                ->execute([':r' => $reponse, ':q' => $quand, ':m' => $membreId, ':s' => $seanceId]);
        } else {
            $this->db->prepare('INSERT INTO presence (membre_id, seance_id, reponse, reponse_le) VALUES (:m,:s,:r,:q)')
                ->execute([':m' => $membreId, ':s' => $seanceId, ':r' => $reponse, ':q' => $quand]);
        }
        return ['ok' => true, 'erreur' => null];
    }
}
