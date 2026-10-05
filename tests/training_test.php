<?php

require_once __DIR__ . '/../includes/training.php';

function expect_same($expected, $actual, string $message): void
{
    if ($expected !== $actual) {
        throw new RuntimeException($message . '\nAttendu : ' . var_export($expected, true) . '\nObtenu : ' . var_export($actual, true));
    }
}

$headings = [
    'target_audience' => 'PUBLIC VISÉ',
    'prerequisites' => 'PRÉREQUIS',
    'objectives' => 'OBJECTIFS',
    'content' => 'CONTENU',
    'teaching_methods' => 'MÉTHODES PÉDAGOGIQUES',
];

$description = "Présentation de la formation.\n\nPUBLIC VISÉ\nAgents\n\nOBJECTIFS:\nComprendre le RGAA\nAppliquer les règles\n\nCONTENU\nAtelier pratique";
$result = extract_description_sections($description, $headings);

expect_same('Présentation de la formation.', $result['description'], 'La description principale doit être isolée.');
expect_same('Agents', $result['sections']['target_audience'], 'Le public visé doit être restauré.');
expect_same('', $result['sections']['prerequisites'], 'Un champ absent doit rester vide.');
expect_same("Comprendre le RGAA\nAppliquer les règles", $result['sections']['objectives'], 'Les objectifs doivent être restaurés.');
expect_same('Atelier pratique', $result['sections']['content'], 'Le contenu doit être restauré.');
expect_same('', $result['sections']['teaching_methods'], 'Un champ absent doit rester vide.');

$rebuilt = append_description_sections($result['description'], $headings, $result['sections']);
$expectedRebuilt = str_replace('OBJECTIFS:', 'OBJECTIFS', $description);
expect_same($expectedRebuilt, $rebuilt, 'La sauvegarde ne doit ni déplacer ni dupliquer les rubriques.');

$secondExtraction = extract_description_sections($rebuilt, $headings);
expect_same($result, $secondExtraction, 'Une nouvelle ouverture doit conserver chaque valeur dans son champ.');

echo "Tests de description réussis.\n";
