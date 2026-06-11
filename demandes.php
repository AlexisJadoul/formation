<?php
require_once __DIR__ . '/includes/layout.php';
$stmt = db()->query("
    SELECT tr.*, (SELECT COUNT(*) FROM request_interests ri WHERE ri.request_id = tr.id) AS interested
    FROM training_requests tr
    WHERE tr.status = 'approved'
    ORDER BY interested DESC, tr.created_at DESC
");
$requests = $stmt->fetchAll();

render_header('Demandes de formation');
?>
<div class="page-title">
    <div>
        <h1>Demandes de formation</h1>
        <p>Consultez les demandes de formation validées et signalez celles qui vous intéressent.</p>
    </div>
    <a class="btn" href="demande_create.php">Créer une demande</a>
</div>

<div class="card">
    <?php if (!$requests): ?>
        <p>Aucune demande pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($requests as $request): ?>
        <article class="request-card">
            <div>
                <h2><?= e($request['title']) ?></h2>
                <p><?= nl2br(e($request['description'])) ?></p>
                <div class="meta">
                    Statut : <span class="badge">Validée</span> -
                    <?= (int) $request['interested'] ?> personne(s) intéressée(s)
                </div>

                <?php if ($request['admin_comment']): ?>
                    <p class="admin-comment">Commentaire admin : <?= e($request['admin_comment']) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <button class="btn small secondary" type="button" data-open-dialog="request-interest-<?= (int) $request['id'] ?>">Je suis intéressé</button>
            </div>
        </article>

        <dialog class="email-dialog" id="request-interest-<?= (int) $request['id'] ?>" aria-labelledby="request-interest-title-<?= (int) $request['id'] ?>">
            <form method="post" action="demande_interet.php">
                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <div class="dialog-heading">
                    <h2 id="request-interest-title-<?= (int) $request['id'] ?>">Je suis intéressé</h2>
                    <button class="dialog-close" type="button" data-close-dialog aria-label="Fermer">×</button>
                </div>
                <p><strong><?= e($request['title']) ?></strong></p>
                <label for="request-interest-email-<?= (int) $request['id'] ?>">Votre adresse e-mail</label>
                <input id="request-interest-email-<?= (int) $request['id'] ?>" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" required>
                <button class="btn secondary" type="submit">Signaler mon intérêt</button>
            </form>
        </dialog>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
