<?php

namespace GeorgRinger\NewsFilter\Controller;


use GeorgRinger\News\Controller\NewsController;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Demand;
use GeorgRinger\NewsFilter\Domain\Model\Dto\Search;

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

        $newsItems = $this->newsRepository->findDemanded($demand);

        $this->view->assignMultiple([
            'search' => $search,
            'demand' => $demand,
            'news' => $newsItems
        ]);
    }

}