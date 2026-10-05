<?php
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/training.php';
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

$descriptionSections = [
    'target_audience' => 'PUBLIC VISÉ',
    'prerequisites' => 'PRÉREQUIS',
    'objectives' => 'OBJECTIFS',
    'content' => 'CONTENU',
    'teaching_methods' => 'MÉTHODES PÉDAGOGIQUES',
];

$sectionValues = array_fill_keys(array_keys($descriptionSections), '');

if ($id > 0) {
    $stmt = db()->prepare('SELECT * FROM training_slots WHERE id = ?');
    $stmt->execute([$id]);
    $found = $stmt->fetch();

    if (!$found) {
        http_response_code(404);
        exit('Créneau introuvable.');
    }

    $slot = $found;

    $extractedDescription = extract_description_sections($slot['description'], $descriptionSections);
    $slot['description'] = $extractedDescription['description'];
    $sectionValues = $extractedDescription['sections'];
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $slot['title'] = trim($_POST['title'] ?? '');
    $slot['description'] = trim($_POST['description'] ?? '');
    $slot['trainer'] = trim($_POST['trainer'] ?? '');
    $slot['location'] = trim($_POST['location'] ?? '');
    $slot['start_at'] = submitted_datetime_value($_POST['start_date'] ?? '', $_POST['start_time'] ?? '');
    $slot['end_at'] = submitted_datetime_value($_POST['end_date'] ?? '', $_POST['end_time'] ?? '');
    $slot['capacity'] = (int) ($_POST['capacity'] ?? 0);

    foreach ($descriptionSections as $field => $heading) {
        $sectionValues[$field] = trim($_POST[$field] ?? '');
    }

    if ($slot['title'] === '' || $slot['description'] === '' || $slot['start_at'] === '' || $slot['end_at'] === '') {
        $errors[] = 'Le titre, la description, la date de début et la date de fin sont obligatoires.';
    }

    if (($slot['start_at'] !== '' && !is_half_hour_datetime($slot['start_at']))
        || ($slot['end_at'] !== '' && !is_half_hour_datetime($slot['end_at']))) {
        $errors[] = 'Les heures de début et de fin doivent être choisies par tranches de 30 minutes.';
    }

    if ($slot['capacity'] < 1) {
        $errors[] = 'La capacité doit être supérieure à 0.';
    }

    if (!$errors) {
        $startAt = str_replace('T', ' ', $slot['start_at']) . ':00';
        $endAt = str_replace('T', ' ', $slot['end_at']) . ':00';

        $slot['description'] = append_description_sections($slot['description'], $descriptionSections, $sectionValues);

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

function submitted_datetime_value(string $date, string $time): string
{
    if ($date === '' || $time === '') {
        return '';
    }

    return $date . 'T' . $time;
}

function datetime_date_value(?string $value): string
{
    $datetime = datetime_input_value($value);

    return $datetime === '' ? '' : substr($datetime, 0, 10);
}

function datetime_time_value(?string $value): string
{
    $datetime = datetime_input_value($value);

    return $datetime === '' ? '' : substr($datetime, 11, 5);
}

function render_time_options(?string $selectedTime): void
{
    echo '<option value="">Choisir une heure</option>';

    foreach (half_hour_times() as $time) {
        $selected = $time === $selectedTime ? ' selected' : '';
        echo '<option value="' . e($time) . '"' . $selected . '>' . e($time) . '</option>';
    }
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

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="7" required><?= e($slot['description']) ?></textarea>

        <fieldset class="description-details">
            <legend>Informations complémentaires</legend>
            <p class="small">Ces champs sont facultatifs. Seules les rubriques renseignées seront ajoutées à la description du créneau.</p>

            <label for="target_audience">Public visé</label>
            <textarea id="target_audience" name="target_audience" rows="3"><?= e($sectionValues['target_audience']) ?></textarea>

            <label for="prerequisites">Prérequis</label>
            <textarea id="prerequisites" name="prerequisites" rows="3"><?= e($sectionValues['prerequisites']) ?></textarea>

            <label for="objectives">Objectifs</label>
            <textarea id="objectives" name="objectives" rows="4"><?= e($sectionValues['objectives']) ?></textarea>

            <label for="content">Contenu</label>
            <textarea id="content" name="content" rows="5"><?= e($sectionValues['content']) ?></textarea>

            <label for="teaching_methods">Méthodes pédagogiques</label>
            <textarea id="teaching_methods" name="teaching_methods" rows="4"><?= e($sectionValues['teaching_methods']) ?></textarea>
        </fieldset>

        <label>Intervenant</label>
        <input type="text" name="trainer" value="<?= e($slot['trainer']) ?>">

        <label>Lieu</label>
        <input type="text" name="location" value="<?= e($slot['location']) ?>">

        <div class="grid two compact datetime-range">
            <fieldset class="datetime-field">
                <legend>Début</legend>
                <div class="grid datetime-parts compact">
                    <div>
                        <label for="start_date">Date</label>
                        <input type="date" id="start_date" name="start_date" value="<?= e(datetime_date_value($slot['start_at'])) ?>" required>
                    </div>
                    <div>
                        <label for="start_time">Heure</label>
                        <select id="start_time" name="start_time" required>
                            <?php render_time_options(datetime_time_value($slot['start_at'])); ?>
                        </select>
                    </div>
                </div>
            </fieldset>
            <fieldset class="datetime-field">
                <legend>Fin</legend>
                <div class="grid datetime-parts compact">
                    <div>
                        <label for="end_date">Date</label>
                        <input type="date" id="end_date" name="end_date" value="<?= e(datetime_date_value($slot['end_at'])) ?>" required>
                    </div>
                    <div>
                        <label for="end_time">Heure</label>
                        <select id="end_time" name="end_time" required>
                            <?php render_time_options(datetime_time_value($slot['end_at'])); ?>
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
        <p class="small datetime-help">Les horaires sont proposés uniquement à l’heure pile ou à la demi-heure.</p>

        <label>Nombre de places</label>
        <input type="number" name="capacity" min="1" value="<?= (int) $slot['capacity'] ?>" required>

        <button class="btn" type="submit">Enregistrer</button>
    </form>
</div>
<?php render_footer(); ?>
