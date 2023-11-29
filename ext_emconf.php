<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Filter for news',
    'description' => 'Filter news by categories, tags and time',
    'category' => 'fe',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'version' => '2.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-12.4.99',
            'news' => '9.0.0-11.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
