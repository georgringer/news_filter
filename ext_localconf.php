<?php

if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Domain/Repository/AbstractDemandedRepository.php']['findDemanded']['news_filter']
    = \GeorgRinger\NewsFilter\Hooks\Repository::class . '->modify';

$vars = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('tx_news_pi1');
if (isset($vars['search']) && is_array($vars['search'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['News']['plugins']['Pi1']['controllers'][\GeorgRinger\News\Controller\NewsController::class]['nonCacheableActions'][] = 'list';
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Repository/CategoryRepository'][] = 'news_filter';

// For 7x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass']['news_filter']
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;

// For 8x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing']['news_filter']
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Controller/NewsController.php']['createDemandObjectFromSettings']['news_filter']
 = \GeorgRinger\NewsFilter\Hooks\EnrichDemandObject::class . '->run';
