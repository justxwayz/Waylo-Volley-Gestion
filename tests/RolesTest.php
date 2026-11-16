<?php
T::check(Roles::atteint('admin', 'coach') && Roles::atteint('admin', 'membre'), 'admin hérite des droits coach et membre');
T::check(Roles::atteint('coach', 'membre') && !Roles::atteint('coach', 'admin'), 'coach : droits membre, pas admin');
T::check(!Roles::atteint('membre', 'coach') && Roles::atteint('membre', 'membre'), 'membre : pas de droits coach');
T::check(!Roles::atteint(null, 'membre') && Roles::atteint(null, 'public'), 'visiteur : uniquement les pages publiques');
T::check(!Roles::atteint('inconnu', 'membre'), 'rôle inconnu : aucun droit');

$db = TestDb::make();
$coach = TestDb::membre($db, 'Coach', 'coach');
$s = TestDb::seance($db, '2026-03-12 20:00:00');
$u = ['id' => $coach, 'nom' => 'Coach', 'role' => 'coach'];
$get = ['id' => $s];
$statut = fn(string $path) => Router::dispatch(TestDb::ctx($db, $u, '2026-03-13 12:00:00', [], $get), $path)->status;

T::check($statut('seance/participants') === 200, 'coach : voit les participants');
T::check($statut('seance/pointage') === 200, 'coach : accède au pointage');
T::check($statut('calendrier') === 200, 'coach : calendrier (droits membre)');
foreach (['membres', 'membre/ajouter', 'seance/creer', 'seance/modifier', 'membre/reinitialiser'] as $p) {
    T::check($statut($p) === 403 || Router::dispatch(TestDb::ctx($db, $u, '2026-03-13 12:00:00', ['csrf' => 'x'], $get), $p)->status === 403, "coach : $p refusé (403)");
}
$html = Router::dispatch(TestDb::ctx($db, $u, '2026-03-10 12:00:00', [], $get), 'seance')->body;
T::check(str_contains($html, 'Pointage') && !str_contains($html, 'Annuler la séance') && !str_contains($html, '>Modifier<'), 'fiche séance : le coach voit Participants/Pointage, pas Modifier/Annuler');
$ajout = (new AjouterMembreHandler($db))->handle('Coach2', 'coach2@test.local', 'coach', '', new DateTimeImmutable('2026-03-10'));
T::check($ajout['ok'], 'AjouterMembre accepte le rôle coach');
