<?php

namespace Dtc\GridBundle\Grid\Pager;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridSourcePager extends Pager
{
    private $gridSource;
    private $totalPages;

    public function __construct(GridSourceInterface $gridSource)
    {
        $this->gridSource = $gridSource;
    }

    public function getCurrentPage()
    {
        $limit = $this->gridSource->getLimit();
        $offset = $this->gridSource->getOffset();

        if (!$limit) {
            return $offset;
        }

        return ceil(($offset / $limit) + 1);
    }

    public function getTotalPages()
    {
        $limit = $this->gridSource->getLimit();
        if (!$limit) {
            return $this->gridSource->getCount();
        }

        return ceil($this->gridSource->getCount() / $limit);
    }

    public function getRange($delta)
    {
    }
}
