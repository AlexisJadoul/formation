<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_login();

$stmt = db()->prepare("
    SELECT tr.*, u.name, COUNT(rv.id) AS votes,
    MAX(CASE WHEN rv.user_id = ? THEN 1 ELSE 0 END) AS has_voted
    FROM training_requests tr
    JOIN users u ON u.id = tr.user_id
    LEFT JOIN request_votes rv ON rv.request_id = tr.id
    WHERE tr.status = 'approved' OR tr.user_id = ?
    GROUP BY tr.id
    ORDER BY tr.status = 'approved' DESC, votes DESC, tr.created_at DESC
");
$stmt->execute([$user['id'], $user['id']]);
$requests = $stmt->fetchAll();

render_header('Demandes de formation');
?>
<div class="page-title">
    <div>
        <h1>Demandes de formation</h1>
        <p>Les demandes validées sont visibles par tous les agents. Chacun peut indiquer son intérêt.</p>
    </div>
    <a class="btn" href="demande_create.php">Nouvelle demande</a>
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
                    Demandé par <?= e($request['name']) ?> -
                    statut : <span class="badge"><?= e($request['status']) ?></span> -
                    <?= (int) $request['votes'] ?> vote(s)
                </div>

                <?php if ($request['admin_comment']): ?>
                    <p class="admin-comment">Commentaire admin : <?= e($request['admin_comment']) ?></p>
                <?php endif; ?>
            </div>

            <?php if ($request['status'] === 'approved' && (int) $request['user_id'] !== (int) $user['id']): ?>
                <form method="post" action="demande_vote.php">
                    <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                    <button class="btn small <?= $request['has_voted'] ? 'secondary' : '' ?>" type="submit">
                        <?= $request['has_voted'] ? 'Retirer mon intérêt' : 'Ça m’intéresse' ?>
                    </button>
                </form>
            <?php endif; ?>
        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
