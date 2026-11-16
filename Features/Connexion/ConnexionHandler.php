<?php
// SLICE Connexion : identifiants valides, compte actif, et limitation des tentatives (voir LimiteTentatives).
final class ConnexionHandler
{
    private LimiteTentatives $limite;

    public function __construct(private PDO $db)
    {
        $this->limite = new LimiteTentatives($db);
    }

    /** @return array{ok:bool,membre:?array,erreur:?string,avertissement:?string,bloque:bool} */
    public function handle(string $email, string $motDePasse, DateTimeImmutable $now): array
    {
        $email = trim(strtolower($email));

        // 1. déjà bloqué ? (même avec le bon mot de passe)
        if ($msg = $this->limite->messageSiBloque($email, $now)) {
            return self::ko($msg, bloque: true);
        }

        // 2. vérification des identifiants
        $stmt = $this->db->prepare('SELECT * FROM membre WHERE email = :e');
        $stmt->execute([':e' => $email]);
        $m = $stmt->fetch();

        if (!$m || !password_verify($motDePasse, $m['mot_de_passe'])) {
            $echec = $this->limite->enregistrerEchec($email, $now);
            if ($echec['bloque']) {
                return self::ko($echec['message'], bloque: true);
            }
            return self::ko('Email ou mot de passe incorrect.', $echec['avertissement']);
        }

        // 3. identifiants OK mais compte désactivé : pas un échec de saisie, on ne compte pas
        if ((int)$m['actif'] !== 1) {
            return self::ko('Ce compte est désactivé. Contactez un administrateur.');
        }

        $this->limite->remettreAZero($email);
        return ['ok' => true, 'membre' => $m, 'erreur' => null, 'avertissement' => null, 'bloque' => false];
    }

    private static function ko(string $erreur, ?string $avertissement = null, bool $bloque = false): array
    {
        return ['ok' => false, 'membre' => null, 'erreur' => $erreur, 'avertissement' => $avertissement, 'bloque' => $bloque];
    }
}
