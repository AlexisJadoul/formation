<?php
require_once __DIR__ . '/includes/layout.php';
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

render_header($slot['title']);
?>
<article class="card">
    <div class="page-title">
        <div>
            <h1><?= e($slot['title']) ?></h1>
            <p class="meta">
                <?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?> -
                <?= date('H:i', strtotime($slot['end_at'])) ?>
            </p>
        </div>
    </div>

    <p><?= nl2br(e($slot['description'])) ?></p>

    <ul class="details">
        <li><strong>Intervenant :</strong> <?= e($slot['trainer'] ?: 'Non précisé') ?></li>
        <li><strong>Lieu :</strong> <?= e($slot['location'] ?: 'Non précisé') ?></li>
        <li><strong>Capacité :</strong> <?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?> inscrit(s)</li>
    </ul>

    <?php if (strtotime($slot['start_at']) <= time()): ?>
        <div class="alert info">Les inscriptions à ce créneau sont closes.</div>
    <?php elseif ((int) $slot['registered'] >= (int) $slot['capacity']): ?>
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
    <?php else: ?>
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
