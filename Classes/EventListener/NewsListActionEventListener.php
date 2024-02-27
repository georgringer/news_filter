<?php

declare(strict_types=1);

namespace GeorgRinger\NewsFilter\EventListener;

use GeorgRinger\News\Domain\Model\Dto\NewsDemand;
use GeorgRinger\News\Domain\Repository\CategoryRepository;
use GeorgRinger\News\Domain\Repository\TagRepository;
use GeorgRinger\News\Event\NewsListActionEvent;
use GeorgRinger\News\Utility\Page;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\PropertyMapper;

class NewsListActionEventListener
{
    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var TagRepository */
    protected $tagRepository;

    /** @var PropertyMapper */
    protected $propertyMapper;

    public function __construct(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PropertyMapper $propertyMapper
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->propertyMapper = $propertyMapper;
    }

    public function __invoke(NewsListActionEvent $event)
    {
        $data = $event->getAssignedValues();
        $settings = $data['settings'];

        if ($settings['enableFilter'] ?? false) {
            $search = GeneralUtility::makeInstance(Search::class);

            $vars = GeneralUtility::_POST('tx_news_pi1');
            if (isset($vars['search']) && is_array($vars['search'])) {
                /** @var Search $search */
                $search = $this->propertyMapper->convert($vars['search'], Search::class);
            }

            $extended = [
                'currentDate' => GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'timestamp'),
                'searchDemand' => $search,
            ];

            $categories2 = $this->getAllRecordsByPid('sys_category', $settings['filterCategories']);
            if (!empty($categories2)) {
                if ($settings['filterCategoriesOrderBy'] ?? false) {
                    $categories2order = [
                        $settings['filterCategoriesOrderBy'] => strtoupper($settings['filterCategoriesOrderDirection'] ?? 'ASC'),
                    ];
                } else {
                    $categories2order = [];
                }
                $extended['categories'] = $this->categoryRepository->findByIdListWithLanguageSupport($categories2, $categories2order);
            }

            $tags2 = $this->getAllRecordsByPid('tx_news_domain_model_tag', $settings['filterTags']);
            if (!empty($tags2)) {
                if ($settings['filterTagsOrderBy'] ?? false) {
                    $tags2order = [
                        $settings['filterTagsOrderBy'] => strtoupper($settings['filterTagsOrderDirection'] ?? 'ASC'),
                    ];
                } else {
                    $tags2order = [];
                }
                $extended['tags'] = $this->tagRepository->findByIdList($tags2, $tags2order);
            }

            $data['extendedVariables'] = $extended;
        }

        $event->setAssignedValues($data);
    }

    protected function getAllRecordsByPid(string $tableName, string $pidList): array
    {
        $list = [];

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        $rows = $queryBuilder
            ->select('uid')
            ->from($tableName)
            ->where(
                $queryBuilder->expr()->in(
                    'pid',
                    $queryBuilder->createNamedParameter(explode(',', $pidList), Connection::PARAM_INT_ARRAY)
                )
            )
            ->execute()
            ->fetchAll();

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
     * @return NewsDemand
     */
    protected function createDemandObjectFromSettings(
        $settings,
        $class = 'GeorgRinger\\News\\Domain\\Model\\Dto\\NewsDemand'
    ) {
        $class = isset($settings['demandClass']) && !empty($settings['demandClass']) ? $settings['demandClass'] : $class;

        /* @var $demand \GeorgRinger\News\Domain\Model\Dto\NewsDemand */
        $demand = GeneralUtility::makeInstance($class, $settings);
        if (!$demand instanceof NewsDemand) {
            throw new \UnexpectedValueException(
                sprintf(
                    'The demand object must be an instance of \GeorgRinger\\News\\Domain\\Model\\Dto\\NewsDemand, but %s given!',
                    $class
                ),
                1423157953
            );
        }

        $demand->setCategories(GeneralUtility::trimExplode(',', $settings['categories'], true));
        $demand->setCategoryConjunction($settings['categoryConjunction']);
        $demand->setIncludeSubCategories((bool)$settings['includeSubCategories']);
        $demand->setTags($settings['tags']);

        $demand->setTopNewsRestriction((int)$settings['topNewsRestriction']);
        $demand->setTimeRestriction($settings['timeRestriction']);
        $demand->setTimeRestrictionHigh($settings['timeRestrictionHigh']);
        $demand->setArchiveRestriction($settings['archiveRestriction']);
        $demand->setExcludeAlreadyDisplayedNews((bool)$settings['excludeAlreadyDisplayedNews']);
        $demand->setHideIdList((string)($settings['hideIdList'] ?? ''));

        if ($settings['orderBy']) {
            $demand->setOrder($settings['orderBy'] . ' ' . $settings['orderDirection']);
        }
        $demand->setOrderByAllowed($settings['orderByAllowed']);

        $demand->setTopNewsFirst((bool)$settings['topNewsFirst']);

        $demand->setLimit((int)$settings['limit']);
        $demand->setOffset((int)$settings['offset']);

        $demand->setSearchFields($settings['search']['fields']);
        $demand->setDateField($settings['dateField']);
        $demand->setMonth((int)$settings['month']);
        $demand->setYear((int)$settings['year']);

        $demand->setStoragePage(Page::extendPidListByChildren(
            $settings['startingpoint'],
            $settings['recursive']
        ));
        return $demand;
    }
}
