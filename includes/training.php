<?php

function is_half_hour_datetime(string $value): bool
{
    if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:(?:00|30)$/', $value) !== 1) {
        return false;
    }

    $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);

    return $date !== false && $date->format('Y-m-d\TH:i') === $value;
}

function half_hour_times(): array
{
    $times = [];

    for ($hour = 0; $hour < 24; $hour++) {
        $times[] = sprintf('%02d:00', $hour);
        $times[] = sprintf('%02d:30', $hour);
    }

    return $times;
}

function append_description_sections(string $description, array $sections, array $values): string
{
    $descriptionParts = [trim($description)];

    foreach ($sections as $field => $heading) {
        $value = trim($values[$field] ?? '');
        if ($value !== '') {
            $descriptionParts[] = $heading . "\n" . $value;
        }
    }

    return implode("\n\n", $descriptionParts);
}

function extract_description_sections(string $description, array $sectionHeadings): array
{
    $values = array_fill_keys(array_keys($sectionHeadings), '');
    $headingFields = [];

    foreach ($sectionHeadings as $field => $heading) {
        $headingFields[mb_strtoupper(trim($heading), 'UTF-8')] = $field;
    }

    $descriptionLines = [];
    $currentField = null;
    $sectionLines = [];

    $storeCurrentSection = static function () use (&$values, &$currentField, &$sectionLines): void {
        if ($currentField !== null) {
            $values[$currentField] = trim(implode("\n", $sectionLines));
        }

        $sectionLines = [];
    };

    foreach (preg_split('/\R/', trim($description)) as $line) {
        $normalizedLine = mb_strtoupper(rtrim(trim($line), ':'), 'UTF-8');

        if (isset($headingFields[$normalizedLine])) {
            $storeCurrentSection();
            $currentField = $headingFields[$normalizedLine];
            continue;
        }

        if ($currentField === null) {
            $descriptionLines[] = $line;
        } else {
            $sectionLines[] = $line;
        }
    }

    $storeCurrentSection();

    return [
        'description' => trim(implode("\n", $descriptionLines)),
        'sections' => $values,
    ];
}

function parse_training_description(string $description, array $sectionHeadings): array
{
    $sections = [];
    $currentHeading = null;
    $currentLines = [];

    $storeSection = static function () use (&$sections, &$currentHeading, &$currentLines): void {
        $content = trim(implode("\n", $currentLines));

        if ($content !== '') {
            $sections[] = [
                'heading' => $currentHeading,
                'lines' => preg_split('/\R/', $content),
            ];
        }

        $currentLines = [];
    };

    $normalizedHeadings = [];
    foreach ($sectionHeadings as $heading) {
        $normalizedHeadings[mb_strtoupper(trim($heading), 'UTF-8')] = $heading;
    }

    foreach (preg_split('/\R/', trim($description)) as $line) {
        $trimmedLine = trim($line);
        $normalizedLine = mb_strtoupper(rtrim($trimmedLine, ':'), 'UTF-8');

        if (isset($normalizedHeadings[$normalizedLine])) {
            $storeSection();
            $currentHeading = $normalizedHeadings[$normalizedLine];
            continue;
        }

        $currentLines[] = $line;
    }

    $storeSection();

    return $sections;
}
