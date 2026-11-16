<?php
// Formulaire HTML d'une séance, commun à CreerSeance et ModifierSeance.
final class SeanceFormulaire
{
    /**
     * @param array $valeurs titre, debut, fin, lieu (debut/fin au format datetime-local ou SQL)
     * @param string $lienAnnuler  si fourni, ajoute un lien "Annuler" à côté du bouton
     */
    public static function render(Context $c, string $action, string $bouton, array $valeurs = [], ?string $erreur = null, ?string $lienAnnuler = null): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $titre = Html::e($valeurs['titre'] ?? 'Entrainement');
        $debut = Html::e(SeanceDonnees::pourInput($valeurs['debut'] ?? null));
        $fin = Html::e(SeanceDonnees::pourInput($valeurs['fin'] ?? null));
        $lieu = Html::e($valeurs['lieu'] ?? '');
        $act = Html::e($action);
        $btn = Html::e($bouton);
        $lien = $lienAnnuler ? ' <a href="' . Html::e($lienAnnuler) . '">Annuler</a>' : '';
        return <<<HTML
$err
<form method="post" action="$act" class="card">
  $csrf
  <label>Titre</label><input name="titre" value="$titre" required maxlength="150">
  <label>Début</label><input type="datetime-local" name="debut" value="$debut" required>
  <label>Fin</label><input type="datetime-local" name="fin" value="$fin" required>
  <label>Lieu</label><input name="lieu" value="$lieu" required maxlength="150">
  <p><button>$btn</button>$lien</p>
</form>
HTML;
    }
}
