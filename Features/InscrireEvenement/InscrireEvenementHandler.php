<?php
// SLICE InscrireEvenement : s'inscrire / se désinscrire.
// Règles : événement prévu (non annulé), avant la date limite, jamais d'inscription en double.
final class InscrireEvenementHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,message:?string} */
    public function handle(int $membreId, int $evenementId, string $action, DateTimeImmutable $now): array
    {
        if (!in_array($action, ['inscrire', 'desinscrire'], true)) {
            return self::ko('Action invalide.');
        }
        $s = $this->db->prepare('SELECT * FROM evenement WHERE id = :id');
        $s->execute([':id' => $evenementId]);
        $e = $s->fetch();
        if (!$e) {
            return self::ko('Événement introuvable.');
        }
        if ($e['statut'] === 'annule') {
            return self::ko('Cet événement est annulé.');
        }
        if (!Regles::inscriptionOuverte($e, $now)) {
            return self::ko('Les inscriptions sont closes.');
        }

        $x = $this->db->prepare('SELECT COUNT(*) FROM inscription_evenement WHERE membre_id = :m AND evenement_id = :e');
        $x->execute([':m' => $membreId, ':e' => $evenementId]);
        $dejaInscrit = (int)$x->fetchColumn() > 0;

        if ($action === 'inscrire') {
            if ($dejaInscrit) {
                return self::ko('Vous êtes déjà inscrit.');
            }
            $this->db->prepare('INSERT INTO inscription_evenement (membre_id, evenement_id, inscrit_le) VALUES (:m,:e,:q)')
                ->execute([':m' => $membreId, ':e' => $evenementId, ':q' => $now->format('Y-m-d H:i:s')]);
            return ['ok' => true, 'erreur' => null, 'message' => 'Inscription enregistrée.'];
        }
        if (!$dejaInscrit) {
            return self::ko('Vous n\'êtes pas inscrit.');
        }
        $this->db->prepare('DELETE FROM inscription_evenement WHERE membre_id = :m AND evenement_id = :e')
            ->execute([':m' => $membreId, ':e' => $evenementId]);
        return ['ok' => true, 'erreur' => null, 'message' => 'Désinscription enregistrée.'];
    }

    private static function ko(string $e): array
    {
        return ['ok' => false, 'erreur' => $e, 'message' => null];
    }
}
