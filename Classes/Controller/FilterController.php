<?php

namespace GeorgRinger\NewsFilter\Controller;


use GeorgRinger\News\Controller\NewsController;
use GeorgRinger\News\Utility\Page;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class FilterController extends NewsController
{

    /**
     * @param \GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search
     */
    public function formAction(\GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search = null)
    {
        if (null === $search) {
            $search = $this->objectManager->get(Search::class);
        }

        $this->view->assignMultiple([
            'search' => $search,
        ]);
        $this->initializeForm();
    }

    /**
     * @param \GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search
     */
    public function resultAction(\GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search = null)
    {
        if (null === $search) {
            $search = $this->objectManager->get(Search::class);
        }

        $demand = $this->objectManager->get(Demand::class);
        $demand->setStoragePage(Page::extendPidListByChildren($this->settings['startingpoint']));
        $demand->setCategories(explode(',', $this->settings['categories']));

        $demand->setFilteredCategories($search->getFilteredCategories());
        $demand->setFilteredTags($search->getFilteredTags());
        $demand->setFromDate($search->getFromDate());
        $demand->setToDate($search->getToDate());

        $newsItems = $this->newsRepository->findDemanded($demand);

        $this->view->assignMultiple([
            'search' => $search,
            'demand' => $demand,
            'news' => $newsItems
        ]);
        $this->initializeForm();
    }

    protected function initializeForm()
    {
        $categories = $this->getAllRecordsByPid('sys_category', $this->settings['categories']);
        if (!empty($categories)) {
            $this->view->assign('categories', $this->categoryRepository->findByIdList($categories));
        }
        $tags = $this->getAllRecordsByPid('tx_news_domain_model_tag', $this->settings['tags']);
        if (!empty($tags)) {
            $this->view->assign('tags', $this->tagRepository->findByIdList($tags));
        }
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
                        $queryBuilder->createNamedParameter(explode(',', $pidList), Connection::PARAM_INT_ARRAY)
                    )
                )
                ->execute()
                ->fetchAll();
        } else {
            /** @var DatabaseConnection $db */
            $db = $GLOBALS['TYPO3_DB'];
            $where = 'pid IN(' . $db->cleanIntList($pidList) . ')';
            $rows = $db->exec_SELECTgetRows('uid', $tableName, $where);
        }

        foreach ($rows as $row) {
            $list[] = $row['uid'];
        }
        return $list;
    }

}