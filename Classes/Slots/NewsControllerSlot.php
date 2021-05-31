<?php

namespace GeorgRinger\NewsFilter\Slots;


use GeorgRinger\News\Domain\Repository\CategoryRepository;
use GeorgRinger\News\Domain\Repository\NewsRepository;
use GeorgRinger\News\Domain\Repository\TagRepository;
use GeorgRinger\News\Utility\Page;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

class NewsControllerSlot
{

    /** @var ObjectManager */
    protected $objectManager;

    public function __construct()
    {
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

    }

    public function listActionSlot($newsItems, $overwriteDemand, $demand,
                                   $categories, $tags, $settings)
    {

        if ($settings['enableFilter']) {
            $search = $this->objectManager->get(Search::class);

            $vars = \TYPO3\CMS\Core\Utility\GeneralUtility::_POST('tx_news_pi1');
            if (isset($vars['search']) && is_array($vars['search'])) {
                /** @var Search $search */
                $search = $this->objectManager->get(PropertyMapper::class)->convert($vars['search'], Search::class);

                $demand = $this->createDemandObjectFromSettings($settings, Demand::class);
                $demand->setStoragePage(\GeorgRinger\News\Utility\Page::extendPidListByChildren($settings['startingpoint']));
                $demand->setCategories(explode(',', $settings['categories']));

                $demand->setFilteredCategories($search->getFilteredCategories());
                $demand->setFilteredTags($search->getFilteredTags());
                $demand->setFromDate($search->getFromDate());
                $demand->setToDate($search->getToDate());

                $newsRepository = $this->objectManager->get(NewsRepository::class);
                $newsItems = $newsRepository->findDemanded($demand);
            }

            $extended = [
                'currentDate' => $GLOBALS['EXEC_TIME'],
                'searchDemand' => $search,
            ];

            $categories2 = $this->getAllRecordsByPid('sys_category', $settings['filterCategories']);
            if (!empty($categories2)) {
                if (isset($settings['filterCategoriesOrderBy'])) {
                    $categories2order = [
                        $settings['filterCategoriesOrderBy'] => strtoupper($settings['filterCategoriesOrderDirection'] ?? 'ASC'),
                    ];
                } else {
                    $categories2order = [];
                }
                $categoryRepository = $this->objectManager->get(CategoryRepository::class);
                $extended['categories'] = $categoryRepository->findByIdListWithLanguageSupport($categories2, $categories2order);
            }

            $tags2 = $this->getAllRecordsByPid('tx_news_domain_model_tag', $settings['filterTags']);
            if (!empty($tags2)) {
                if (isset($settings['filterTagsOrderBy'])) {
                    $tags2order = [
                        $settings['filterTagsOrderBy'] => strtoupper($settings['filterTagsOrderDirection'] ?? 'ASC'),
                    ];
                } else {
                    $tags2order = [];
                }
                $tagRepository = $this->objectManager->get(TagRepository::class);
                $extended['tags'] = $tagRepository->findByIdList($tags2, $tags2order);
            }
        }

        $data = [
            'news' => $newsItems,
            'overwriteDemand' => $overwriteDemand,
            'demand' => $demand,
            'categories' => $categories,
            'tags' => $tags,
            'settings' => $settings,
            'extendedVariables' => $extended
        ];

        return $data;
    }

    protected function getAllRecordsByPid(string $tableName, string $pidList): array
    {
        $list = [];
        if (class_exists(ConnectionPool::class)) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
            $rows = $queryBuilder
                ->select('uid')
                ->from($tableName)
                ->where(
                    $queryBuilder->expr()->in(
                        'pid',
                        $queryBuilder->createNamedParameter(explode(',', $pidList), \TYPO3\CMS\Core\Database\Connection::PARAM_INT_ARRAY)
                    )
                )
                ->execute()
                ->fetchAll();
        } else {
            /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $db */
            $db = $GLOBALS['TYPO3_DB'];
            $where = 'pid IN(' . $db->cleanIntList($pidList) . ')';
            $rows = $db->exec_SELECTgetRows('uid', $tableName, $where);
        }

        foreach ($rows as $row) {
            $list[] = $row['uid'];
        }
        return $list;
    }

    /**
     * Create the demand object which define which records will get shown
     *
     * @param array $settings
     * @param string $class optional class which must be an instance of \GeorgRinger\News\Domain\Model\Dto\NewsDemand
     * @return \GeorgRinger\News\Domain\Model\Dto\NewsDemand
     */
    protected function createDemandObjectFromSettings(
        $settings,
        $class = 'GeorgRinger\\News\\Domain\\Model\\Dto\\NewsDemand'
    )
    {
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /* @var $demand \GeorgRinger\News\Domain\Model\Dto\NewsDemand */
        $demand = $this->objectManager->get($class, $settings);
        if (!$demand instanceof \GeorgRinger\News\Domain\Model\Dto\NewsDemand) {
            throw new \UnexpectedValueException(
                sprintf('The demand object must be an instance of \GeorgRinger\\News\\Domain\\Model\\Dto\\NewsDemand, but %s given!',
                    $class),
                1423157953);
        }

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction']);
        $demand->setIncludeSubCategories($settings['includeSubCategories']);
        $demand->setTags($settings['tags']);

        $demand->setTopNewsRestriction($settings['topNewsRestriction']);
        $demand->setTimeRestriction($settings['timeRestriction']);
        $demand->setTimeRestrictionHigh($settings['timeRestrictionHigh']);
        $demand->setArchiveRestriction($settings['archiveRestriction']);
        $demand->setExcludeAlreadyDisplayedNews($settings['excludeAlreadyDisplayedNews']);
        $demand->setHideIdList($settings['hideIdList']);

        if ($settings['orderBy']) {
            $demand->setOrder($settings['orderBy'] . ' ' . $settings['orderDirection']);
        }
        $demand->setOrderByAllowed($settings['orderByAllowed']);

        $demand->setTopNewsFirst($settings['topNewsFirst']);

        $demand->setLimit($settings['limit']);
        $demand->setOffset($settings['offset']);

        $demand->setSearchFields($settings['search']['fields']);
        $demand->setDateField($settings['dateField']);
        $demand->setMonth($settings['month']);
        $demand->setYear($settings['year']);

        $demand->setStoragePage(Page::extendPidListByChildren($settings['startingpoint'],
            $settings['recursive']));
        return $demand;
    }

}