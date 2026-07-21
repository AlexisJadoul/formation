<?php
require_once __DIR__ . '/includes/layout.php';
$stats = [];

$stats['approved_requests'] = db()->query("SELECT COUNT(*) FROM training_requests WHERE status = 'approved'")->fetchColumn();
$stats['pending_requests'] = db()->query("SELECT COUNT(*) FROM training_requests WHERE status = 'pending'")->fetchColumn();
$stats['slots'] = db()->query("SELECT COUNT(*) FROM training_slots WHERE start_at >= NOW()")->fetchColumn();

$stmt = db()->query("
    SELECT tr.id, tr.title, tr.description,
        (SELECT COUNT(*) FROM request_interests ri WHERE ri.request_id = tr.id) AS interested
    FROM training_requests tr
    WHERE tr.status = 'approved'
    ORDER BY interested DESC, tr.created_at DESC
    LIMIT 5
");
$topRequests = $stmt->fetchAll();

$stmt = db()->query("
    SELECT ts.*, COUNT(sr.id) AS registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.start_at >= NOW()
    GROUP BY ts.id
    ORDER BY ts.start_at ASC
    LIMIT 5
");
$nextSlots = $stmt->fetchAll();

render_header('Tableau de bord');
?>
<section class="hero card">
    <h1>Bienvenue sur la plateforme</h1>
    <p>
        Cette plateforme permet de centraliser les besoins de formation, de mesurer l'intérêt des agents,
        puis d'ouvrir des créneaux adaptés aux demandes les plus soutenues.
    </p>
    <div class="actions">
        <a class="btn" href="demande_create.php">Créer une demande</a>
        <a class="btn secondary" href="demandes.php">Voir les demandes</a>
        <a class="btn secondary" href="formations.php">Voir les créneaux</a>
    </div>
</section>

<section class="grid stats">
    <div class="card"><strong><?= (int) $stats['approved_requests'] ?></strong><span>Demandes visibles</span></div>
    <div class="card"><strong><?= (int) $stats['pending_requests'] ?></strong><span>Demandes en attente</span></div>
    <div class="card"><strong><?= (int) $stats['slots'] ?></strong><span>Créneaux à venir</span></div>
</section>

<section class="grid two">
    <div class="card dashboard-panel">
        <h2>Demandes les plus soutenues</h2>
        <?php if (!$topRequests): ?>
            <p>Aucune demande validée pour le moment.</p>
        <?php endif; ?>
        <?php foreach ($topRequests as $request): ?>
            <article class="dashboard-request-card">
                <a class="dashboard-request-details" href="demandes.php#demande-<?= (int) $request['id'] ?>" aria-label="Voir les détails de la demande <?= e($request['title']) ?>">
                    <h3><?= e($request['title']) ?></h3>
                    <span class="dashboard-request-arrow" aria-hidden="true">→</span>
                </a>
                <p><?= e(mb_strimwidth($request['description'], 0, 130, '...')) ?></p>
                <div class="dashboard-request-footer">
                    <span class="badge"><?= (int) $request['interested'] ?> intéressé(s)</span>
                    <button class="btn small secondary" type="button" data-open-dialog="dashboard-request-interest-<?= (int) $request['id'] ?>">Je suis intéressé</button>
                </div>
            </article>

            <dialog class="email-dialog" id="dashboard-request-interest-<?= (int) $request['id'] ?>" aria-labelledby="dashboard-request-interest-title-<?= (int) $request['id'] ?>">
                <form method="post" action="demande_interet.php">
                    <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="return_to" value="dashboard.php">
                    <div class="dialog-heading">
                        <h2 id="dashboard-request-interest-title-<?= (int) $request['id'] ?>">Je suis intéressé</h2>
                        <button class="dialog-close" type="button" data-close-dialog aria-label="Fermer">×</button>
                    </div>
                    <p><strong><?= e($request['title']) ?></strong></p>
                    <label for="dashboard-request-interest-email-<?= (int) $request['id'] ?>">Votre adresse e-mail</label>
                    <input id="dashboard-request-interest-email-<?= (int) $request['id'] ?>" type="email" name="participant_email" maxlength="190" autocomplete="email" inputmode="email" required>
                    <button class="btn secondary" type="submit">Signaler mon intérêt</button>
                </form>
            </dialog>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>Prochains créneaux</h2>
        <?php if (!$nextSlots): ?>
            <p>Aucun créneau à venir.</p>
        <?php endif; ?>
        <?php foreach ($nextSlots as $slot): ?>
            <a class="list-item dashboard-list-link" href="formation_view.php?id=<?= (int) $slot['id'] ?>" aria-label="Consulter le créneau <?= e($slot['title']) ?>">
                <h3><?= e($slot['title']) ?></h3>
                <p><?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?> - <?= date('H:i', strtotime($slot['end_at'])) ?></p>
                <span class="badge"><?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?> inscrit(s)</span>
                <span class="list-item-action">Voir le créneau et s’inscrire <span aria-hidden="true">→</span></span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
