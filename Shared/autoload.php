<?php
// Chargement automatique : Shared/<Classe>.php ou Features/<Slice>/<Classe>.php
spl_autoload_register(function (string $class): void {
    static $map = null;
    if ($map === null) {
        $map = [];
        $root = dirname(__DIR__);
        foreach (glob($root . '/Shared/*.php') as $f) {
            $map[basename($f, '.php')] = $f;
        }
        foreach (glob($root . '/Features/*/*.php') as $f) {
            $name = basename($f, '.php');
            if (!str_ends_with($name, 'Test') && $name !== 'Route') {
                $map[$name] = $f;
            }
        }
    }
    if (isset($map[$class])) {
        require_once $map[$class];
    }
});
