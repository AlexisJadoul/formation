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
        <div class="alert info">Ce créneau est complet.</div>
    <?php else: ?>
        <section class="registration-form" aria-labelledby="registration-title">
            <h2 id="registration-title">Je m’inscris à cette formation</h2>
            <form method="post" action="inscription.php" aria-describedby="email-help">
                <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

                <label for="participant_email">Votre adresse e-mail</label>
                <input id="participant_email" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" aria-describedby="email-help" required>
                <p class="field-help" id="email-help">Cette adresse permet d’identifier votre inscription et ne sera pas affichée publiquement.</p>

                <button class="btn" type="submit">Je confirme mon inscription</button>
            </form>
        </section>
    <?php endif; ?>

</article>

<?php render_footer(); ?>
