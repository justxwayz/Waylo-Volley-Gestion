<?php
// Infrastructure : connexion PDO. Aucune logique métier.
final class Database
{
    public static function fromEnv(): PDO
    {
        foreach (['DB_HOST', 'DB_NAME', 'DB_USER'] as $cle) {
            if (getenv($cle) === false || getenv($cle) === '') {
                throw new ConfigurationManquante('Variable d\'environnement manquante : ' . $cle);
            }
        }
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;port=%s;charset=utf8mb4',
            getenv('DB_HOST'), getenv('DB_NAME'), getenv('DB_PORT') ?: '3306'
        );
        return new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 3,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            // rowCount() d'un UPDATE = lignes trouvées (et non modifiées) : indispensable pour les "update puis insert"
            PDO::MYSQL_ATTR_FOUND_ROWS => true,
        ]);
    }
}
