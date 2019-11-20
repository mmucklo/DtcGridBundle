<?php

namespace Dtc\GridBundle\Grid\Source;

use Dtc\GridBundle\Grid\Column\AbstractGridColumn;
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
    protected $parameters;

    public function bind(Request $request)
    {
        // Change limit, offset.
        if ($limit = $request->get('limit')) {
            $this->limit = $limit;
        }

        if ($page = $request->get('page')) {
            $this->offset = $this->limit * ($page - 1);
        }

        if ($offset = $request->get('offset')) {
            $this->offset = $offset;
        }

        if ($filter = $request->get('filter')) {
            $this->filter = $filter;
        }

        if ($sortColumn = $request->get('sort_column')) {
            $sortOrder = $request->get('sort_order');
            $sortOrder = strtoupper($sortOrder);

            $this->orderBy[$sortColumn] = $sortOrder;
        }

        if ($orderBy = $request->get('order')) {
            $this->orderBy = $orderBy;
        }
    }

    public function getDefaultSort()
    {
        return null;
    }

    public function getId()
    {
        return $this->id;
    }

    private $divId = null;

    public function getDivId()
    {
        if (!$this->divId) {
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

    public function setColumns($columns)
    {
        /** @var AbstractGridColumn $col */
        foreach ($columns as $col) {
            $this->columns[$col->getField()] = $col;
        }
    }

    public function removeColumn($field)
    {
        $this->removeColumns(func_get_args());
    }

    public function selectColumns(array $fields)
    {
        $selectedCols = array();
        foreach ($fields as $field) {
            if (isset($this->columns[$field])) {
                $selectedCols[$field] = $this->columns[$field];
            }
        }

        $this->columns = $selectedCols;
    }

    public function removeColumns(array $fields)
    {
        foreach ($fields as $field) {
            unset($this->columns[$field]);
        }
    }

    public function getPager()
    {
        if (!$this->pager) {
            $this->pager = new GridSourcePager($this);
        }

        return $this->pager;
    }

    /**
     * @return int $limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @param int $limit
     */
    public function setLimit($limit)
    {
        $this->limit = $limit;
    }

    /**
     * @return int $offset
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * @param int $offset
     */
    public function setOffset($offset)
    {
        $this->offset = $offset;
    }

    /**
     * @return array $filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param array $filter
     */
    public function setFilter(array $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return array $orderBy
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * @param array $orderBy
     */
    public function setOrderBy(array $orderBy)
    {
        $this->orderBy = $orderBy;
    }

    public function getLastModified()
    {
        return null;
    }

    public function getParameter($key)
    {
        return $this->parameters[$key];
    }

    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    /**
     * @return array $parameters
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param array $parameters
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @return bool Returns true if this GridSource has an ID or false otherwise.
     */
    public function hasIdColumn()
    {
        return false;
    }

    /**
     * @param $id
     *
     * @return mixed|null Returns the row identified by Id if found.
     */
    public function find($id)
    {
        return null;
    }
}
