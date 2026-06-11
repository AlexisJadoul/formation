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
    SELECT tr.*, COALESCE(tr.requester_email, u.email) AS email, u.name,
        (SELECT COUNT(*) FROM request_interests ri WHERE ri.request_id = tr.id) AS interested
    FROM training_requests tr
    LEFT JOIN users u ON u.id = tr.user_id
    ORDER BY tr.status = 'pending' DESC, tr.created_at DESC
");
$requests = $stmt->fetchAll();

$interestsByRequest = [];
$stmt = db()->query('SELECT request_id, participant_email, created_at FROM request_interests ORDER BY created_at ASC');
foreach ($stmt->fetchAll() as $interest) {
    $interestsByRequest[$interest['request_id']][] = $interest;
}

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
                    <?= $request['name'] ? e($request['name']) . ' - ' : '' ?><?= e($request['email']) ?> -
                    statut actuel : <span class="badge"><?= e($request['status']) ?></span> -
                    <?= (int) $request['interested'] ?> personne(s) intéressée(s)
                </div>
                <?php if ($request['status'] === 'approved'): ?>
                    <details class="interest-details">
                        <summary>Voir les personnes intéressées (<?= (int) $request['interested'] ?>)</summary>
                        <?php if (empty($interestsByRequest[$request['id']])): ?>
                            <p class="small">Personne n’a encore signalé son intérêt.</p>
                        <?php else: ?>
                            <ul>
                                <?php foreach ($interestsByRequest[$request['id']] as $interest): ?>
                                    <li><a href="mailto:<?= e($interest['participant_email']) ?>"><?= e($interest['participant_email']) ?></a> — <?= date('d/m/Y H:i', strtotime($interest['created_at'])) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </details>
                <?php endif; ?>
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
