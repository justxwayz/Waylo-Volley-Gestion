<?php
// SLICE PointerPresenceReelle (admin) : le jour J, on coche qui est VRAIMENT venu.
// Règles : séance non annulée ET déjà commencée. Tous les membres actifs sont pointés (venu = 1 ou 0).
final class PointerPresenceReelleHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{seance:array,membres:array}|null  membres : id, nom, reponse, venu */
    public function formulaire(int $seanceId): ?array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $seanceId]);
        $seance = $s->fetch();
        if (!$seance) {
            return null;
        }
        $m = $this->db->prepare(
            'SELECT m.id, m.nom, p.reponse, p.venu
             FROM membre m LEFT JOIN presence p ON p.membre_id = m.id AND p.seance_id = :s
             WHERE m.actif = 1 ORDER BY m.nom ASC'
        );
        $m->execute([':s' => $seanceId]);
        return ['seance' => $seance, 'membres' => $m->fetchAll()];
    }

    /**
     * @param int[] $venusIds membres cochés "venu"
     * @return array{ok:bool,erreur:?string}
     */
    public function handle(int $seanceId, array $venusIds, DateTimeImmutable $now): array
    {
        $f = $this->formulaire($seanceId);
        if (!$f) {
            return ['ok' => false, 'erreur' => 'Séance introuvable.'];
        }
        $seance = $f['seance'];
        if ($seance['statut'] === 'annulee') {
            return ['ok' => false, 'erreur' => 'Impossible de pointer une séance annulée.'];
        }
        if (new DateTimeImmutable($seance['debut']) > $now) {
            return ['ok' => false, 'erreur' => 'La séance n\'a pas encore commencé.'];
        }
        $venus = array_flip(array_map('intval', $venusIds));

        $this->db->beginTransaction();
        foreach ($f['membres'] as $m) {
            $venu = isset($venus[(int)$m['id']]) ? 1 : 0;
            $up = $this->db->prepare('UPDATE presence SET venu = :v WHERE membre_id = :m AND seance_id = :s');
            $up->execute([':v' => $venu, ':m' => $m['id'], ':s' => $seanceId]);
            if ($up->rowCount() === 0) {
                $this->db->prepare('INSERT INTO presence (membre_id, seance_id, reponse, reponse_le, venu) VALUES (:m,:s,NULL,NULL,:v)')
                    ->execute([':m' => $m['id'], ':s' => $seanceId, ':v' => $venu]);
            }
        }
        $this->db->commit();
        return ['ok' => true, 'erreur' => null];
    }
}
