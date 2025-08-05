<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Filter for news',
    'description' => 'Filter news by categories, tags and time',
    'category' => 'fe',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'typo3' => '11.5.0-12.4.99',
            'news' => '11.0.0-12.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
