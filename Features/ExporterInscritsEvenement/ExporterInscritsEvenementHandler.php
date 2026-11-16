<?php
// SLICE ExporterInscritsEvenement (coach + admin) : inscrits d'un événement en CSV.
final class ExporterInscritsEvenementHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,csv:?string,nom:?string} */
    public function handle(int $evenementId): array
    {
        $s = $this->db->prepare('SELECT * FROM evenement WHERE id = :id');
        $s->execute([':id' => $evenementId]);
        $e = $s->fetch();
        if (!$e) {
            return ['ok' => false, 'erreur' => 'Événement introuvable.', 'csv' => null, 'nom' => null];
        }
        $i = $this->db->prepare(
            'SELECT m.nom, m.email, i.inscrit_le FROM inscription_evenement i JOIN membre m ON m.id = i.membre_id
             WHERE i.evenement_id = :id ORDER BY m.nom ASC'
        );
        $i->execute([':id' => $evenementId]);
        $lignes = array_map(
            fn($r) => [$e['titre'], Html::dateFr($e['debut']), $e['statut'] === 'annule' ? 'Annulé' : 'Prévu', $r['nom'], $r['email'], Html::dateFr($r['inscrit_le'])],
            $i->fetchAll()
        );
        return [
            'ok' => true, 'erreur' => null, 'nom' => Csv::nomFichier('inscrits-evenement', (string)$evenementId),
            'csv' => Csv::encoder(['Événement', 'Date', 'Statut', 'Membre', 'Email', 'Inscrit le'], $lignes),
        ];
    }
}
