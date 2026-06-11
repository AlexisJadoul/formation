<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_login();

$stmt = db()->prepare("
    SELECT ts.*,
    COUNT(sr.id) AS registered,
    MAX(CASE WHEN sr.user_id = ? AND sr.status = 'registered' THEN 1 ELSE 0 END) AS is_registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.start_at >= NOW()
    GROUP BY ts.id
    ORDER BY ts.start_at ASC
");
$stmt->execute([$user['id']]);
$slots = $stmt->fetchAll();

render_header('Créneaux de formation');
?>
<div class="page-title">
    <div>
        <h1>Créneaux de formation</h1>
        <p>Consulte les créneaux disponibles et inscris-toi directement aux formations proposées.</p>
    </div>
    <?php if (is_admin($user)): ?>
        <a class="btn" href="formation_edit.php">Créer un créneau</a>
    <?php endif; ?>
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

            <form method="post" action="inscription.php">
                <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
                <button class="btn small <?= $slot['is_registered'] ? 'secondary' : '' ?>" type="submit">
                    <?= $slot['is_registered'] ? 'Annuler mon inscription' : 'M’inscrire' ?>
                </button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
