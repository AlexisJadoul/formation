<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $requestId = (int) ($_POST['request_id'] ?? 0);
    $status = $_POST['status'] ?? '';
    $comment = trim($_POST['admin_comment'] ?? '');

    if (in_array($status, ['approved', 'rejected', 'pending'], true)) {
        $stmt = db()->prepare('UPDATE training_requests SET status = ?, admin_comment = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$status, $comment, $requestId]);
        flash('Demande mise à jour.');
    }

    redirect('admin_demandes.php');
}

$stmt = db()->query("
    SELECT tr.*, u.name, u.email, COUNT(rv.id) AS votes
    FROM training_requests tr
    JOIN users u ON u.id = tr.user_id
    LEFT JOIN request_votes rv ON rv.request_id = tr.id
    GROUP BY tr.id
    ORDER BY tr.status = 'pending' DESC, tr.created_at DESC
");
$requests = $stmt->fetchAll();

render_header('Administration des demandes');
?>
<h1>Administration des demandes</h1>
<div class="card">
    <?php if (!$requests): ?>
        <p>Aucune demande.</p>
    <?php endif; ?>

    <?php foreach ($requests as $request): ?>
        <article class="request-card admin">
            <div>
                <h2><?= e($request['title']) ?></h2>
                <p><?= nl2br(e($request['description'])) ?></p>
                <div class="meta">
                    <?= e($request['name']) ?> - <?= e($request['email']) ?> -
                    statut actuel : <span class="badge"><?= e($request['status']) ?></span> -
                    <?= (int) $request['votes'] ?> vote(s)
                </div>
            </div>

            <form method="post" class="inline-form">
                <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">

                <label>Décision</label>
                <select name="status">
                    <option value="pending" <?= $request['status'] === 'pending' ? 'selected' : '' ?>>En attente</option>
                    <option value="approved" <?= $request['status'] === 'approved' ? 'selected' : '' ?>>Validée</option>
                    <option value="rejected" <?= $request['status'] === 'rejected' ? 'selected' : '' ?>>Refusée</option>
                </select>

                <label>Commentaire</label>
                <textarea name="admin_comment" rows="3"><?= e($request['admin_comment']) ?></textarea>

                <button class="btn small" type="submit">Enregistrer</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
