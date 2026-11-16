<?php
// SLICE Connexion : règle métier = identifiants valides ET compte actif.
final class ConnexionHandler
{
    public function __construct(private PDO $db) {}

    /** @return array{ok:bool,membre:?array,erreur:?string} */
    public function handle(string $email, string $motDePasse): array
    {
        $stmt = $this->db->prepare('SELECT * FROM membre WHERE email = :e');
        $stmt->execute([':e' => trim(strtolower($email))]);
        $m = $stmt->fetch();

        if (!$m || !password_verify($motDePasse, $m['mot_de_passe'])) {
            return ['ok' => false, 'membre' => null, 'erreur' => 'Email ou mot de passe incorrect.'];
        }
        if ((int)$m['actif'] !== 1) {
            return ['ok' => false, 'membre' => null, 'erreur' => 'Ce compte est désactivé. Contactez un administrateur.'];
        }
        return ['ok' => true, 'membre' => $m, 'erreur' => null];
    }
}
