<?php
// SLICE CreerEvenement (admin) : tournoi, match, soirée... avec date limite d'inscription.
// Règles : titre/lieu obligatoires, fin après début, date limite d'inscription au plus tard au début.
final class CreerEvenementHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,id:?int} */
    public function handle(string $titre, string $description, string $debut, string $fin, string $lieu, string $limite, DateTimeImmutable $now): array
    {
        $titre = trim($titre);
        $lieu = trim($lieu);
        $description = trim($description);
        if ($titre === '' || $lieu === '') {
            return self::ko('Le titre et le lieu sont obligatoires.');
        }
        if (mb_strlen($titre) > 150 || mb_strlen($lieu) > 150 || mb_strlen($description) > 2000) {
            return self::ko('Titre et lieu : 150 caractères max ; description : 2000 max.');
        }
        $d = SeanceDonnees::parse($debut);
        $f = SeanceDonnees::parse($fin);
        $l = SeanceDonnees::parse($limite);
        if (!$d || !$f || !$l) {
            return self::ko('Dates invalides.');
        }
        if ($f <= $d) {
            return self::ko('La fin doit être après le début.');
        }
        if ($l > $d) {
            return self::ko('La date limite d\'inscription doit être au plus tard au début de l\'événement.');
        }
        $this->db->prepare('INSERT INTO evenement (titre, description, debut, fin, lieu, date_limite, statut, cree_le) VALUES (:t,:de,:d,:f,:l,:li,\'prevu\',:c)')
            ->execute([
                ':t' => $titre, ':de' => $description, ':d' => $d->format('Y-m-d H:i:s'), ':f' => $f->format('Y-m-d H:i:s'),
                ':l' => $lieu, ':li' => $l->format('Y-m-d H:i:s'), ':c' => $now->format('Y-m-d H:i:s'),
            ]);
        return ['ok' => true, 'erreur' => null, 'id' => (int)$this->db->lastInsertId()];
    }

    private static function ko(string $e): array
    {
        return ['ok' => false, 'erreur' => $e, 'id' => null];
    }
}
