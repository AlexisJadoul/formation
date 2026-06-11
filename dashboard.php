<?php
require_once __DIR__ . '/includes/layout.php';
$stats = [];

$stats['approved_requests'] = db()->query("SELECT COUNT(*) FROM training_requests WHERE status = 'approved'")->fetchColumn();
$stats['pending_requests'] = db()->query("SELECT COUNT(*) FROM training_requests WHERE status = 'pending'")->fetchColumn();
$stats['slots'] = db()->query("SELECT COUNT(*) FROM training_slots WHERE start_at >= NOW()")->fetchColumn();

$stmt = db()->query("
    SELECT tr.id, tr.title, tr.description, u.name, COUNT(rv.id) AS votes
    FROM training_requests tr
    JOIN users u ON u.id = tr.user_id
    LEFT JOIN request_votes rv ON rv.request_id = tr.id
    WHERE tr.status = 'approved'
    GROUP BY tr.id
    ORDER BY votes DESC, tr.created_at DESC
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
        <a class="btn" href="demandes.php">Voir les demandes</a>
        <a class="btn secondary" href="formations.php">Voir les créneaux</a>
    </div>
</section>

<section class="grid stats">
    <div class="card"><strong><?= (int) $stats['approved_requests'] ?></strong><span>Demandes visibles</span></div>
    <div class="card"><strong><?= (int) $stats['pending_requests'] ?></strong><span>Demandes en attente</span></div>
    <div class="card"><strong><?= (int) $stats['slots'] ?></strong><span>Créneaux à venir</span></div>
</section>

<section class="grid two">
    <div class="card">
        <h2>Demandes les plus soutenues</h2>
        <?php if (!$topRequests): ?>
            <p>Aucune demande validée pour le moment.</p>
        <?php endif; ?>
        <?php foreach ($topRequests as $request): ?>
            <article class="list-item">
                <h3><?= e($request['title']) ?></h3>
                <p><?= e(mb_strimwidth($request['description'], 0, 130, '...')) ?></p>
                <span class="badge"><?= (int) $request['votes'] ?> vote(s)</span>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="card">
        <h2>Prochains créneaux</h2>
        <?php if (!$nextSlots): ?>
            <p>Aucun créneau à venir.</p>
        <?php endif; ?>
        <?php foreach ($nextSlots as $slot): ?>
            <article class="list-item">
                <h3><a href="formation_view.php?id=<?= (int) $slot['id'] ?>"><?= e($slot['title']) ?></a></h3>
                <p><?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?> - <?= date('H:i', strtotime($slot['end_at'])) ?></p>
                <span class="badge"><?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?> inscrit(s)</span>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php render_footer(); ?>
