<?php

namespace GeorgRinger\NewsFilter\Controller;


class NewsController extends \GeorgRinger\News\Controller\NewsController
{

    /**
     * @param \GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search
     */
    public function newsFilterAction(\GeorgRinger\NewsFilter\Domain\Model\Dto\Search $search = null)
    {
        if (null === $search) {
            $search = $this->objectManager->get(\GeorgRinger\NewsFilter\Domain\Model\Dto\Search::class);
        } else {
            $demand = $this->objectManager->get(\GeorgRinger\NewsFilter\Domain\Model\Dto\Demand::class);
            $demand->setStoragePage(\GeorgRinger\News\Utility\Page::extendPidListByChildren($this->settings['startingpoint']));
            $demand->setCategories(explode(',', $this->settings['categories']));

            $demand->setFilteredCategories($search->getFilteredCategories());
            $demand->setFilteredTags($search->getFilteredTags());
            $demand->setFromDate($search->getFromDate());
            $demand->setToDate($search->getToDate());

            $newsItems = $this->newsRepository->findDemanded($demand);
        }

        $this->view->assignMultiple([
            'search' => $search,
            'demand' => $demand,
            'news' => $newsItems
        ]);
        $this->initializeForm();
    }


    protected function initializeForm()
    {
        $categories = $this->getAllRecordsByPid('sys_category', $this->settings['filterCategories']);
        if (!empty($categories)) {
            $this->view->assign('categories', $this->categoryRepository->findByIdList($categories));
        }
        $tags = $this->getAllRecordsByPid('tx_news_domain_model_tag', $this->settings['filterTags']);
        if (!empty($tags)) {
            $this->view->assign('tags', $this->tagRepository->findByIdList($tags));
        }
    }

    protected function getAllRecordsByPid(string $tableName, string $pidList): array
    {
        $list = [];
        if (class_exists(\TYPO3\CMS\Core\Database\ConnectionPool::class)) {
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable($tableName);
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

}