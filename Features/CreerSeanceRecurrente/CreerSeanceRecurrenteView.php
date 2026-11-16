<?php
final class CreerSeanceRecurrenteView
{
    public static function form(Context $c, ?string $erreur = null, array $v = []): string
    {
        $err = $erreur ? '<p class="flash ko">' . Html::e($erreur) . '</p>' : '';
        $csrf = Html::csrfField($c->session);
        $coches = array_map('intval', (array)($v['jours'] ?? [2, 4]));
        $jours = '';
        foreach (CreerSeanceRecurrenteHandler::JOURS as $n => $nom) {
            $jours .= '<label style="display:inline-block;margin-right:1rem"><input type="checkbox" name="jours[]" value="' . $n . '"'
                    . (in_array($n, $coches, true) ? ' checked' : '') . '> ' . $nom . '</label>';
        }
        $titre = Html::e($v['titre'] ?? 'Entrainement');
        $lieu = Html::e($v['lieu'] ?? '');
        $hd = Html::e($v['heure_debut'] ?? '20:00');
        $hf = Html::e($v['heure_fin'] ?? '22:00');
        $dd = Html::e($v['date_debut'] ?? '');
        $df = Html::e($v['date_fin'] ?? '');
        $max = CreerSeanceRecurrenteHandler::MAX_SEANCES;
        return <<<HTML
$err
<form method="post" action="?p=seance/recurrente" class="card">
  $csrf
  <label>Titre</label><input name="titre" value="$titre" required maxlength="150">
  <label>Lieu</label><input name="lieu" value="$lieu" required maxlength="150">
  <label>Jours de la semaine</label><div>$jours</div>
  <label>Heure de début</label><input type="time" name="heure_debut" value="$hd" required>
  <label>Heure de fin</label><input type="time" name="heure_fin" value="$hf" required>
  <label>Du</label><input type="date" name="date_debut" value="$dd" required>
  <label>Au (inclus)</label><input type="date" name="date_fin" value="$df" required>
  <p class="muted">Maximum $max séances d'un coup. Les séances déjà existantes (même titre, même début) sont ignorées.</p>
  <p><button>Générer les séances</button></p>
</form>
HTML;
    }
}
