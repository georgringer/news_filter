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

        $this->updateConstraints($params['demand'], $params['respectEnableFields'], $params['query'], $params['constraints']);
    }

    /**
     * @param Demand $demand
     * @param bool $respectEnableFields
     * @param QueryInterface $query
     * @param array $constraints
     */
    protected function updateConstraints(Demand $demand, $respectEnableFields, QueryInterface $query, array &$constraints)
    {

    }
}