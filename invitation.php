<?php
require_once __DIR__ . '/includes/auth.php';

$slotId = (int) ($_GET['id'] ?? 0);

if ($slotId < 1 || (int) ($_SESSION['calendar_slot_id'] ?? 0) !== $slotId) {
    http_response_code(403);
    exit('Cette invitation calendrier n’est pas disponible.');
}

$stmt = db()->prepare('SELECT id, title, description, location, start_at, end_at FROM training_slots WHERE id = ?');
$stmt->execute([$slotId]);
$slot = $stmt->fetch();

if (!$slot) {
    http_response_code(404);
    exit('Créneau introuvable.');
}

function ics_escape(?string $value): string
{
    $value = str_replace('\\', '\\\\', $value ?? '');
    $value = str_replace(["\r\n", "\r", "\n"], '\\n', $value);
    return str_replace([',', ';'], ['\\,', '\\;'], $value);
}

function ics_fold(string $line): string
{
    $folded = '';
    $limit = 75;

    while (strlen($line) > $limit) {
        $length = $limit;
        while ($length > 0 && (ord($line[$length]) & 0xC0) === 0x80) {
            $length--;
        }

        $folded .= substr($line, 0, $length) . "\r\n ";
        $line = substr($line, $length);
        $limit = 74;
    }

    return $folded . $line;
}

$host = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['HTTP_HOST'] ?? 'localhost') ?: 'localhost';
$filename = 'formation-' . $slotId . '.ics';
$lines = [
    'BEGIN:VCALENDAR',
    'VERSION:2.0',
    'PRODID:-//Plateforme de formations//Invitation//FR',
    'CALSCALE:GREGORIAN',
    'METHOD:PUBLISH',
    'BEGIN:VEVENT',
    'UID:formation-' . $slotId . '@' . $host,
    'DTSTAMP:' . gmdate('Ymd\\THis\\Z'),
    'DTSTART:' . date('Ymd\\THis', strtotime($slot['start_at'])),
    'DTEND:' . date('Ymd\\THis', strtotime($slot['end_at'])),
    'SUMMARY:' . ics_escape($slot['title']),
    'DESCRIPTION:' . ics_escape($slot['description']),
    'LOCATION:' . ics_escape($slot['location']),
    'STATUS:CONFIRMED',
    'END:VEVENT',
    'END:VCALENDAR',
];
$calendar = implode("\r\n", array_map('ics_fold', $lines)) . "\r\n";

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($calendar));
header('X-Content-Type-Options: nosniff');
echo $calendar;
