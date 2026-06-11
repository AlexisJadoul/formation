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

</article>

<?php render_footer(); ?>
