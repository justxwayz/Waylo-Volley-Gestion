<?php
// SLICE ModifierSeance (admin) : on ne modifie pas une séance annulée ni déjà commencée.
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
        $titre = trim($titre);
        $lieu = trim($lieu);
        $d = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $debut) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $debut);
        $f = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $fin) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $fin);
        if ($titre === '' || $lieu === '' || !$d || !$f) {
            return ['ok' => false, 'erreur' => 'Champs invalides.'];
        }
        if ($f <= $d) {
            return ['ok' => false, 'erreur' => 'La fin doit être après le début.'];
        }
        $this->db->prepare('UPDATE seance SET titre=:t, debut=:d, fin=:f, lieu=:l WHERE id=:id')
            ->execute([':t' => $titre, ':d' => $d->format('Y-m-d H:i:s'), ':f' => $f->format('Y-m-d H:i:s'), ':l' => $lieu, ':id' => $id]);
        return ['ok' => true, 'erreur' => null];
    }
}
