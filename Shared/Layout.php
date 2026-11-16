<?php
// Coquille HTML commune. Le contenu vient de chaque slice.
final class Layout
{
    public static function page(Context $c, string $titre, string $contenu): string
    {
        $nav = Menu::html($c);
        $flash = '';
        foreach ($c->session->takeFlash() as $f) {
            $flash .= '<p class="flash ' . Html::e($f['t']) . '">' . Html::e($f['m']) . '</p>';
        }
        $t = Html::e($titre);
        return <<<HTML
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>$t - Waylo Volley</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header><strong>Waylo Volley</strong><nav>$nav</nav></header>
<main>
$flash
<h1>$t</h1>
$contenu
</main>
</body>
</html>
HTML;
    }
}
