<?php
$db = TestDb::make();
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$futur = TestDb::seance($db, '2026-03-12 20:00:00');
$passee = TestDb::seance($db, '2026-03-09 20:00:00');
$h = new AnnulerSeanceHandler($db);

T::check($h->handle($futur, $now)['ok'], 'annulation d\'une séance à venir');
T::check($db->query("SELECT statut FROM seance WHERE id = $futur")->fetchColumn() === 'annulee', 'statut = annulee (pas de suppression)');
T::check($h->handle($futur, $now)['ok'] === false, 'double annulation refusée');
T::check($h->handle($passee, $now)['ok'] === false, 'séance passée non annulable');
T::check($db->query('SELECT COUNT(*) FROM seance')->fetchColumn() == 2, 'la séance annulée reste en base');
