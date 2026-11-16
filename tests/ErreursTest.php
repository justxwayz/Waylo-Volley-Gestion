<?php
$c = fn(Throwable $e) => Erreurs::classer($e);

T::check($c(new PDOException('SQLSTATE[HY000] [2002] Connection refused'))['code'] === 503, 'serveur injoignable => 503 "indisponible"');
T::check($c(new PDOException('SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo for mysql failed'))['cause'] === 'connexion', 'hôte inconnu => connexion');
T::check($c(new PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'@'x'"))['cause'] === 'identifiants', 'mauvais mot de passe => identifiants');
T::check($c(new PDOException("SQLSTATE[HY000] [1049] Unknown database 'volley'"))['cause'] === 'base inconnue', 'base inconnue');
$schema = $c(new PDOException("SQLSTATE[42S02]: Base table or view not found: 1146 Table 'volley.evenement' doesn't exist"));
T::check($schema['code'] === 500 && $schema['cause'] === 'schéma' && str_contains($schema['conseil'], 'migrations'), 'table absente => "base non à jour" + conseil migrations');
T::check($c(new PDOException('SQLSTATE[23000]: Integrity constraint violation'))['cause'] === 'sql', 'autre erreur SQL => message générique');
T::check($c(new ConfigurationManquante('DB_HOST'))['cause'] === 'config', 'variable manquante => configuration incomplète');
T::check($c(new LogicException('bug'))['cause'] === 'interne', 'bug quelconque => erreur inattendue');

// masquage du mot de passe
putenv('DB_PASSWORD=Tr3sS3cret!');
T::check(Erreurs::masquer("Access denied, using password 'Tr3sS3cret!'") === "Access denied, using password '***'", 'mot de passe masqué dans les messages');

// page d'erreur : ne fuit rien hors debug, échappe tout
$e = new PDOException("SQLSTATE[HY000] [1045] Access denied for user 'root'@'172.18.0.3' (using password: YES) Tr3sS3cret!");
$p = PageErreur::html($e, false);
T::check($p['code'] === 503 && str_contains($p['html'], 'Accès à la base refusé'), 'page : titre lisible');
T::check(!str_contains($p['html'], 'Tr3sS3cret') && !str_contains($p['html'], 'root') && !str_contains($p['html'], '172.18'), 'page : aucun détail technique hors debug');
$d = PageErreur::html($e, true);
T::check(str_contains($d['html'], 'Détail technique') && !str_contains($d['html'], 'Tr3sS3cret') && str_contains($d['html'], '***'), 'mode debug : détail visible mais mot de passe masqué');
$x = PageErreur::html(new RuntimeException('<script>alert(1)</script>'), true);
T::check(!str_contains($x['html'], '<script>alert') && str_contains($x['html'], '&lt;script&gt;'), 'détail échappé (pas de XSS)');
putenv('DB_PASSWORD');

// Vraie connexion refusée (port fermé) : l'exception réelle de pdo_mysql est bien classée
putenv('DB_HOST=127.0.0.1'); putenv('DB_PORT=1'); putenv('DB_NAME=volley'); putenv('DB_USER=root'); putenv('DB_PASSWORD=secret123');
try {
    Database::fromEnv();
    T::check(false, 'connexion réelle refusée : une exception était attendue');
} catch (Throwable $err) {
    T::check(Erreurs::classer($err)['code'] === 503, 'vraie connexion refusée => 503 (' . Erreurs::classer($err)['cause'] . ')');
}
putenv('DB_HOST');
try { Database::fromEnv(); T::check(false, 'DB_HOST absent : exception attendue'); }
catch (ConfigurationManquante $m) { T::check(str_contains($m->getMessage(), 'DB_HOST'), 'DB_HOST absent => ConfigurationManquante'); }
putenv('DB_PORT'); putenv('DB_NAME'); putenv('DB_USER'); putenv('DB_PASSWORD');

// De bout en bout : le front controller, base injoignable, dans un processus séparé
$racine = dirname(__DIR__);
$cmd = 'cd ' . escapeshellarg($racine . '/public') . ' && DB_HOST=127.0.0.1 DB_PORT=1 DB_NAME=volley DB_USER=root DB_PASSWORD=motdepasseultrasecret php -d error_log=/dev/null index.php 2>&1';
$sortie = (string)shell_exec($cmd);
T::check(str_contains($sortie, 'Base de données indisponible') && str_contains($sortie, 'Réessayer'), 'index.php : base injoignable => page lisible');
T::check(!str_contains($sortie, 'motdepasseultrasecret') && !str_contains($sortie, 'PDOException') && !str_contains($sortie, 'Stack trace'), 'index.php : ni mot de passe, ni trace, ni nom de classe affichés');

// Sans aucune variable d'environnement (autoload à froid : ConfigurationManquante doit être trouvée toute seule)
$cmd = 'cd ' . escapeshellarg($racine . '/public') . ' && env -u DB_HOST -u DB_NAME -u DB_USER -u DB_PASSWORD php -d error_log=/dev/null index.php 2>&1';
$sortie = (string)shell_exec($cmd);
T::check(str_contains($sortie, 'Configuration incomplète') && !str_contains($sortie, 'Fatal error') && !str_contains($sortie, 'not found'), 'index.php sans configuration : page "Configuration incomplète" (pas de fatal)');
