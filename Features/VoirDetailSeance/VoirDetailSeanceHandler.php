<?php
// SLICE VoirDetailSeance : fiche d'une séance + ma réponse + compteurs.
final class VoirDetailSeanceHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{seance:array,ma_reponse:?string,compteurs:array<string,int>,ouvert:bool}|null */
    public function handle(int $seanceId, int $membreId, DateTimeImmutable $now): ?array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $seanceId]);
        $seance = $s->fetch();
        if (!$seance) {
            return null;
        }
        $m = $this->db->prepare('SELECT reponse FROM presence WHERE seance_id = :s AND membre_id = :m');
        $m->execute([':s' => $seanceId, ':m' => $membreId]);
        $ma = $m->fetchColumn();

        $c = $this->db->prepare('SELECT reponse, COUNT(*) AS n FROM presence WHERE seance_id = :s AND reponse IS NOT NULL GROUP BY reponse');
        $c->execute([':s' => $seanceId]);
        $compteurs = ['present' => 0, 'absent' => 0, 'peut_etre' => 0];
        foreach ($c->fetchAll() as $row) {
            $compteurs[$row['reponse']] = (int)$row['n'];
        }
        return [
            'seance' => $seance,
            'ma_reponse' => $ma === false ? null : $ma,
            'compteurs' => $compteurs,
            'ouvert' => Regles::reponsesOuvertes($seance, $now),
        ];
    }
}
