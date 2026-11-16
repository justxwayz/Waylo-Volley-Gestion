<?php
// SLICE CreerSeance (admin) : validation des champs + règle "la fin est après le début".
final class CreerSeanceHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,id:?int} */
    public function handle(string $titre, string $debut, string $fin, string $lieu, DateTimeImmutable $now): array
    {
        $titre = trim($titre);
        $lieu = trim($lieu);
        if ($titre === '' || $lieu === '') {
            return ['ok' => false, 'erreur' => 'Le titre et le lieu sont obligatoires.', 'id' => null];
        }
        $d = self::parse($debut);
        $f = self::parse($fin);
        if (!$d || !$f) {
            return ['ok' => false, 'erreur' => 'Dates invalides.', 'id' => null];
        }
        if ($f <= $d) {
            return ['ok' => false, 'erreur' => 'La fin doit être après le début.', 'id' => null];
        }
        $this->db->prepare('INSERT INTO seance (titre, debut, fin, lieu, statut, cree_le) VALUES (:t,:d,:f,:l,\'prevue\',:c)')
            ->execute([
                ':t' => $titre, ':d' => $d->format('Y-m-d H:i:s'), ':f' => $f->format('Y-m-d H:i:s'),
                ':l' => $lieu, ':c' => $now->format('Y-m-d H:i:s'),
            ]);
        return ['ok' => true, 'erreur' => null, 'id' => (int)$this->db->lastInsertId()];
    }

    private static function parse(string $v): ?DateTimeImmutable
    {
        // accepte "2026-03-10T20:00" (input datetime-local) ou "2026-03-10 20:00:00"
        $dt = DateTimeImmutable::createFromFormat('Y-m-d\TH:i', $v) ?: DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $v);
        return $dt ?: null;
    }
}
