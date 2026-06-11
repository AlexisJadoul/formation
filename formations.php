<?php
require_once __DIR__ . '/includes/layout.php';
$stmt = db()->query("
    SELECT ts.*, COUNT(sr.id) AS registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.start_at >= NOW()
    GROUP BY ts.id
    ORDER BY ts.start_at ASC
");
$slots = $stmt->fetchAll();

render_header('Créneaux de formation');
?>
<div class="page-title">
    <div>
        <h1>Créneaux de formation</h1>
        <p>Consultez les créneaux proposés et inscrivez-vous avec votre adresse e-mail, sans créer de compte.</p>
    </div>
</div>

<div class="grid two">
    <?php if (!$slots): ?>
        <div class="card"><p>Aucun créneau disponible pour le moment.</p></div>
    <?php endif; ?>

    <?php foreach ($slots as $slot): ?>
        <article class="card slot-card">
            <h2><a href="formation_view.php?id=<?= (int) $slot['id'] ?>"><?= e($slot['title']) ?></a></h2>
            <p><?= e(mb_strimwidth($slot['description'], 0, 180, '...')) ?></p>
            <div class="meta">
                <?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?> -
                <?= date('H:i', strtotime($slot['end_at'])) ?><br>
                Lieu : <?= e($slot['location'] ?: 'Non précisé') ?><br>
                Places : <?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?>
            </div>

            <a class="btn small secondary" href="formation_view.php?id=<?= (int) $slot['id'] ?>">Voir la formation et m’inscrire</a>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
