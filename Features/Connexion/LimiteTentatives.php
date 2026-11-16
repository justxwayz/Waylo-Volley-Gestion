<?php
// Règle de limitation des tentatives de connexion (propre à la slice Connexion).
//
//  - 5 échecs consécutifs = un palier de blocage
//  - à l'échec n°3 (et 4) : avertissement "il reste N tentative(s) avant blocage"
//  - palier 1 : blocage 1 minute  ; palier 2 : blocage 5 minutes
//  - palier 3 : blocage définitif, seul un admin peut débloquer (réinitialisation du mot de passe)
//  - une connexion réussie remet tout à zéro
//  - suivi par email, même inconnu : on ne révèle pas si un compte existe
final class LimiteTentatives
{
    public const MAX_ECHECS = 5;
    public const AVERTIR_A = 3;
    /** secondes de blocage par palier (le palier 3 est définitif) */
    public const BLOCAGES = [1 => 60, 2 => 300];
    public const PALIER_DEFINITIF = 3;

    public function __construct(private PDO $db) {}

    /** @return array{niveau:int,echecs:int,bloque_jusqu:?string} */
    public function etat(string $email): array
    {
        $s = $this->db->prepare('SELECT echecs, niveau, bloque_jusqu FROM tentative_connexion WHERE email = :e');
        $s->execute([':e' => $email]);
        $r = $s->fetch();
        return $r ? ['niveau' => (int)$r['niveau'], 'echecs' => (int)$r['echecs'], 'bloque_jusqu' => $r['bloque_jusqu']]
                  : ['niveau' => 0, 'echecs' => 0, 'bloque_jusqu' => null];
    }

    /** Message de blocage si l'email est bloqué à cet instant, sinon null. */
    public function messageSiBloque(string $email, DateTimeImmutable $now): ?string
    {
        $e = $this->etat($email);
        if ($e['niveau'] >= self::PALIER_DEFINITIF) {
            return 'Trop de tentatives, contactez un admin.';
        }
        if ($e['bloque_jusqu'] !== null && new DateTimeImmutable($e['bloque_jusqu']) > $now) {
            $reste = (new DateTimeImmutable($e['bloque_jusqu']))->getTimestamp() - $now->getTimestamp();
            return 'Trop de tentatives. Réessayez dans ' . self::duree($reste) . '.';
        }
        return null;
    }

    /**
     * Enregistre un échec.
     * @return array{message:?string,bloque:bool,avertissement:?string}
     */
    public function enregistrerEchec(string $email, DateTimeImmutable $now): array
    {
        $e = $this->etat($email);
        $echecs = $e['echecs'] + 1;
        $niveau = $e['niveau'];
        $bloqueJusqu = null;
        $message = null;
        $avertissement = null;
        $bloque = false;

        if ($echecs >= self::MAX_ECHECS) {
            $niveau++;
            $echecs = 0;
            $bloque = true;
            if ($niveau >= self::PALIER_DEFINITIF) {
                $message = 'Trop de tentatives, contactez un admin.';
            } else {
                $secondes = self::BLOCAGES[$niveau];
                $bloqueJusqu = $now->modify("+$secondes seconds")->format('Y-m-d H:i:s');
                $message = 'Trop de tentatives. Compte bloqué pendant ' . self::duree($secondes) . '.';
            }
        } elseif ($echecs >= self::AVERTIR_A) {
            $reste = self::MAX_ECHECS - $echecs;
            $avertissement = 'Attention : il vous reste ' . $reste . ' tentative' . ($reste > 1 ? 's' : '') . ' avant blocage.';
        }

        $existe = $this->db->prepare('UPDATE tentative_connexion SET echecs = :ec, niveau = :n, bloque_jusqu = :b, maj_le = :m WHERE email = :e');
        $existe->execute([':ec' => $echecs, ':n' => $niveau, ':b' => $bloqueJusqu, ':m' => $now->format('Y-m-d H:i:s'), ':e' => $email]);
        if ($existe->rowCount() === 0) {
            $this->db->prepare('INSERT INTO tentative_connexion (email, echecs, niveau, bloque_jusqu, maj_le) VALUES (:e,:ec,:n,:b,:m)')
                ->execute([':e' => $email, ':ec' => $echecs, ':n' => $niveau, ':b' => $bloqueJusqu, ':m' => $now->format('Y-m-d H:i:s')]);
        }
        return ['message' => $message, 'bloque' => $bloque, 'avertissement' => $avertissement];
    }

    public function remettreAZero(string $email): void
    {
        $this->db->prepare('DELETE FROM tentative_connexion WHERE email = :e')->execute([':e' => $email]);
    }

    private static function duree(int $secondes): string
    {
        if ($secondes < 60) {
            return $secondes . ' seconde' . ($secondes > 1 ? 's' : '');
        }
        $m = (int)ceil($secondes / 60);
        return $m . ' minute' . ($m > 1 ? 's' : '');
    }
}
