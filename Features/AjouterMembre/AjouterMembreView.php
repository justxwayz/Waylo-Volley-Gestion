<?php
final class AjouterMembreView
{
    public static function form(Context $c, ?string $erreur = null, array $v = []): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $nom = Html::e($v['nom'] ?? '');
        $email = Html::e($v['email'] ?? '');
        $admin = ($v['role'] ?? '') === 'admin' ? ' selected' : '';
        return <<<HTML
$err
<form method="post" action="?p=membre/ajouter" class="card">
  $csrf
  <label>Nom</label><input name="nom" value="$nom" required maxlength="100">
  <label>Email</label><input type="email" name="email" value="$email" required>
  <label>Rôle</label><select name="role"><option value="membre">Membre</option><option value="admin"$admin>Administrateur</option></select>
  <label>Mot de passe temporaire (vide = généré automatiquement)</label><input name="mot_de_passe" autocomplete="off">
  <p><button>Créer le compte</button> <a href="?p=membres">Retour</a></p>
</form>
HTML;
    }
}
