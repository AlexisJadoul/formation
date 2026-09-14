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
