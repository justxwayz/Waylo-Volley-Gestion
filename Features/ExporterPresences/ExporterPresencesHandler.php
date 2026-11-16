<?php
// SLICE ExporterPresences (coach + admin) : export CSV des présences.
//   ?seance=12    -> une séance (tous les membres actifs, même sans réponse)
//   ?mois=2026-03 -> toutes les présences d'un mois
//   (rien)        -> tout l'historique
final class ExporterPresencesHandler
{
    private const ENTETES = ['Séance', 'Date', 'Heure', 'Lieu', 'Statut séance', 'Membre', 'Email', 'Réponse', 'Présence réelle'];

    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,csv:?string,nom:?string} */
    public function handle(?int $seanceId, ?string $mois): array
    {
        if ($seanceId !== null) {
            return $this->parSeance($seanceId);
        }
        $debut = null;
        $fin = null;
        if ($mois !== null && $mois !== '') {
            $d = DateTimeImmutable::createFromFormat('!Y-m', $mois);
            if (!$d) {
                return ['ok' => false, 'erreur' => 'Mois invalide (format AAAA-MM).', 'csv' => null, 'nom' => null];
            }
            $debut = $d->format('Y-m-01 00:00:00');
            $fin = $d->modify('first day of next month')->format('Y-m-d 00:00:00');
        }
        $sql = 'SELECT s.titre, s.debut, s.lieu, s.statut, m.nom, m.email, p.reponse, p.venu
                FROM presence p JOIN seance s ON s.id = p.seance_id JOIN membre m ON m.id = p.membre_id'
             . ($debut ? ' WHERE s.debut >= :d AND s.debut < :f' : '') . ' ORDER BY s.debut ASC, m.nom ASC';
        $st = $this->db->prepare($sql);
        $st->execute($debut ? [':d' => $debut, ':f' => $fin] : []);
        return ['ok' => true, 'erreur' => null, 'csv' => Csv::encoder(self::ENTETES, array_map([$this, 'ligne'], $st->fetchAll())),
                'nom' => Csv::nomFichier('presences', $mois ?: 'historique')];
    }

    private function parSeance(int $id): array
    {
        $s = $this->db->prepare('SELECT * FROM seance WHERE id = :id');
        $s->execute([':id' => $id]);
        $seance = $s->fetch();
        if (!$seance) {
            return ['ok' => false, 'erreur' => 'Séance introuvable.', 'csv' => null, 'nom' => null];
        }
        $st = $this->db->prepare(
            'SELECT m.nom, m.email, p.reponse, p.venu FROM membre m
             LEFT JOIN presence p ON p.membre_id = m.id AND p.seance_id = :s
             WHERE m.actif = 1 OR p.id IS NOT NULL ORDER BY m.nom ASC'
        );
        $st->execute([':s' => $id]);
        $lignes = array_map(fn($r) => $this->ligne($r + ['titre' => $seance['titre'], 'debut' => $seance['debut'], 'lieu' => $seance['lieu'], 'statut' => $seance['statut']]), $st->fetchAll());
        return ['ok' => true, 'erreur' => null, 'csv' => Csv::encoder(self::ENTETES, $lignes), 'nom' => Csv::nomFichier('presences-seance', (string)$id)];
    }

    private function ligne(array $r): array
    {
        $venu = $r['venu'] === null ? '' : ((int)$r['venu'] === 1 ? 'Oui' : 'Non');
        return [
            $r['titre'], Html::dateFr($r['debut']), Html::heure($r['debut']), $r['lieu'],
            $r['statut'] === 'annulee' ? 'Annulée' : 'Prévue',
            $r['nom'], $r['email'], $r['reponse'] === null ? 'Pas de réponse' : Regles::libelleReponse($r['reponse']), $venu,
        ];
    }
}
