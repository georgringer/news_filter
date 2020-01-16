<?php

namespace GeorgRinger\NewsFilter\Hooks;

class FlexFormHook
{

    const PATH = 'typo3conf/ext/news_filter/Configuration/FlexForms/flexform_newsfilter.xml';

    // For 7x

    /**
     * @param array $dataStructArray
     * @param array $conf
     * @param array $row
     * @param string $table
     */
    public function getFlexFormDS_postProcessDS(&$dataStructArray, $conf, $row, $table)
    {
        if ($table === 'tt_content' && $row['CType'] === 'list' && $row['list_type'] === 'news_pi1') {
            $dataStructArray['sheets']['extraEntry'] = self::PATH;
        }
    }

    // For 8x

    /**
     * @param array $dataStructure
     * @param array $identifier
     * @return array
     */
    public function parseDataStructureByIdentifierPostProcess(array $dataStructure, array $identifier): array
    {
        if ($identifier['type'] === 'tca' && $identifier['tableName'] === 'tt_content' && $identifier['dataStructureKey'] === 'news_pi1,list') {
            $file = PATH_site . self::PATH;
            $content = file_get_contents($file);
            if ($content) {
                $dataStructure['sheets']['news_filter'] = \TYPO3\CMS\Core\Utility\GeneralUtility::xml2array($content);
            }
        }
        return $dataStructure;
    }
}