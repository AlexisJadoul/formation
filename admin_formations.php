<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $deleteId = (int) $_POST['delete_id'];
    $stmt = db()->prepare('DELETE FROM training_slots WHERE id = ?');
    $stmt->execute([$deleteId]);
    flash('Créneau supprimé.');
    redirect('admin_formations.php');
}

$stmt = db()->query("
    SELECT ts.*, COUNT(sr.id) AS registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    GROUP BY ts.id
    ORDER BY ts.start_at DESC
");
$slots = $stmt->fetchAll();

render_header('Administration des créneaux');
?>
<div class="page-title">
    <div>
        <h1>Administration des créneaux</h1>
        <p>Création, modification et suivi des inscriptions.</p>
    </div>
    <a class="btn" href="formation_edit.php">Créer un créneau</a>
</div>

<div class="card">
    <?php if (!$slots): ?>
        <p>Aucun créneau.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Titre</th>
                    <th>Date</th>
                    <th>Places</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($slots as $slot): ?>
                    <tr>
                        <td><?= e($slot['title']) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($slot['start_at'])) ?></td>
                        <td><?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?></td>
                        <td class="table-actions">
                            <a class="btn small secondary" href="formation_view.php?id=<?= (int) $slot['id'] ?>">Voir</a>
                            <a class="btn small" href="formation_edit.php?id=<?= (int) $slot['id'] ?>">Modifier</a>
                            <form method="post" onsubmit="return confirm('Supprimer ce créneau ?');">
                                <input type="hidden" name="delete_id" value="<?= (int) $slot['id'] ?>">
                                <button class="btn small danger" type="submit">Supprimer</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
<?php render_footer(); ?>
