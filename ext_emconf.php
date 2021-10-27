<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Filter for news',
    'description' => 'Filter news by categories, tags and time',
    'category' => 'fe',
    'author' => 'Georg Ringer',
    'author_email' => 'mail@ringer.it',
    'state' => 'beta',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.0-11.4.99',
            'news' => '9.0.0-10.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
