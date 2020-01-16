<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['Domain/Repository/AbstractDemandedRepository.php']['findDemanded'][$_EXTKEY]
    = \GeorgRinger\NewsFilter\Hooks\Repository::class . '->modify';

$vars = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('tx_news_pi1');
if (isset($vars['search']) && is_array($vars['search'])) {
    $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['extbase']['extensions']['News']['plugins']['Pi1']['controllers']['News']['nonCacheableActions'][] =
        $vars['__referrer']['@action'] ?: 'list';
}

$GLOBALS['TYPO3_CONF_VARS']['EXT']['news']['classes']['Domain/Repository/CategoryRepository'][] = $_EXTKEY;

// For 7x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['getFlexFormDSClass'][]
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;

// For 8x
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS'][\TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools::class]['flexParsing'][]
    = \GeorgRinger\NewsFilter\Hooks\FlexFormHook::class;


/** @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher $signalSlotDispatcher */
$signalSlotDispatcher = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Extbase\SignalSlot\Dispatcher::class);
$signalSlotDispatcher->connect(
    \GeorgRinger\News\Controller\NewsController::class,
    'listAction',
    \GeorgRinger\NewsFilter\Slots\NewsControllerSlot::class,
    'listActionSlot',
    true
);
