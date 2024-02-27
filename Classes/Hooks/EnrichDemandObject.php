<?php

declare(strict_types=1);

namespace GeorgRinger\NewsFilter\Hooks;

use GeorgRinger\News\Event\NewsListActionEvent;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\Exception;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

/**
 * EnrichDemandObject
 */
class EnrichDemandObject
{
    /** @var PropertyMapper */
    protected $propertyMapper;

    /**
     * @param PropertyMapper $propertyMapper
     */
    public function __construct(PropertyMapper $propertyMapper)
    {
        $this->propertyMapper = $propertyMapper;
    }

    /**
     * @param array $params
     * @return void
     * @throws Exception
     */
    public function run(array &$params): void
    {
        $demand = $params['demand'];
        if (get_class($demand) !== Demand::class) {
            return;
        }
        $settings = $params['settings'];

        if ($settings['enableFilter'] ?? false) {

            $vars = GeneralUtility::_POST('tx_news_pi1');
            $hasPostData = isset($vars['search']) && is_array($vars['search']);
            if ($hasPostData) {
                // set the demand based on POST data

                /** @var Search $search */
                $search = $this->propertyMapper->convert($vars['search'], Search::class);
                $this->setSearchDemand($settings, $search, $demand);
            } else {
                // set the demand based on session data
                if ($settings['keepFilterOnReload']) {
                    $search = $this->getSearchFromSession();
                    $this->setSearchDemand($settings, $search, $demand);
                }
            }
        }
    }

    /**
     * Load filter from session or create a new empty search
     *
     * @param NewsListActionEvent $event
     * @return Search
     * @throws Exception
     */
    protected function getSearchFromSession(): Search
    {
        $request = $GLOBALS['TYPO3_REQUEST'];
        $frontendUser = $request->getAttribute('frontend.user');
        $sessionFilter = $frontendUser->getKey('ses', 'tx_news_filter');
        if ($sessionFilter) {
            $sessionFilter = unserialize($sessionFilter);
            $search = $this->propertyMapper->convert($sessionFilter, Search::class);
        } else {
            $search = GeneralUtility::makeInstance(Search::class);
        }

        return $search;
    }

    /**
     * @param array $settings
     * @param Search $search
     * @param Demand $demand
     * @return Demand
     */
    protected function setSearchDemand(array $settings, Search $search, Demand $demand): Demand
    {
        $search->setFields($settings['search']['fields']);
        $search->setSplitSubjectWords((bool)$settings['search']['splitSearchWord']);
        $demand->setSearch($search);
        $demand->setFilteredCategories($search->getFilteredCategories());
        $demand->setFilteredTags($search->getFilteredTags());
        $demand->setFromDate($search->getFromDate());
        $demand->setToDate($search->getToDate());

        return $demand;
    }
}
