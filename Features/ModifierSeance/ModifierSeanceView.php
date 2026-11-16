<?php
final class ModifierSeanceView
{
    public static function form(Context $c, array $s, ?string $erreur = null): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $id = (int)$s['id'];
        $titre = Html::e($s['titre']);
        $lieu = Html::e($s['lieu']);
        $debut = Html::e(str_replace(' ', 'T', substr((string)$s['debut'], 0, 16)));
        $fin = Html::e(str_replace(' ', 'T', substr((string)$s['fin'], 0, 16)));
        return <<<HTML
$err
<form method="post" action="?p=seance/modifier&id=$id" class="card">
  $csrf
  <label>Titre</label><input name="titre" value="$titre" required maxlength="150">
  <label>Début</label><input type="datetime-local" name="debut" value="$debut" required>
  <label>Fin</label><input type="datetime-local" name="fin" value="$fin" required>
  <label>Lieu</label><input name="lieu" value="$lieu" required maxlength="150">
  <p><button>Enregistrer</button> <a href="?p=seance&id=$id">Annuler</a></p>
</form>
HTML;
    }
}
