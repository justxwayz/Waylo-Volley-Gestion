<?php
// SLICE ModifierSeance (admin) : on ne modifie pas une séance annulée ni déjà commencée (validation : Shared/SeanceDonnees).
final class ModifierSeanceHandler
{
    public function __construct(private PDO $db) {}

    public function find(int $id): ?array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $id]);
        return $s->fetch() ?: null;
    }

    /** @return array{ok:bool,erreur:?string} */
    public function handle(int $id, string $titre, string $debut, string $fin, string $lieu, DateTimeImmutable $now): array
    {
        $seance = $this->find($id);
        if (!$seance) {
            return ['ok' => false, 'erreur' => 'Séance introuvable.'];
        }
        if ($seance['statut'] === 'annulee') {
            return ['ok' => false, 'erreur' => 'Une séance annulée ne peut pas être modifiée.'];
        }
        if (new DateTimeImmutable($seance['debut']) <= $now) {
            return ['ok' => false, 'erreur' => 'Une séance déjà commencée ne peut plus être modifiée.'];
        }
        $v = SeanceDonnees::valider($titre, $debut, $fin, $lieu);
        if (!$v['ok']) {
            return ['ok' => false, 'erreur' => $v['erreur']];
        }
        $this->db->prepare('UPDATE seance SET titre=:t, debut=:d, fin=:f, lieu=:l WHERE id=:id')
            ->execute([':t' => $v['titre'], ':d' => $v['debut'], ':f' => $v['fin'], ':l' => $v['lieu'], ':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }
}
