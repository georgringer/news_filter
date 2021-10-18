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
            'typo3' => '8.7.13-10.4.99',
            'news' => '7.0.0-9.99.99',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
