<?php
$csv = Csv::encoder(['Nom', 'Note'], [['Alice', 'a;b'], ['Bob', 'dit "ok"'], ['=1+1', null], ['-5', "ligne1\nligne2"]]);
T::check(str_starts_with($csv, "\xEF\xBB\xBF"), 'BOM UTF-8 présent (accents corrects dans Excel)');
T::check(str_contains($csv, "Nom;Note\r\n"), 'séparateur ";" et fin de ligne CRLF');
T::check(str_contains($csv, '"a;b"'), 'cellule contenant ";" entre guillemets');
T::check(str_contains($csv, '"dit ""ok"""'), 'guillemets doublés');
T::check(str_contains($csv, "'=1+1;") && str_contains($csv, "'-5;"), 'injection de formule neutralisée (= et -)');
T::check(str_contains($csv, "\"ligne1\nligne2\""), 'saut de ligne dans une cellule entre guillemets');
T::check(Csv::nomFichier('Presences', 'seance 12') === 'presences-seance-12.csv' && Csv::nomFichier('../../x') === 'x.csv', 'nom de fichier assaini (pas de chemin ni de caractères spéciaux)');
$r = Response::fichier('x', 'a"b.csv');
T::check($r->filename === 'a"b.csv' && str_starts_with($r->contentType, 'text/csv'), 'réponse fichier');
