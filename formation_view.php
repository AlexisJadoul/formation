<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/training.php';
$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT ts.*, COUNT(sr.id) AS registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.id = ?
    GROUP BY ts.id
");
$stmt->execute([$id]);
$slot = $stmt->fetch();

if (!$slot) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

$isAdmin = is_admin();
$registrations = [];
if ($isAdmin) {
    $stmt = db()->prepare("
        SELECT participant_email, created_at
        FROM slot_registrations
        WHERE slot_id = ? AND status = 'registered'
        ORDER BY created_at ASC
    ");
    $stmt->execute([$id]);
    $registrations = $stmt->fetchAll();
}

$outlookUrl = 'https://outlook.office.com/calendar/0/deeplink/compose?' . http_build_query([
    'path' => '/calendar/action/compose',
    'rru' => 'addevent',
    'startdt' => date('Y-m-d\TH:i:s', strtotime($slot['start_at'])),
    'enddt' => date('Y-m-d\TH:i:s', strtotime($slot['end_at'])),
    'subject' => $slot['title'],
    'body' => $slot['description'],
    'location' => $slot['location'],
], '', '&', PHP_QUERY_RFC3986);

render_header($slot['title']);
$descriptionSections = parse_training_description($slot['description'], [
    'PUBLIC VISÉ',
    'PRÉREQUIS',
    'OBJECTIFS',
    'CONTENU',
    'MÉTHODES PÉDAGOGIQUES',
]);
?>
<article class="card training-sheet">
    <header class="training-header">
        <p class="training-eyebrow">Fiche de formation</p>
        <h1><?= e($slot['title']) ?></h1>
        <p class="training-date">
            <span aria-hidden="true">&#128197;</span>
            <span><?= date('d/m/Y', strtotime($slot['start_at'])) ?></span>
            <span class="training-date-separator" aria-hidden="true"></span>
            <span><?= date('H:i', strtotime($slot['start_at'])) ?> – <?= date('H:i', strtotime($slot['end_at'])) ?></span>
        </p>
    </header>

    <div class="training-facts" aria-label="Informations pratiques">
        <div class="training-fact">
            <span class="training-fact-label">Intervenant</span>
            <strong><?= e($slot['trainer'] ?: 'Non précisé') ?></strong>
        </div>
        <div class="training-fact">
            <span class="training-fact-label">Lieu</span>
            <strong><?= e($slot['location'] ?: 'Non précisé') ?></strong>
        </div>
        <div class="training-fact">
            <span class="training-fact-label">Places occupées</span>
            <strong><?= (int) $slot['registered'] ?> sur <?= (int) $slot['capacity'] ?></strong>
        </div>
    </div>

    <div class="training-content">
        <?php foreach ($descriptionSections as $section): ?>
            <?php if ($section['heading'] === null): ?>
                <div class="training-introduction">
                    <?php foreach ($section['lines'] as $line): ?>
                        <?php if (trim($line) !== ''): ?><p><?= e(trim($line)) ?></p><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <section class="training-section">
                    <h2><?= e($section['heading']) ?></h2>
                    <?php $contentLines = array_values(array_filter(array_map('trim', $section['lines']), static fn ($line) => $line !== '')); ?>
                    <?php if (count($contentLines) > 1): ?>
                        <ul>
                            <?php foreach ($contentLines as $line): ?>
                                <li><?= e(ltrim($line, "-–• \t")) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php elseif ($contentLines): ?>
                        <p><?= e($contentLines[0]) ?></p>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($isAdmin): ?>
        <section class="admin-participants">
            <h2>Participants inscrits <span class="badge"><?= count($registrations) ?></span></h2>
            <?php if (!$registrations): ?>
                <p>Aucune inscription pour le moment.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Adresse e-mail</th>
                            <th>Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $registration): ?>
                            <tr>
                                <td><a href="mailto:<?= e($registration['participant_email']) ?>"><?= e($registration['participant_email']) ?></a></td>
                                <td><?= date('d/m/Y H:i', strtotime($registration['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <?php if ((int) ($_SESSION['calendar_slot_id'] ?? 0) === (int) $slot['id']): ?>
        <div class="calendar-invitation">
            <p><strong>Votre inscription est confirmée.</strong> Ajoutez maintenant ce créneau dans Outlook.</p>
            <a class="btn" href="<?= e($outlookUrl) ?>" target="_blank" rel="noopener noreferrer">Ajouter dans Outlook</a>
        </div>
    <?php endif; ?>

    <?php if (!$isAdmin && strtotime($slot['start_at']) <= time()): ?>
        <div class="alert info">Les inscriptions à ce créneau sont closes.</div>
    <?php elseif (!$isAdmin && (int) $slot['registered'] >= (int) $slot['capacity']): ?>
        <div class="alert info">Ce créneau est complet. Vous pouvez signaler votre intérêt pour être recontacté.</div>
        <button class="btn secondary" type="button" data-open-dialog="interest-dialog">Je suis intéressé</button>

        <dialog class="email-dialog" id="interest-dialog" aria-labelledby="interest-title">
            <form method="post" action="interet.php">
                <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="dialog-heading">
                    <h2 id="interest-title">Je suis intéressé</h2>
                    <button class="dialog-close" type="button" data-close-dialog aria-label="Fermer">×</button>
                </div>
                <p>Indiquez votre adresse e-mail pour être recontacté si une place se libère ou si un nouveau créneau est créé.</p>
                <label for="interest_email">Votre adresse e-mail</label>
                <input id="interest_email" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" required>
                <button class="btn secondary" type="submit">Signaler mon intérêt</button>
            </form>
        </dialog>
    <?php elseif (!$isAdmin): ?>
        <button class="btn" type="button" data-open-dialog="registration-dialog">Je m’inscris</button>

        <dialog class="email-dialog" id="registration-dialog" aria-labelledby="registration-title">
            <form method="post" action="inscription.php">
                <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="dialog-heading">
                    <h2 id="registration-title">Je m’inscris</h2>
                    <button class="dialog-close" type="button" data-close-dialog aria-label="Fermer">×</button>
                </div>
                <p>Indiquez votre adresse e-mail pour confirmer votre inscription.</p>
                <label for="registration_email">Votre adresse e-mail</label>
                <input id="registration_email" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" required>
                <button class="btn" type="submit">Je confirme mon inscription</button>
            </form>
        </dialog>
    <?php endif; ?>

</article>

<?php render_footer(); ?>
