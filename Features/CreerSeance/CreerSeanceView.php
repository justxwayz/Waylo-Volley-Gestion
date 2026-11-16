<?php
final class CreerSeanceView
{
    public static function form(Context $c, ?string $erreur = null, array $v = []): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $titre = Html::e($v['titre'] ?? 'Entrainement');
        $debut = Html::e($v['debut'] ?? '');
        $fin = Html::e($v['fin'] ?? '');
        $lieu = Html::e($v['lieu'] ?? '');
        return <<<HTML
$err
<form method="post" action="?p=seance/creer" class="card">
  $csrf
  <label>Titre</label><input name="titre" value="$titre" required maxlength="150">
  <label>Début</label><input type="datetime-local" name="debut" value="$debut" required>
  <label>Fin</label><input type="datetime-local" name="fin" value="$fin" required>
  <label>Lieu</label><input name="lieu" value="$lieu" required maxlength="150">
  <p><button>Créer la séance</button></p>
</form>
HTML;
    }
}
