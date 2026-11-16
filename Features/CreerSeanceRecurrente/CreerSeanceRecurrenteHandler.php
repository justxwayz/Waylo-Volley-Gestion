<?php
// SLICE CreerSeanceRecurrente (admin) : génère toutes les séances d'une période (ex. chaque mardi et jeudi).
// Règles : au moins un jour, heures cohérentes, période non passée, 120 séances maximum d'un coup,
//          une séance identique (même titre, même début) n'est jamais créée deux fois.
final class CreerSeanceRecurrenteHandler
{
    public const MAX_SEANCES = 120;
    public const JOURS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'];

    public function __construct(private PDO $db) {}

    /**
     * @param int[] $jours numéros ISO des jours (1 = lundi ... 7 = dimanche)
     * @return array{ok:bool,erreur:?string,creees:int,ignorees:int,premiere:?string}
     */
    public function handle(string $titre, string $lieu, array $jours, string $heureDebut, string $heureFin, string $dateDebut, string $dateFin, DateTimeImmutable $now): array
    {
        $titre = trim($titre);
        $lieu = trim($lieu);
        if ($titre === '' || $lieu === '') {
            return self::ko('Le titre et le lieu sont obligatoires.');
        }
        $jours = array_values(array_unique(array_filter(array_map('intval', $jours), fn($j) => $j >= 1 && $j <= 7)));
        if (!$jours) {
            return self::ko('Choisissez au moins un jour de la semaine.');
        }
        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $heureDebut) || !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $heureFin)) {
            return self::ko('Heures invalides.');
        }
        if ($heureFin <= $heureDebut) {
            return self::ko('L\'heure de fin doit être après l\'heure de début.');
        }
        $d = DateTimeImmutable::createFromFormat('!Y-m-d', $dateDebut);
        $f = DateTimeImmutable::createFromFormat('!Y-m-d', $dateFin);
        if (!$d || !$f) {
            return self::ko('Dates invalides.');
        }
        if ($f < $d) {
            return self::ko('La date de fin doit être après la date de début.');
        }
        if ($d < $now->setTime(0, 0)) {
            return self::ko('La période ne peut pas commencer dans le passé.');
        }

        // Génération des occurrences
        $occurrences = [];
        for ($jour = $d; $jour <= $f; $jour = $jour->modify('+1 day')) {
            if (in_array((int)$jour->format('N'), $jours, true)) {
                $occurrences[] = [$jour->format('Y-m-d') . ' ' . $heureDebut . ':00', $jour->format('Y-m-d') . ' ' . $heureFin . ':00'];
            }
            if (count($occurrences) > self::MAX_SEANCES) {
                return self::ko('Trop de séances d\'un coup (maximum ' . self::MAX_SEANCES . '). Réduisez la période.');
            }
        }
        if (!$occurrences) {
            return self::ko('Aucune séance ne correspond à ces jours sur cette période.');
        }

        $existe = $this->db->prepare('SELECT COUNT(*) FROM seance WHERE titre = :t AND debut = :d');
        $insert = $this->db->prepare('INSERT INTO seance (titre, debut, fin, lieu, statut, cree_le) VALUES (:t,:d,:f,:l,\'prevue\',:c)');
        $creees = 0;
        $ignorees = 0;
        $this->db->beginTransaction();
        foreach ($occurrences as [$debut, $fin]) {
            $existe->execute([':t' => $titre, ':d' => $debut]);
            if ((int)$existe->fetchColumn() > 0) {
                $ignorees++;
                continue;
            }
            $insert->execute([':t' => $titre, ':d' => $debut, ':f' => $fin, ':l' => $lieu, ':c' => $now->format('Y-m-d H:i:s')]);
            $creees++;
        }
        $this->db->commit();
        return ['ok' => true, 'erreur' => null, 'creees' => $creees, 'ignorees' => $ignorees, 'premiere' => $occurrences[0][0]];
    }

    private static function ko(string $e): array
    {
        return ['ok' => false, 'erreur' => $e, 'creees' => 0, 'ignorees' => 0, 'premiere' => null];
    }
}
