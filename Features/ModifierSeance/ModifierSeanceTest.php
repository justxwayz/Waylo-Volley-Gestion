<?php
$db = TestDb::make();
$now = new DateTimeImmutable('2026-03-10 12:00:00');
$futur = TestDb::seance($db, '2026-03-12 20:00:00');
$passee = TestDb::seance($db, '2026-03-09 20:00:00');
$annulee = TestDb::seance($db, '2026-03-15 20:00:00', 'annulee');
$h = new ModifierSeanceHandler($db);

T::check($h->handle($futur, 'Match', '2026-03-12T19:00', '2026-03-12T21:00', 'Salle B', $now)['ok'], 'modification d\'une séance à venir');
T::check($h->find($futur)['lieu'] === 'Salle B' && $h->find($futur)['debut'] === '2026-03-12 19:00:00', 'valeurs bien enregistrées');
T::check($h->handle($passee, 'X', '2026-03-12T19:00', '2026-03-12T21:00', 'Y', $now)['ok'] === false, 'séance déjà commencée => refus');
T::check($h->handle($annulee, 'X', '2026-03-16T19:00', '2026-03-16T21:00', 'Y', $now)['ok'] === false, 'séance annulée => refus');
T::check($h->handle($futur, 'X', '2026-03-12T21:00', '2026-03-12T19:00', 'Y', $now)['ok'] === false, 'fin avant début => refus');
T::check($h->handle(999, 'X', '2026-03-12T19:00', '2026-03-12T21:00', 'Y', $now)['ok'] === false, 'séance inexistante => refus');
