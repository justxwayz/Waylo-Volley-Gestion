<?php
$racine = dirname(__DIR__);

Router::reinitialiser(true);
$idx = Router::index();
T::check(isset($idx['calendrier']) && $idx['calendrier']['file'] === 'Features/VoirCalendrier/Route.php' && $idx['calendrier']['role'] === 'membre', 'index : chemin => fichier de la slice + rôle');

// Invalidation : ajouter un dossier de slice fait apparaître sa route, le supprimer la fait disparaître
$dir = $racine . '/Features/ZzTemporaire';
@mkdir($dir);
file_put_contents($dir . '/Route.php', "<?php return ['zz/temp' => ['role' => 'public', 'run' => fn(Context \$c) => Response::html('ok')]];");
Router::reinitialiser();
T::check(isset(Router::index()['zz/temp']), 'nouvelle slice détectée automatiquement (cache invalidé)');
$db = TestDb::make();
T::check(Router::dispatch(TestDb::ctx($db), 'zz/temp')->body === 'ok', 'la route de la nouvelle slice répond');
unlink($dir . '/Route.php');
rmdir($dir);
Router::reinitialiser();
T::check(!isset(Router::index()['zz/temp']), 'slice supprimée : route disparue');
T::check(Router::dispatch(TestDb::ctx($db), 'zz/temp')->status === 404, 'route inconnue => 404');

// Cache chaud : une requête ne charge qu'UN SEUL Route.php (test dans un processus séparé)
Router::reinitialiser(true);
Router::index();
$script = <<<'PHP'
require $argv[1] . '/Shared/autoload.php';
require $argv[1] . '/tests/bootstrap.php';
$db = TestDb::make();
$r = Router::dispatch(TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'membre'], '2026-03-10 12:00:00', [], ['m' => '2026-03']), 'calendrier');
$charges = array_filter(get_included_files(), fn($f) => str_ends_with($f, '/Route.php'));
echo json_encode([$r->status, count($charges)]);
PHP;
$out = json_decode((string)shell_exec('php -r ' . escapeshellarg($script) . ' ' . escapeshellarg($racine) . ' 2>&1'), true);
T::check($out === [200, 1], 'cache chaud : 1 seul fichier Route.php chargé pour afficher le calendrier (résultat : ' . json_encode($out) . ')');
