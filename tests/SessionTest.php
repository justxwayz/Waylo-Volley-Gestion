<?php
$s = Session::memory();
$avant = $s->csrf();
$s->login(['id' => 1, 'nom' => 'A', 'role' => 'membre']);
T::check($s->csrf() !== $avant, 'le jeton CSRF change à la connexion');
T::check($s->user()['id'] === 1, 'utilisateur enregistré');
$s->logout();
T::check($s->user() === null, 'déconnexion : session vidée');

// Session native : à tester dans un processus séparé (une session ne peut pas démarrer après un affichage).
$script = <<<'PHP'
require $argv[1] . '/Shared/autoload.php';
ini_set('session.use_cookies', '0');
ini_set('session.use_only_cookies', '0');
ini_set('session.cache_limiter', '');
ini_set('session.save_path', sys_get_temp_dir());
session_id('identifiantavantconnexion123');
$n = Session::native();
$avant = session_id();
$n->login(['id' => 2, 'nom' => 'B', 'role' => 'membre']);
$apres = session_id();
$n->logout();
$fin = session_id();
echo json_encode([$avant !== $apres, $apres !== $fin, $apres !== '']);
PHP;
$out = shell_exec('php -r ' . escapeshellarg($script) . ' ' . escapeshellarg(dirname(__DIR__)) . ' 2>&1');
$r = json_decode((string)$out, true);
T::check($r === [true, true, true], 'native : identifiant régénéré à la connexion et à la déconnexion' . ($r ? '' : ' (sortie : ' . trim((string)$out) . ')'));
