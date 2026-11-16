<?php
// Découvre les routes déclarées par chaque slice (Features/*/Route.php).
// Ajouter une fonctionnalité = ajouter un dossier. Aucun fichier central à modifier.
//
// Cache : un index "chemin => fichier Route.php + rôle + menu" est mémorisé sur disque.
// Une requête ne charge ensuite QUE le Route.php de la slice demandée (au lieu de tous).
// L'index se reconstruit seul dès qu'un Route.php est ajouté, supprimé ou modifié (signature des mtime).
final class Router
{
    /** @var array<string,array{file:string,role:string,menu:?array}>|null */
    private static ?array $index = null;

    /** @return string[] chemins relatifs des Route.php (triés) */
    private static function fichiers(): array
    {
        $racine = dirname(__DIR__);
        $liste = glob($racine . '/Features/*/Route.php') ?: [];
        sort($liste);
        return array_map(fn($f) => substr($f, strlen($racine) + 1), $liste);
    }

    private static function signature(array $fichiers): string
    {
        $racine = dirname(__DIR__);
        $s = '';
        foreach ($fichiers as $f) {
            $s .= $f . ':' . filemtime($racine . '/' . $f) . ';';
        }
        return md5($s);
    }

    private static function fichierCache(): ?string
    {
        foreach ([dirname(__DIR__) . '/var/cache', sys_get_temp_dir() . '/waylo-volley-cache'] as $dir) {
            if ((is_dir($dir) || @mkdir($dir, 0775, true)) && is_writable($dir)) {
                return $dir . '/routes.php';
            }
        }
        return null;
    }

    /** @return array<string,array{file:string,role:string,menu:?array}> */
    public static function index(): array
    {
        if (self::$index !== null) {
            return self::$index;
        }
        $fichiers = self::fichiers();
        $signature = self::signature($fichiers);
        $cache = self::fichierCache();

        if ($cache && is_file($cache)) {
            $data = @include $cache;
            if (is_array($data) && ($data['signature'] ?? null) === $signature) {
                return self::$index = $data['routes'];
            }
        }

        $routes = [];
        foreach ($fichiers as $f) {
            foreach (require dirname(__DIR__) . '/' . $f as $path => $def) {
                $routes[$path] = ['file' => $f, 'role' => $def['role'], 'menu' => $def['menu'] ?? null];
            }
        }
        if ($cache) {
            $tmp = $cache . '.' . getmypid() . '.tmp';
            if (@file_put_contents($tmp, '<?php return ' . var_export(['signature' => $signature, 'routes' => $routes], true) . ';')) {
                @rename($tmp, $cache);
            }
        }
        return self::$index = $routes;
    }

    /** Vide le cache mémoire et le fichier (tests, déploiement). */
    public static function reinitialiser(bool $fichier = false): void
    {
        self::$index = null;
        if ($fichier && ($c = self::fichierCache()) && is_file($c)) {
            @unlink($c);
        }
    }

    /** Définition complète (avec la fonction `run`) d'une route : charge uniquement le fichier de sa slice. */
    private static function definition(string $path): ?array
    {
        $index = self::index();
        if (!isset($index[$path])) {
            return null;
        }
        return (require dirname(__DIR__) . '/' . $index[$path]['file'])[$path] ?? null;
    }

    /** Toutes les définitions (charge tous les fichiers) : utile aux tests. */
    public static function routes(): array
    {
        $routes = [];
        foreach (self::fichiers() as $f) {
            foreach (require dirname(__DIR__) . '/' . $f as $path => $def) {
                $routes[$path] = $def;
            }
        }
        return $routes;
    }

    public static function dispatch(Context $c, string $path): Response
    {
        $def = self::definition($path);
        if (!$def) {
            return Response::html(Layout::page($c, 'Page introuvable', '<p>Cette page n\'existe pas.</p>'), 404);
        }
        $u = $c->user();

        if ($def['role'] !== 'public' && !$u) {
            return Response::redirect('?p=connexion');
        }
        if (!Roles::atteint($u['role'] ?? null, $def['role'])) {
            return Response::html(Layout::page($c, 'Accès refusé', '<p>Vous n\'avez pas les droits nécessaires pour cette page.</p>'), 403);
        }
        if ($c->isPost() && !$c->session->checkCsrf($c->post['csrf'] ?? null)) {
            return Response::html(Layout::page($c, 'Requête invalide', '<p>Jeton de sécurité invalide, rechargez la page.</p>'), 400);
        }
        return ($def['run'])($c);
    }
}
