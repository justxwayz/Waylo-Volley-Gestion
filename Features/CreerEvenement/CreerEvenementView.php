<?php
final class CreerEvenementView
{
    public static function form(Context $c, ?string $erreur = null, array $v = []): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $titre = Html::e($v['titre'] ?? '');
        $desc = Html::e($v['description'] ?? '');
        $debut = Html::e(SeanceDonnees::pourInput($v['debut'] ?? null));
        $fin = Html::e(SeanceDonnees::pourInput($v['fin'] ?? null));
        $limite = Html::e(SeanceDonnees::pourInput($v['date_limite'] ?? null));
        $lieu = Html::e($v['lieu'] ?? '');
        return <<<HTML
$err
<form method="post" action="?p=evenement/creer" class="card">
  $csrf
  <label>Titre</label><input name="titre" value="$titre" required maxlength="150">
  <label>Description</label><textarea name="description" rows="4" style="width:100%" maxlength="2000">$desc</textarea>
  <label>Début</label><input type="datetime-local" name="debut" value="$debut" required>
  <label>Fin</label><input type="datetime-local" name="fin" value="$fin" required>
  <label>Lieu</label><input name="lieu" value="$lieu" required maxlength="150">
  <label>Date limite d'inscription</label><input type="datetime-local" name="date_limite" value="$limite" required>
  <p><button>Créer l'événement</button></p>
</form>
HTML;
    }
}
