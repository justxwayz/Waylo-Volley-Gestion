<?php
$db = TestDb::make();
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$futur = TestDb::evenement($db, '2026-04-11 09:00:00', '2026-04-01 23:59:00');
$passe = TestDb::evenement($db, '2026-03-01 09:00:00', '2026-02-20 23:59:00');
$h = new AnnulerEvenementHandler($db);

T::check($h->handle($futur, $now)['ok'], 'annulation d\'un événement à venir');
T::check($db->query("SELECT statut FROM evenement WHERE id = $futur")->fetchColumn() === 'annule', 'statut = annule (pas de suppression)');
T::check($h->handle($futur, $now)['ok'] === false, 'double annulation refusée');
T::check($h->handle($passe, $now)['ok'] === false, 'événement passé non annulable');
T::check($h->handle(999, $now)['ok'] === false, 'événement inconnu refusé');
T::check((int)$db->query('SELECT COUNT(*) FROM evenement')->fetchColumn() === 2, 'les deux événements restent en base');
