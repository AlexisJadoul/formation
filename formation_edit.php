<?php
require_once __DIR__ . '/includes/layout.php';
$user = require_admin();

$id = (int) ($_GET['id'] ?? 0);
$slot = [
    'title' => '',
    'description' => '',
    'trainer' => '',
    'location' => '',
    'start_at' => '',
    'end_at' => '',
    'capacity' => 10,
];

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM training_slots WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();

    if (!$found) {
        http_response_code(404);
        exit('Créneau introuvable.');
    }

    $slot = $found;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot['title'] = trim($_POST['title'] ?? '');
    $slot['description'] = trim($_POST['description'] ?? '');
    $slot['trainer'] = trim($_POST['trainer'] ?? '');
    $slot['location'] = trim($_POST['location'] ?? '');
    $slot['start_at'] = $_POST['start_at'] ?? '';
    $slot['end_at'] = $_POST['end_at'] ?? '';
    $slot['capacity'] = (int) ($_POST['capacity'] ?? 0);

    if ($slot['title'] === '' || $slot['description'] === '' || $slot['start_at'] === '' || $slot['end_at'] === '') {
        $errors[] = 'Le titre, la description, la date de début et la date de fin sont obligatoires.';
    }

    if ($slot['capacity'] < 1) {
        $errors[] = 'La capacité doit être supérieure à 0.';
    }

    if (!$errors) {
        $startAt = str_replace('T', ' ', $slot['start_at']) . ':00';
        $endAt = str_replace('T', ' ', $slot['end_at']) . ':00';

        if ($id > 0) {
            $stmt = db()->prepare('
                UPDATE training_slots
                SET title = ?, description = ?, trainer = ?, location = ?, start_at = ?, end_at = ?, capacity = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $slot['title'],
                $slot['description'],
                $slot['trainer'],
                $slot['location'],
                $startAt,
                $endAt,
                $slot['capacity'],
                $id
            ]);
            flash('Créneau modifié.');
        } else {
            $stmt = db()->prepare('
                INSERT INTO training_slots (title, description, trainer, location, start_at, end_at, capacity, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $slot['title'],
                $slot['description'],
                $slot['trainer'],
                $slot['location'],
                $startAt,
                $endAt,
                $slot['capacity'],
                $user['id']
            ]);
            flash('Créneau créé.');
        }

        redirect('admin_formations.php');
    }
}

function datetime_input_value(?string $value): string
{
    if (!$value) {
        return '';
    }

    return date('Y-m-d\TH:i', strtotime($value));
}

render_header($id > 0 ? 'Modifier un créneau' : 'Créer un créneau');
?>
<div class="card form-card">
    <h1><?= $id > 0 ? 'Modifier un créneau' : 'Créer un créneau' ?></h1>

    <?php foreach ($errors as $error): ?>
        <div class="alert error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
        <label>Titre</label>
        <input type="text" name="title" value="<?= e($slot['title']) ?>" required>

        <label>Description</label>
        <textarea name="description" rows="7" required><?= e($slot['description']) ?></textarea>

        <label>Intervenant</label>
        <input type="text" name="trainer" value="<?= e($slot['trainer']) ?>">

        <label>Lieu</label>
        <input type="text" name="location" value="<?= e($slot['location']) ?>">

        <div class="grid two compact">
            <div>
                <label>Début</label>
                <input type="datetime-local" name="start_at" value="<?= e(datetime_input_value($slot['start_at'])) ?>" required>
            </div>
            <div>
                <label>Fin</label>
                <input type="datetime-local" name="end_at" value="<?= e(datetime_input_value($slot['end_at'])) ?>" required>
            </div>
        </div>

        <label>Nombre de places</label>
        <input type="number" name="capacity" min="1" value="<?= (int) $slot['capacity'] ?>" required>

        <button class="btn" type="submit">Enregistrer</button>
    </form>
</div>
<?php render_footer(); ?>
