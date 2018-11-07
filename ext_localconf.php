<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Domain/Repository/AbstractDemandedRepository.php']['findDemanded'][$_EXTKEY]
    = \GeorgRinger\NewsFilter\Hooks\Repository::class . '->modify';

// Add new controller/action
$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['switchableControllerActions']['newItems']['News->newsFilter'] = 'News Filter';
$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Controller/NewsController'][] = 'news_filter';

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['News']['plugins']['Pi1']['controllers']['News']['actions'][] = 'newsFilter';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['News']['plugins']['Pi1']['controllers']['News']['nonCacheableActions'][] = 'newsFilter';

// For 7x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][]
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;

// For 8x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing'][]
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;