<?php

namespace GeorgRinger\NewsFilter\Hooks;

use TYPO3\CMS\Core\Configuration\Event\AfterFlexFormDataStructureParsedEvent;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FlexFormHook
{
    private const CTYPES = ['*,news_pi1', '*,news_newsliststicky'];

    public function __invoke(AfterFlexFormDataStructureParsedEvent $event): void
    {
        $dataStructure = $event->getDataStructure();
        $identifier = $event->getIdentifier();

        if ($identifier['type'] === 'tca' && $identifier['tableName'] === 'tt_content' && in_array($identifier['dataStructureKey'], self::CTYPES)) {
            $content = file_get_contents($this->getPath());
            if ($content) {
                $dataStructure['sheets']['extraEntryNewsFilter'] = GeneralUtility::xml2array($content);
            }
        }
        $event->setDataStructure($dataStructure);
    }

    /**
     * @param array $dataStructure
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPostProcess(array $dataStructure, array $identifier): array
    {
        if ($identifier['type'] === 'tca' && $identifier['tableName'] === 'tt_content' && in_array($identifier['dataStructureKey'], self::CTYPES)) {
            $content = file_get_contents($this->getPath());
            if ($content) {
                $dataStructure['sheets']['extraEntryNewsFilter'] = GeneralUtility::xml2array($content);
            }
        }
        return $dataStructure;
    }

    protected function getPath(): string
    {
        $file = (new Typo3Version())->getMajorVersion() >= 12 ? 'flexform_newsfilter12.xml' : 'flexform_newsfilter.xml';
        return ExtensionManagementUtility::extPath('news_filter') . 'Configuration/FlexForms/' . $file;
    }
}
