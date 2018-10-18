<?php

namespace GeorgRinger\NewsFilter\Hooks;

use GeorgRinger\News\Domain\Repository\NewsRepository;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class Repository
{

    public function modify(array $params, NewsRepository $newsRepository)
    {
        if (\get_class($params['demand']) !== Demand::class) {
            return;
        }

        $this->updateConstraints($params['demand'], $params['query'], $params['constraints']);
    }

    /**
     * @param Demand $demand
     * @param bool $respectEnableFields
     * @param QueryInterface $query
     * @param array $constraints
     */
    protected function updateConstraints(Demand $demand, QueryInterface $query, array &$constraints)
    {
        // dates
        $dateField = 'datetime';
        $dateFrom = $demand->getFromDate();
        if ($dateFrom) {
            $date = strtotime($dateFrom);
            if ($date) {
                $constraints[] = $query->greaterThanOrEqual($dateField, $date);
            }
        }
        $dateTo = $demand->getToDate();
        if ($dateTo) {
            $date = strtotime($dateTo);
            if ($date) {
                $date += 86400;
                $constraints[] = $query->lessThanOrEqual($dateField, $date);
            }
        }

        // categories
        $categories = $demand->getFilteredCategories();
        if (!empty($categories)) {
            $categoryConstraint = [];
            foreach ($categories as $category) {
                $categoryConstraint[] = $query->contains('categories', $category);
            }
            $constraints['filteredCategories'] = $query->logicalOr($categoryConstraint);
        }

        // tags
        $tags = $demand->getFilteredTags();
        if (!empty($tags)) {
            $tagConstraint = [];
            foreach ($tags as $tag) {
                $tagConstraint[] = $query->contains('tags', $tag);
            }
            $constraints['filteredTags'] = $query->logicalOr($tagConstraint);
        }

    }
}