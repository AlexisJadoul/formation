<?php

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
