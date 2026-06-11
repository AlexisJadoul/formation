<?php
require_once __DIR__ . '/includes/layout.php';
$stmt = db()->query("
    SELECT tr.*, COUNT(rv.id) AS votes
    FROM training_requests tr
    LEFT JOIN request_votes rv ON rv.request_id = tr.id
    WHERE tr.status = 'approved'
    GROUP BY tr.id
    ORDER BY votes DESC, tr.created_at DESC
");
$requests = $stmt->fetchAll();

render_header('Demandes de formation');
?>
<div class="page-title">
    <div>
        <h1>Demandes de formation</h1>
        <p>Consulte les demandes de formation validées et leur niveau d’intérêt.</p>
    </div>
    <a class="btn" href="demande_create.php">Créer une demande</a>
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
                    Statut : <span class="badge"><?= e($request['status']) ?></span> -
                    <?= (int) $request['votes'] ?> vote(s)
                </div>

                <?php if ($request['admin_comment']): ?>
                    <p class="admin-comment">Commentaire admin : <?= e($request['admin_comment']) ?></p>
                <?php endif; ?>
            </div>

        </article>
    <?php endforeach; ?>
</div>
<?php render_footer(); ?>
