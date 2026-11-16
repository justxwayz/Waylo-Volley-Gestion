<?php
// Aide commune aux tests : base SQLite en mémoire avec le même schéma (version portable).
require_once __DIR__ . '/../Shared/autoload.php';

final class TestDb
{
    public static function make(): PDO
    {
        $db = new PDO('sqlite::memory:');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $db->exec("CREATE TABLE membre (id INTEGER PRIMARY KEY AUTOINCREMENT, nom TEXT, email TEXT UNIQUE, mot_de_passe TEXT, role TEXT DEFAULT 'membre', actif INTEGER DEFAULT 1, cree_le TEXT)");
        $db->exec("CREATE TABLE seance (id INTEGER PRIMARY KEY AUTOINCREMENT, titre TEXT, debut TEXT, fin TEXT, lieu TEXT, statut TEXT DEFAULT 'prevue', cree_le TEXT)");
        $db->exec("CREATE TABLE presence (id INTEGER PRIMARY KEY AUTOINCREMENT, membre_id INTEGER, seance_id INTEGER, reponse TEXT, reponse_le TEXT, venu INTEGER NULL, UNIQUE(membre_id, seance_id))");
        $db->exec("CREATE TABLE evenement (id INTEGER PRIMARY KEY AUTOINCREMENT, titre TEXT, description TEXT NULL, debut TEXT, fin TEXT, lieu TEXT, date_limite TEXT, statut TEXT DEFAULT 'prevu', cree_le TEXT)");
        $db->exec('CREATE TABLE inscription_evenement (id INTEGER PRIMARY KEY AUTOINCREMENT, membre_id INTEGER, evenement_id INTEGER, inscrit_le TEXT, UNIQUE(membre_id, evenement_id))');
        $db->exec('CREATE TABLE tentative_connexion (email TEXT PRIMARY KEY, echecs INTEGER DEFAULT 0, niveau INTEGER DEFAULT 0, bloque_jusqu TEXT NULL, maj_le TEXT)');
        return $db;
    }

    public static function membre(PDO $db, string $nom, string $role = 'membre', int $actif = 1, string $mdp = 'secret123'): int
    {
        $email = strtolower($nom) . '@test.local';
        $db->prepare('INSERT INTO membre (nom,email,mot_de_passe,role,actif,cree_le) VALUES (?,?,?,?,?,?)')
           ->execute([$nom, $email, password_hash($mdp, PASSWORD_DEFAULT), $role, $actif, '2026-01-01 00:00:00']);
        return (int)$db->lastInsertId();
    }

    public static function seance(PDO $db, string $debut, string $statut = 'prevue', string $titre = 'Entrainement'): int
    {
        $fin = (new DateTimeImmutable($debut))->modify('+2 hours')->format('Y-m-d H:i:s');
        $db->prepare('INSERT INTO seance (titre,debut,fin,lieu,statut,cree_le) VALUES (?,?,?,?,?,?)')
           ->execute([$titre, $debut, $fin, 'Gymnase', $statut, '2026-01-01 00:00:00']);
        return (int)$db->lastInsertId();
    }

    public static function evenement(PDO $db, string $debut, string $limite, string $statut = 'prevu', string $titre = 'Tournoi'): int
    {
        $fin = (new DateTimeImmutable($debut))->modify('+4 hours')->format('Y-m-d H:i:s');
        $db->prepare('INSERT INTO evenement (titre,description,debut,fin,lieu,date_limite,statut,cree_le) VALUES (?,?,?,?,?,?,?,?)')
           ->execute([$titre, 'desc', $debut, $fin, 'Salle', $limite, $statut, '2026-01-01 00:00:00']);
        return (int)$db->lastInsertId();
    }

    public static function ctx(PDO $db, ?array $user = null, string $now = '2026-03-10 12:00:00', array $post = [], array $get = []): Context
    {
        $s = Session::memory();
        if ($user) {
            $s->login($user);
        }
        return new Context($db, $s, new DateTimeImmutable($now), $get, $post, $post ? 'POST' : 'GET');
    }
}

final class T
{
    public static int $fail = 0;
    public static int $pass = 0;

    public static function check(bool $ok, string $label): void
    {
        $ok ? self::$pass++ : self::$fail++;
        echo ($ok ? '  OK   ' : '  FAIL ') . $label . "\n";
    }
}
