<?php
// SLICE AnnulerSeance (admin) : on n'efface jamais, on marque "annulee" (l'historique des présences est conservé).
final class AnnulerSeanceHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string} */
    public function handle(int $id, DateTimeImmutable $now): array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $id]);
        $seance = $s->fetch();
        if (!$seance) {
            return ['ok' => false, 'erreur' => 'Séance introuvable.'];
        }
        if ($seance['statut'] === 'annulee') {
            return ['ok' => false, 'erreur' => 'Cette séance est déjà annulée.'];
        }
        if (new DateTimeImmutable($seance['debut']) <= $now) {
            return ['ok' => false, 'erreur' => 'Une séance déjà commencée ne peut plus être annulée.'];
        }
        $this->db->prepare("UPDATE seance SET statut = 'annulee' WHERE id = :id")->execute([':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }
}
