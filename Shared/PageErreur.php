<?php
// Page d'erreur autonome : n'a besoin ni de la base, ni de la session (elle sert justement quand elles sont en panne).
final class PageErreur
{
    public static function html(Throwable $e, bool $debug = false): array
    {
        $r = Erreurs::classer($e);
        $detail = '';
        if ($debug) {
            $detail = '<details><summary>Détail technique (APP_DEBUG=1)</summary><pre>'
                    . htmlspecialchars(Erreurs::masquer(get_class($e) . ': ' . $e->getMessage()), ENT_QUOTES, 'UTF-8') . '</pre></details>';
        }
        $conseil = $r['conseil'] ? '<p class="muted">' . htmlspecialchars($r['conseil'], ENT_QUOTES, 'UTF-8') . '</p>' : '';
        $titre = htmlspecialchars($r['titre'], ENT_QUOTES, 'UTF-8');
        $message = htmlspecialchars($r['message'], ENT_QUOTES, 'UTF-8');
        $page = <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$titre - Waylo Volley</title>
<style>body{font-family:system-ui,sans-serif;background:#0C111F;color:#e8edf5;margin:0}main{max-width:640px;margin:12vh auto;padding:0 1rem}.carte{background:#20293A;border-radius:12px;padding:1.5rem}h1{margin-top:0}.muted{color:#9aa7bd}a{color:#3eadf2}pre{white-space:pre-wrap;background:#141b2c;padding:.6rem;border-radius:8px}</style>
</head>
<body><main><div class="carte" role="alert">
<h1>$titre</h1>
<p>$message</p>
$conseil
<p><a href="">Réessayer</a></p>
$detail
</div></main></body>
</html>
HTML;
        return ['code' => $r['code'], 'html' => $page];
    }

    public static function envoyer(Throwable $e): void
    {
        Erreurs::journaliser($e);
        $p = self::html($e, getenv('APP_DEBUG') === '1');
        if (!headers_sent()) {
            http_response_code($p['code']);
            header('Content-Type: text/html; charset=utf-8');
            if ($p['code'] === 503) {
                header('Retry-After: 10');
            }
        }
        echo $p['html'];
    }
}
