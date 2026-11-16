<?php
// SLICE VoirCalendrier : séances ET événements du mois, avec MA réponse / MON inscription.
final class VoirCalendrierHandler
{
    public function __construct(private PDO $db) {}

    /** Normalise le paramètre "YYYY-MM" (mois courant si invalide). */
    public static function mois(?string $m, DateTimeImmutable $now): DateTimeImmutable
    {
        $d = $m ? DateTimeImmutable::createFromFormat('!Y-m', $m) : false;
        return $d ?: $now->modify('first day of this month')->setTime(0, 0);
    }

    /** @return array{seances:array<int,array<string,mixed>>,evenements:array<int,array<string,mixed>>} */
    public function handle(int $membreId, DateTimeImmutable $mois): array
    {
        return ['seances' => $this->seances($membreId, $mois), 'evenements' => $this->evenements($membreId, $mois)];
    }

    private function evenements(int $membreId, DateTimeImmutable $mois): array
    {
        $stmt = $this->db->prepare(
            'SELECT e.id, e.titre, e.debut, e.fin, e.lieu, e.statut,
                    (SELECT COUNT(*) FROM inscription_evenement i WHERE i.evenement_id = e.id) AS nb_inscrits,
                    (SELECT COUNT(*) FROM inscription_evenement i WHERE i.evenement_id = e.id AND i.membre_id = :m) AS moi
             FROM evenement e WHERE e.debut >= :d AND e.debut < :f ORDER BY e.debut ASC'
        );
        $stmt->execute([':m' => $membreId, ':d' => $mois->format('Y-m-01 00:00:00'), ':f' => $mois->modify('first day of next month')->format('Y-m-d 00:00:00')]);
        return $stmt->fetchAll();
    }

    private function seances(int $membreId, DateTimeImmutable $mois): array
    {
        $debut = $mois->format('Y-m-01 00:00:00');
        $fin = $mois->modify('first day of next month')->format('Y-m-d 00:00:00');
        $stmt = $this->db->prepare(
            'SELECT s.id, s.titre, s.debut, s.fin, s.lieu, s.statut,
                    p.reponse AS ma_reponse,
                    (SELECT COUNT(*) FROM presence x WHERE x.seance_id = s.id AND x.reponse = \'present\') AS nb_presents
             FROM seance s
             LEFT JOIN presence p ON p.seance_id = s.id AND p.membre_id = :m
             WHERE s.debut >= :d AND s.debut < :f
             ORDER BY s.debut ASC'
        );
        $stmt->execute([':m' => $membreId, ':d' => $debut, ':f' => $fin]);
        return $stmt->fetchAll();
    }
}
