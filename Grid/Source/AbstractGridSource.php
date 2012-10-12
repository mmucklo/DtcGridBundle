<?php
namespace Dtc\GridBundle\Grid\Source;

use Dtc\GridBundle\Grid\Pager\GridSourcePager;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractGridSource implements GridSourceInterface
{
    protected $limit = 25;
    protected $offset = 0;
    protected $filter = array();
    protected $orderBy = array();
    protected $pager = array();
    protected $id = 'grid';
    protected $columns;

    public function bind(Request $request)
    {
        // Change limit, offset
        if ($limit = $request->get('limit'))
        {
            $this->limit = $limit;
        }

        if ($page = $request->get('page'))
        {
            $this->offset = $this->limit * ($page - 1);
        }

        if ($offset = $request->get('offset'))
        {
            $this->offset = $offset;
        }

        if ($filter = $request->get('filter')) {
            $this->filter = $filter;
        }

        if ($sortColumn = $request->get('sort_column'))
        {
            $sortOrder = $request->get('sort_order');
            $sortOrder = strtoupper($sortOrder);

            $this->orderBy[$sortColumn] = $sortOrder;
        }

        if ($orderBy = $request->get('order'))
        {
            $this->orderBy = $orderBy;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    private $divId = null;

    public function getDivId()
    {
        if (!$this->divId)
        {
            $this->divId = preg_replace('/[^a-zA-Z0-9\-]/', '', $this->id);
        }

        return $this->divId;
    }

    public function setId($value)
    {
        $this->id = $value;
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function setColumns($value)
    {
        $this->columns = $value;
    }

    public function removeColumn($field)
    {
        $this->removeColumns(func_get_args());
    }

    public function selectColums(array $fields)
    {
        $selectedCols = array();
        foreach ( $fields as $field )
        {
            if (isset($this->columns[$field]))
            {
                $selectedCols[] = $this->columns[$field];
            }
        }

        $this->columns = $selectedCols;
    }

    public function removeColumns(array $fields)
    {
        foreach ( $fields as $field )
        {
            unset($this->columns[$field]);
        }
    }

    public function getPager()
    {
        if (!$this->pager)
        {
            $this->pager = new GridSourcePager($this);
        }

        return $this->pager;
    }

    /**
     * @return the $limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param field_type $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return the $offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param field_type $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return the $filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param field_type $filter
     */
    public function setFilter($filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return the $orderBy
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param field_type $orderBy
     */
    public function setOrderBy($orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function getLastModified() {
        return null;
    }
}
