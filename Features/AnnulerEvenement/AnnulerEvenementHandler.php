<?php
// SLICE AnnulerEvenement (admin) : on marque "annule", on ne supprime pas (les inscrits restent visibles).
final class AnnulerEvenementHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string} */
    public function handle(int $id, DateTimeImmutable $now): array
    {
        $s = $this->db->prepare('SELECT * FROM evenement WHERE id = :id');
        $s->execute([':id' => $id]);
        $e = $s->fetch();
        if (!$e) {
            return ['ok' => false, 'erreur' => 'Événement introuvable.'];
        }
        if ($e['statut'] === 'annule') {
            return ['ok' => false, 'erreur' => 'Cet événement est déjà annulé.'];
        }
        if (new DateTimeImmutable($e['debut']) <= $now) {
            return ['ok' => false, 'erreur' => 'Un événement déjà commencé ne peut plus être annulé.'];
        }
        $this->db->prepare("UPDATE evenement SET statut = 'annule' WHERE id = :id")->execute([':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }
}
