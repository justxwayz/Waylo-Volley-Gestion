<?php
// Transforme une exception technique en erreur lisible (code HTTP, titre, message, conseil).
// Aucun détail technique (hôte, utilisateur, mot de passe, requête SQL) n'est montré hors mode debug.
final class Erreurs
{
    /** @return array{code:int,titre:string,message:string,conseil:?string,cause:string} */
    public static function classer(Throwable $e): array
    {
        $msg = $e->getMessage();

        if ($e instanceof ConfigurationManquante) {
            return self::r(503, 'Configuration incomplète', 'L\'application n\'est pas configurée pour accéder à la base de données.',
                'Vérifiez les variables DB_HOST, DB_NAME, DB_USER, DB_PASSWORD (voir docker-compose.yml).', 'config');
        }
        if ($e instanceof PDOException) {
            // Connexion impossible : serveur arrêté ou pas prêt (2002/2003/2006/2013), identifiants (1045/1044), base inconnue (1049)
            if (preg_match('/\[(2002|2003|2006|2013)\]|server has gone away|Connection refused|Name or service not known|getaddrinfo/i', $msg)) {
                return self::r(503, 'Base de données indisponible', 'Le service de base de données ne répond pas pour le moment. Réessayez dans quelques instants.',
                    'Si vous venez de démarrer l\'application, MySQL peut mettre jusqu\'à une minute : « docker compose ps » doit afficher « healthy ».', 'connexion');
            }
            if (preg_match('/\[(1045|1044)\]|Access denied/i', $msg)) {
                return self::r(503, 'Accès à la base refusé', 'Les identifiants de la base de données sont refusés.',
                    'Vérifiez DB_USER et DB_PASSWORD (fichier .env ou docker-compose.yml).', 'identifiants');
            }
            if (preg_match('/\[1049\]|Unknown database/i', $msg)) {
                return self::r(503, 'Base de données introuvable', 'La base de données demandée n\'existe pas.',
                    'Recréez-la : « docker compose down -v » puis « docker compose up -d --build ».', 'base inconnue');
            }
            if (preg_match('/42S02|42S22|Base table or view not found|Unknown column/i', $msg)) {
                return self::r(500, 'Base de données non à jour', 'La structure de la base ne correspond pas à cette version de l\'application.',
                    'Appliquez les scripts de db/migrations/ (ou « docker compose down -v » pour repartir de zéro).', 'schéma');
            }
            return self::r(500, 'Erreur de base de données', 'Une erreur est survenue lors de l\'accès aux données.', null, 'sql');
        }
        return self::r(500, 'Erreur inattendue', 'Une erreur inattendue est survenue.', null, 'interne');
    }

    /** Écrit le détail complet dans le journal du serveur (jamais à l'écran), mot de passe masqué. */
    public static function journaliser(Throwable $e): void
    {
        error_log('[waylo-volley] ' . get_class($e) . ' : ' . self::masquer($e->getMessage()) . ' @ ' . basename($e->getFile()) . ':' . $e->getLine());
    }

    /** Masque les secrets connus (mot de passe de la base) dans un texte. */
    public static function masquer(string $texte): string
    {
        foreach (['DB_PASSWORD'] as $cle) {
            $v = getenv($cle);
            if ($v !== false && $v !== '') {
                $texte = str_replace($v, '***', $texte);
            }
        }
        return $texte;
    }

    private static function r(int $code, string $titre, string $message, ?string $conseil, string $cause): array
    {
        return ['code' => $code, 'titre' => $titre, 'message' => $message, 'conseil' => $conseil, 'cause' => $cause];
    }
}
