<?php
// Découvre les routes déclarées par chaque slice (Features/*/Route.php).
// Ajouter une fonctionnalité = ajouter un dossier. Aucun fichier central à modifier.
final class Router
{
    /** @return array<string,array{role:string,run:callable}> */
    public static function routes(): array
    {
        $routes = [];
        foreach (glob(dirname(__DIR__) . '/Features/*/Route.php') as $file) {
            foreach (require $file as $path => $def) {
                $routes[$path] = $def;
            }
        }
        return $routes;
    }

    public static function dispatch(Context $c, string $path): Response
    {
        $routes = self::routes();
        if (!isset($routes[$path])) {
            return Response::html(Layout::page($c, 'Page introuvable', '<p>Cette page n\'existe pas.</p>'), 404);
        }
        $def = $routes[$path];
        $u = $c->user();

        if ($def['role'] !== 'public' && !$u) {
            return Response::redirect('?p=connexion');
        }
        if ($def['role'] === 'admin' && $u['role'] !== 'admin') {
            return Response::html(Layout::page($c, 'Accès refusé', '<p>Réservé aux administrateurs.</p>'), 403);
        }
        if ($c->isPost() && !$c->session->checkCsrf($c->post['csrf'] ?? null)) {
            return Response::html(Layout::page($c, 'Requête invalide', '<p>Jeton de sécurité invalide, rechargez la page.</p>'), 400);
        }
        return ($def['run'])($c);
    }
}
