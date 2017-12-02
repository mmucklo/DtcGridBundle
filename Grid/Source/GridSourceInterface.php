<?php

namespace Dtc\GridBundle\Grid\Source;

use Dtc\GridBundle\Grid\Pager\GridSourcePager;

interface GridSourceInterface
{
    public function setId($id);

    public function getId();

    public function getCount();

    public function getRecords();

    public function getLimit();

    public function getOffset();

    public function getLastModified();

    /**
     * @return array()
     */
    public function getColumns();

    public function getDivId();

    /**
     * @return GridSourcePager
     */
    public function getPager();

    public function getFilter();

    public function getParameters();

    public function getOrderBy();

    public function setColumns($columns);

    public function hasIdColumn();

    public function find($id);

    public function remove($id);

    public function getDefaultSort();
}
