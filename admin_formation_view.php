<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_admin();
$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM training_slots WHERE id = ?');
$stmt->execute([$id]);
$slot = $stmt->fetch();

if (!$slot) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

$stmt = db()->prepare("
    SELECT participant_email, created_at
    FROM slot_registrations
    WHERE slot_id = ? AND status = 'registered'
    ORDER BY created_at ASC
");
$stmt->execute([$id]);
$registrations = $stmt->fetchAll();

$stmt = db()->prepare('SELECT participant_email, created_at FROM slot_interests WHERE slot_id = ? ORDER BY created_at ASC');
$stmt->execute([$id]);
$interests = $stmt->fetchAll();

render_header('Participants - ' . $slot['title']);
?>
<div class="page-title">
    <div>
        <h1><?= e($slot['title']) ?></h1>
        <p><?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?> - <?= date('H:i', strtotime($slot['end_at'])) ?></p>
    </div>
    <div class="table-actions">
        <a class="btn small secondary" href="formation_view.php?id=<?= (int) $slot['id'] ?>">Voir la page publique</a>
        <a class="btn small" href="admin_formations.php">Retour aux créneaux</a>
    </div>
</div>

<div class="grid two participant-lists">
    <section class="card">
        <h2>Inscrits <span class="badge"><?= count($registrations) ?> / <?= (int) $slot['capacity'] ?></span></h2>
        <p class="small">Ces personnes ont confirmé leur participation.</p>
        <?php if (!$registrations): ?>
            <p>Aucune inscription pour le moment.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Adresse e-mail</th><th>Inscrit le</th></tr></thead>
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

    <section class="card">
        <h2>Intéressés <span class="badge"><?= count($interests) ?></span></h2>
        <p class="small">Ces personnes souhaitent être informées sans être inscrites.</p>
        <?php if (!$interests): ?>
            <p>Personne n’a signalé son intérêt pour le moment.</p>
        <?php else: ?>
            <table>
                <thead><tr><th>Adresse e-mail</th><th>Intéressé le</th></tr></thead>
                <tbody>
                    <?php foreach ($interests as $interest): ?>
                        <tr>
                            <td><a href="mailto:<?= e($interest['participant_email']) ?>"><?= e($interest['participant_email']) ?></a></td>
                            <td><?= date('d/m/Y H:i', strtotime($interest['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
</div>
<?php render_footer(); ?>
