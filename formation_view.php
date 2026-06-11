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
        <div class="alert info">Les inscriptions et déclarations d’intérêt pour ce créneau sont closes.</div>
    <?php else: ?>
        <div class="grid two participation-options">
            <section class="registration-form" aria-labelledby="registration-title">
                <h2 id="registration-title">Je m’inscris</h2>
                <?php if ((int) $slot['registered'] >= (int) $slot['capacity']): ?>
                    <div class="alert info">Ce créneau est complet.</div>
                <?php else: ?>
                    <p class="field-help">Confirmez votre participation à ce créneau.</p>
                    <form method="post" action="inscription.php" aria-describedby="registration-email-help">
                        <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                        <label for="registration_email">Votre adresse e-mail</label>
                        <input id="registration_email" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" aria-describedby="registration-email-help" required>
                        <p class="field-help" id="registration-email-help">Cette adresse permet d’identifier votre inscription et ne sera pas affichée publiquement.</p>

                        <button class="btn" type="submit">Je confirme mon inscription</button>
                    </form>
                <?php endif; ?>
            </section>

            <section class="registration-form interest-form" aria-labelledby="interest-title">
                <h2 id="interest-title">Je suis intéressé</h2>
                <p class="field-help">Signalez votre intérêt sans réserver de place. L’administrateur pourra vous contacter.</p>
                <form method="post" action="interet.php" aria-describedby="interest-email-help">
                    <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                    <label for="interest_email">Votre adresse e-mail</label>
                    <input id="interest_email" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" aria-describedby="interest-email-help" required>
                    <p class="field-help" id="interest-email-help">Votre adresse sera visible uniquement par les administrateurs.</p>

                    <button class="btn secondary" type="submit">Signaler mon intérêt</button>
                </form>
            </section>
        </div>
    <?php endif; ?>

</article>

<?php render_footer(); ?>
