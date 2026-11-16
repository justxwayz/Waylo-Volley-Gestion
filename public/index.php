<?php
// Front controller : aucune logique métier, il aiguille vers la slice demandée.
// Toute erreur (base indisponible, SQL, bug) est transformée en page lisible ; le détail va dans le journal du serveur.
require __DIR__ . '/../Shared/autoload.php';

try {
    $ctx = new Context(
        Database::fromEnv(),
        Session::native(),
        new DateTimeImmutable('now'),
        $_GET,
        $_POST,
        $_SERVER['REQUEST_METHOD'] ?? 'GET'
    );
    Router::dispatch($ctx, (string)($_GET['p'] ?? 'calendrier'))->send();
} catch (Throwable $e) {
    PageErreur::envoyer($e);
}
