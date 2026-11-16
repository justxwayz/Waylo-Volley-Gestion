<?php
// Front controller : aucune logique métier, il aiguille vers la slice demandée.
require __DIR__ . '/../Shared/autoload.php';

try {
    $db = Database::fromEnv();
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Base de données indisponible.';
    exit;
}

$ctx = new Context(
    $db,
    Session::native(),
    new DateTimeImmutable('now'),
    $_GET,
    $_POST,
    $_SERVER['REQUEST_METHOD'] ?? 'GET'
);

$path = $_GET['p'] ?? 'calendrier';
Router::dispatch($ctx, $path)->send();
