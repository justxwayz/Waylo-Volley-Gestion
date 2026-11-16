<?php
$ok = SeanceDonnees::valider(' Match ', '2026-03-12T20:00', '2026-03-12T22:00', ' Salle ');
T::check($ok['ok'] && $ok['titre'] === 'Match' && $ok['lieu'] === 'Salle' && $ok['debut'] === '2026-03-12 20:00:00', 'validation : valeurs nettoyées et dates normalisées');
T::check(SeanceDonnees::valider('', '2026-03-12T20:00', '2026-03-12T22:00', 'S')['ok'] === false, 'titre vide refusé');
T::check(SeanceDonnees::valider('T', '2026-03-12T22:00', '2026-03-12T20:00', 'S')['ok'] === false, 'fin avant début refusée');
T::check(SeanceDonnees::valider('T', 'zzz', '2026-03-12T20:00', 'S')['ok'] === false, 'date invalide refusée');
T::check(SeanceDonnees::valider(str_repeat('x', 151), '2026-03-12T20:00', '2026-03-12T22:00', 'S')['ok'] === false, 'titre de plus de 150 caractères refusé');
T::check(SeanceDonnees::pourInput('2026-03-12 20:00:00') === '2026-03-12T20:00' && SeanceDonnees::pourInput(null) === '', 'conversion vers input datetime-local');

$db = TestDb::make();
$ctx = TestDb::ctx($db, ['id' => 1, 'nom' => 'A', 'role' => 'admin']);
$html = SeanceFormulaire::render($ctx, '?p=x', 'Go', ['titre' => '<b>', 'debut' => '2026-03-12 20:00:00'], 'Oups', '?p=retour');
T::check(str_contains($html, '&lt;b&gt;') && str_contains($html, 'value="2026-03-12T20:00"') && str_contains($html, 'Oups') && str_contains($html, '?p=retour'), 'formulaire : échappement, valeurs, erreur, lien annuler');
