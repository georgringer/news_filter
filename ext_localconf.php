<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'GeorgRinger.' . $_EXTKEY,
    'Filter',
    [
        'Filter' => 'form,result'
    ],
    [
        'Filter' => 'form,result'
    ]
);

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Domain/Repository/AbstractDemandedRepository.php']['findDemanded'][$_EXTKEY]
    = \GeorgRinger\NewsFilter\Hooks\Repository::class . '->modify';
