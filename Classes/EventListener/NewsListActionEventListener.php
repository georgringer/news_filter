<?php

declare(strict_types=1);

namespace GeorgRinger\NewsFilter\EventListener;

use Doctrine\DBAL\DBALException;
use GeorgRinger\News\Domain\Repository\CategoryRepository;
use GeorgRinger\News\Domain\Repository\TagRepository;
use GeorgRinger\News\Event\NewsListActionEvent;
use GeorgRinger\News\Utility\Page;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Property\Exception;
use TYPO3\CMS\Extbase\Property\PropertyMapper;
use TYPO3\CMS\Extbase\Service\CacheService;

/**
 * NewsListActionEventListener
 */
class NewsListActionEventListener
{
    /** @var CategoryRepository */
    protected $categoryRepository;

    /** @var TagRepository */
    protected $tagRepository;

    /** @var PropertyMapper */
    protected $propertyMapper;

    /** @var CacheService */
    protected $cacheService;

    public function __construct(
        CategoryRepository $categoryRepository,
        TagRepository $tagRepository,
        PropertyMapper $propertyMapper,
        CacheService $cacheService
    ) {
        $this->categoryRepository = $categoryRepository;
        $this->tagRepository = $tagRepository;
        $this->propertyMapper = $propertyMapper;
        $this->cacheService = $cacheService;
    }

    /**
     * @throws Exception
     * @throws AspectNotFoundException
     */
    public function __invoke(NewsListActionEvent $event): void
    {
        $data = $event->getAssignedValues();
        $settings = $data['settings'];

        if ($settings['enableFilter'] ?? false) {

            $search = $settings['keepFilterOnReload'] ?
                $this->getSearchFromSession($event) :
                GeneralUtility::makeInstance(Search::class);

            // set a new search if a new search was submitted
            $vars = GeneralUtility::_POST('tx_news_pi1');
            $hasPostData = isset($vars['search']) && is_array($vars['search']);
            if ($hasPostData) {

                if ($settings['keepFilterOnReload']) {
                    $this->saveSearchInSession($event, $vars['search']);

                    // clear teh cache for the current page when a
                    // new filter is saved to the session in order
                    // to get the new filter after reloading the page
                    $currentPage = $GLOBALS['TSFE']->id;
                    $this->cacheService->clearPageCache([$currentPage]);
                }

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

    /**
     * @param string $tableName
     * @param string $pidList
     * @return array
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws DBALException
     */
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
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($rows as $row) {
            $list[] = $row['uid'];
        }
        return $list;
    }

    /**
     * Load filter from session or create a new empty search
     *
     * @param NewsListActionEvent $event
     * @return Search
     * @throws Exception
     */
    protected function getSearchFromSession(NewsListActionEvent $event): Search
    {
        $request = $event->getRequest();
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
     * serialize and save search in session
     *
     * @param NewsListActionEvent $event
     * @param array $search
     * @return void
     */
    protected function saveSearchInSession(NewsListActionEvent $event, array $search): void
    {
        $request = $event->getRequest();
        $frontendUser = $request->getAttribute('frontend.user');
        $sessionData = serialize($search);
        $frontendUser->setKey('ses', 'tx_news_filter', $sessionData);
    }
}
