<?php
final class ConnexionView
{
    public static function form(Context $c, ?string $erreur, string $email = '', ?string $avertissement = null): string
    {
        $err = $erreur ? '<p class="flash ko" role="alert">' . Html::e($erreur) . '</p>' : '';
        if ($avertissement) {
            $err .= '<p class="flash alerte" role="alert">' . Html::e($avertissement) . '</p>';
        }
        $csrf = Html::csrfField($c->session);
        $e = Html::e($email);
        return <<<HTML
$err
<form method="post" action="?p=connexion" class="card">
  $csrf
  <label>Email</label><input type="email" name="email" value="$e" required autofocus>
  <label>Mot de passe</label><input type="password" name="mot_de_passe" required>
  <p><button>Se connecter</button></p>
</form>
HTML;
    }
}
