<?php
declare(strict_types=1);

namespace GeorgRinger\NewsFilter\Hooks;

use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

class EnrichDemandObject
{
    public function run(array &$params): void
    {
        $demand = $params['demand'];
        if (get_class($demand) !== Demand::class) {
            return;
        }
        $settings = $params['settings'];
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);


        if ($settings['enableFilter']) {
            $vars = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('tx_news_pi1');
            if (isset($vars['search']) && is_array($vars['search'])) {
                /** @var Search $search */
                $search = $objectManager->get(PropertyMapper::class)->convert($vars['search'], Search::class);

                $demand->setFilteredCategories($search->getFilteredCategories());
                $demand->setFilteredTags($search->getFilteredTags());
                $demand->setFromDate($search->getFromDate());
                $demand->setToDate($search->getToDate());

            }
        }
    }
}
