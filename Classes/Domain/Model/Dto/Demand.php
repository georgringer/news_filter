<?php

namespace GeorgRinger\NewsFilter\Domain\Model\Dto;

use GeorgRinger\News\Domain\Model\Dto\NewsDemand;

class Demand extends NewsDemand
{

    /** @var string */
    protected $fromDate = '';

    /** @var string */
    protected $toDate = '';

    /** @var array */
    protected $filteredTags = [];

    /** @var array */
    protected $filteredCategories = [];

    /**
     * @return string
     */
    public function getFromDate(): string
    {
        return $this->fromDate;
    }

    /**
     * @param string $fromDate
     */
    public function setFromDate(string $fromDate)
    {
        $this->fromDate = $fromDate;
    }

    /**
     * @return string
     */
    public function getToDate(): string
    {
        return $this->toDate;
    }

    /**
     * @param string $toDate
     */
    public function setToDate(string $toDate)
    {
        $this->toDate = $toDate;
    }

    /**
     * @return array
     */
    public function getFilteredTags(): array
    {
        return $this->filteredTags;
    }

    /**
     * @param array $filteredTags
     */
    public function setFilteredTags(array $filteredTags)
    {
        $this->filteredTags = $filteredTags;
    }

    /**
     * @return array
     */
    public function getFilteredCategories(): array
    {
        return $this->filteredCategories;
    }

    /**
     * @param array $filteredCategories
     */
    public function setFilteredCategories(array $filteredCategories)
    {
        $this->filteredCategories = $filteredCategories;
    }


}