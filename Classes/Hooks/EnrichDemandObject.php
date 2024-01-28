<?php

declare(strict_types=1);

namespace GeorgRinger\NewsFilter\Hooks;

use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

class EnrichDemandObject
{
    /** @var PropertyMapper */
    protected $propertyMapper;

    public function __construct(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    public function run(array &$params): void
    {
        $demand = $params['demand'];
        if (get_class($demand) !== Demand::class) {
            return;
        }
        $settings = $params['settings'];

        if ($settings['enableFilter'] ?? false) {
            $vars = GeneralUtility::_POST('tx_news_pi1');
            if (isset($vars['search']) && is_array($vars['search'])) {
                /** @var Search $search */
                $search = $this->propertyMapper->convert($vars['search'], Search::class);
                $search->setFields($settings['search']['fields']);
                $search->setSplitSubjectWords((bool)$settings['search']['splitSearchWord']);
                $demand->setSearch($search);
                $demand->setFilteredCategories($search->getFilteredCategories());
                $demand->setFilteredTags($search->getFilteredTags());
                $demand->setFromDate($search->getFromDate());
                $demand->setToDate($search->getToDate());
            }
        }
    }
}
