<?php
final class ModifierMonProfilView
{
    public static function form(Context $c, array $membre, ?string $erreur = null): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $nom = Html::e($membre['nom']);
        $email = Html::e($membre['email']);
        return <<<HTML
$err
<form method="post" action="?p=profil" class="card">
  $csrf
  <label>Email (non modifiable)</label><input value="$email" disabled>
  <label>Nom</label><input name="nom" value="$nom" required maxlength="100">
  <h2>Changer de mot de passe (facultatif)</h2>
  <label>Ancien mot de passe</label><input type="password" name="ancien">
  <label>Nouveau mot de passe (8 caractères min.)</label><input type="password" name="nouveau">
  <p><button>Enregistrer</button></p>
</form>
HTML;
    }
}
