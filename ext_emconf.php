<?php

$EM_CONF[$_EXTKEY] = [
    'title' => '[clickstorm] Image Point',
    'description' => '[clickstorm] Image Point provides a new render type with a wizard for determining coordinates on an image.
    Coordinates can be set on the image of a data record (e.g. content element, page, ...), which are then output as percentages.',
    'category' => 'be',
    'author' => 'Noah Buchterkirchen, Marc Hirdes - clickstorm GmbH',
    'author_email' => 'hirdes@clickstorm.de',
    'author_company' => 'clickstorm GmbH',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-11.99.99',
        ],
    ],
    'autoload' => [
        'psr-4' => [
            'Clickstorm\\CsImagePoint\\' => 'Classes',
        ],
    ],
];
