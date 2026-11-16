<?php
// SLICE VoirMesPresences : mon historique + mon taux de présence.
// Taux = séances où j'étais réellement là / séances passées, non annulées et pointées par un admin.
final class VoirMesPresencesHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{lignes:array,venues:int,pointees:int,taux:?int} */
    public function handle(int $membreId, DateTimeImmutable $now): array
    {
        $stmt = $this->db->prepare(
            'SELECT s.id, s.titre, s.debut, s.statut, p.reponse, p.venu
             FROM presence p JOIN seance s ON s.id = p.seance_id
             WHERE p.membre_id = :m
             ORDER BY s.debut DESC'
        );
        $stmt->execute([':m' => $membreId]);
        $lignes = $stmt->fetchAll();

        $venues = 0;
        $pointees = 0;
        $limite = $now->format('Y-m-d H:i:s');
        foreach ($lignes as $l) {
            if ($l['statut'] === 'prevue' && $l['debut'] < $limite && $l['venu'] !== null) {
                $pointees++;
                $venues += (int)$l['venu'] === 1 ? 1 : 0;
            }
        }
        return [
            'lignes' => $lignes,
            'venues' => $venues,
            'pointees' => $pointees,
            'taux' => $pointees > 0 ? (int)round($venues * 100 / $pointees) : null,
        ];
    }
}
