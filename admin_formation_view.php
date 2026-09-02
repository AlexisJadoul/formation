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

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location'] ?? '');
    $capacity = filter_var($_POST['capacity'] ?? null, FILTER_VALIDATE_INT);

    if (!csrf_is_valid($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Le formulaire a expiré. Veuillez réessayer.';
    }

    if ($capacity === false || $capacity < 1) {
        $errors[] = 'La capacité doit être supérieure à 0.';
    }

    $stmt = db()->prepare("SELECT COUNT(*) FROM slot_registrations WHERE slot_id = ? AND status = 'registered'");
    $stmt->execute([$id]);
    $registrationCount = (int) $stmt->fetchColumn();

    if ($capacity !== false && $capacity < $registrationCount) {
        $errors[] = 'La capacité ne peut pas être inférieure au nombre de participants déjà inscrits (' . $registrationCount . ').';
    }

    if (!$errors) {
        $stmt = db()->prepare('UPDATE training_slots SET location = ?, capacity = ?, updated_at = NOW() WHERE id = ?');
        $stmt->execute([$location, $capacity, $id]);
        flash('Le lieu et la capacité du créneau ont été modifiés.');
        redirect('admin_formation_view.php?id=' . $id);
    }

    $slot['location'] = $location;
    $slot['capacity'] = $capacity === false ? ($_POST['capacity'] ?? '') : $capacity;
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
        <a class="btn small secondary" href="#modifier-creneau">Modifier le créneau</a>
        <a class="btn small" href="admin_formations.php">Retour aux créneaux</a>
    </div>
</div>

<section class="card form-card" id="modifier-creneau">
    <h2>Modifier le lieu ou la capacité</h2>
    <p class="small">Vous pouvez mettre à jour ces informations même lorsque le créneau est déjà organisé.</p>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="grid two compact">
            <div>
                <label for="location">Lieu</label>
                <input id="location" type="text" name="location" value="<?= e($slot['location']) ?>">
            </div>
            <div>
                <label for="capacity">Nombre de places</label>
                <input id="capacity" type="number" name="capacity" min="<?= max(1, count($registrations)) ?>" value="<?= e((string) $slot['capacity']) ?>" required>
            </div>
        </div>

        <button class="btn" type="submit">Enregistrer les modifications</button>
    </form>
</section>

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
