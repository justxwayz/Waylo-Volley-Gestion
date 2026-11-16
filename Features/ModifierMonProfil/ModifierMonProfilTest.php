<?php
$db = TestDb::make();
$id = TestDb::membre($db, 'Alice');
$h = new ModifierMonProfilHandler($db);

T::check($h->handle($id, '  ', '', '')['ok'] === false, 'nom vide refusé');
T::check($h->handle($id, 'Alice B.', '', '')['ok'] === true && $h->find($id)['nom'] === 'Alice B.', 'changement de nom');
T::check($h->handle($id, 'Alice', 'faux', 'nouveaumdp1')['ok'] === false, 'ancien mot de passe faux refusé');
T::check($h->handle($id, 'Alice', 'secret123', 'court')['ok'] === false, 'nouveau mot de passe trop court refusé');
T::check($h->handle($id, 'Alice', 'secret123', 'nouveaumdp1')['ok'] === true, 'changement de mot de passe');
$hash = $db->query('SELECT mot_de_passe FROM membre WHERE id = ' . $id)->fetchColumn();
T::check(password_verify('nouveaumdp1', $hash), 'le nouveau mot de passe est bien enregistré (haché)');
