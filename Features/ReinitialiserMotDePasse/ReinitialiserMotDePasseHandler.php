<?php
// SLICE ReinitialiserMotDePasse (admin) : nouveau mot de passe temporaire + déblocage du compte
// (remet à zéro la limitation des tentatives de connexion, y compris le blocage définitif).
final class ReinitialiserMotDePasseHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,erreur:?string,mot_de_passe:?string,nom:?string} */
    public function handle(int $membreId): array
    {
        $s = $this->db->prepare('SELECT id, nom, email FROM membre WHERE id = :id');
        $s->execute([':id' => $membreId]);
        $m = $s->fetch();
        if (!$m) {
            return ['ok' => false, 'erreur' => 'Membre introuvable.', 'mot_de_passe' => null, 'nom' => null];
        }
        $mdp = substr(bin2hex(random_bytes(6)), 0, 10);
        $this->db->prepare('UPDATE membre SET mot_de_passe = :p WHERE id = :id')
            ->execute([':p' => password_hash($mdp, PASSWORD_DEFAULT), ':id' => $membreId]);
        $this->db->prepare('DELETE FROM tentative_connexion WHERE email = :e')->execute([':e' => strtolower($m['email'])]);
        return ['ok' => true, 'erreur' => null, 'mot_de_passe' => $mdp, 'nom' => $m['nom']];
    }
}
