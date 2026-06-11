<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_login();

$id = (int) ($_GET['id'] ?? 0);

$stmt = db()->prepare("
    SELECT ts.*, COUNT(sr.id) AS registered,
    MAX(CASE WHEN sr.user_id = ? AND sr.status = 'registered' THEN 1 ELSE 0 END) AS is_registered
    FROM training_slots ts
    LEFT JOIN slot_registrations sr ON sr.slot_id = ts.id AND sr.status = 'registered'
    WHERE ts.id = ?
    GROUP BY ts.id
");
$stmt->execute([$user['id'], $id]);
$slot = $stmt->fetch();

if (!$slot) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

$stmt = db()->prepare("
    SELECT u.name, u.email, sr.created_at
    FROM slot_registrations sr
    JOIN users u ON u.id = sr.user_id
    WHERE sr.slot_id = ? AND sr.status = 'registered'
    ORDER BY sr.created_at ASC
");
$stmt->execute([$id]);
$registeredUsers = $stmt->fetchAll();

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
        <?php if (is_admin($user)): ?>
            <a class="btn secondary" href="formation_edit.php?id=<?= (int) $slot['id'] ?>">Modifier</a>
        <?php endif; ?>
    </div>

    <p><?= nl2br(e($slot['description'])) ?></p>

    <ul class="details">
        <li><strong>Intervenant :</strong> <?= e($slot['trainer'] ?: 'Non précisé') ?></li>
        <li><strong>Lieu :</strong> <?= e($slot['location'] ?: 'Non précisé') ?></li>
        <li><strong>Capacité :</strong> <?= (int) $slot['registered'] ?> / <?= (int) $slot['capacity'] ?> inscrit(s)</li>
    </ul>

    <form method="post" action="inscription.php">
        <input type="hidden" name="slot_id" value="<?= (int) $slot['id'] ?>">
        <button class="btn <?= $slot['is_registered'] ? 'secondary' : '' ?>" type="submit">
            <?= $slot['is_registered'] ? 'Annuler mon inscription' : 'M’inscrire à ce créneau' ?>
        </button>
    </form>
</article>

<?php if (is_admin($user)): ?>
    <section class="card">
        <h2>Agents inscrits</h2>
        <?php if (!$registeredUsers): ?>
            <p>Aucune inscription.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>Nom</th><th>Email</th><th>Date d’inscription</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($registeredUsers as $registeredUser): ?>
                        <tr>
                            <td><?= e($registeredUser['name']) ?></td>
                            <td><?= e($registeredUser['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($registeredUser['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php render_footer(); ?>
