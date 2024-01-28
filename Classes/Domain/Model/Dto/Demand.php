<?php

declare(strict_types=1);

namespace GeorgRinger\NewsFilter\Domain\Model\Dto;

use GeorgRinger\News\Domain\Model\Dto\NewsDemand;

class Demand extends NewsDemand
{
    /** @var string */
    protected string $fromDate = '';

    /** @var string */
    protected string $toDate = '';

    /** @var array */
    protected array $filteredTags = [];

    /** @var array */
    protected array $filteredCategories = [];

    public function getFromDate(): string
    {
        return $this->fromDate;
    }

    public function setFromDate(string $fromDate): void
    {
        $this->fromDate = $fromDate;
    }

    public function getToDate(): string
    {
        return $this->toDate;
    }

    public function setToDate(string $toDate): void
    {
        $this->toDate = $toDate;
    }

    public function getFilteredTags(): array
    {
        return $this->filteredTags;
    }

    public function setFilteredTags(array $filteredTags)
    {
        $this->filteredTags = $filteredTags;
    }

    public function getFilteredCategories(): array
    {
        return $this->filteredCategories;
    }

    public function setFilteredCategories(array $filteredCategories): void
    {
        $this->filteredCategories = $filteredCategories;
    }
}
