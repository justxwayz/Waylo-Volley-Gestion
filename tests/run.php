<?php
// Lance tous les tests de slices : php tests/run.php
require __DIR__ . '/bootstrap.php';

foreach (array_merge(glob(__DIR__ . '/../Features/*/*Test.php'), glob(__DIR__ . '/*Test.php')) as $file) {
    echo basename(dirname($file)) . ' / ' . basename($file) . "\n";
    require $file;
}
echo "\n" . T::$pass . " OK, " . T::$fail . " en échec\n";
exit(T::$fail ? 1 : 0);
