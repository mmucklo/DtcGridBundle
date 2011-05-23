<?php
namespace Dtc\GridBundle\Grid\Source;

use Dtc\GridBundle\Grid\Pager\Pager;

abstract class AbstractGridSource 
	implements GridSourceInterface
{
	protected $limit = 25;
	protected $offset = 0;
	protected $filter = array();
	protected $orderBy = array();
	protected $pager = array();
	
	public function getPager() {
		if (!$this->pager)
		{
			$this->pager = new Pager();
		}
		
		return $this->pager;
	}
	
	/**
	 * @return the $limit
	 */
	public function getLimit() {
		return $this->limit;
	}

	/**
	 * @param field_type $limit
	 */
	public function setLimit($limit) {
		$this->limit = $limit;
	}

	/**
	 * @return the $offset
	 */
	public function getOffset() {
		return $this->offset;
	}

	/**
	 * @param field_type $offset
	 */
	public function setOffset($offset) {
		$this->offset = $offset;
	}

	/**
	 * @return the $filter
	 */
	public function getFilter() {
		return $this->filter;
	}

	/**
	 * @param field_type $filter
	 */
	public function setFilter($filter) {
		$this->filter = $filter;
	}

	/**
	 * @return the $orderBy
	 */
	public function getOrderBy() {
		return $this->orderBy;
	}

	/**
	 * @param field_type $orderBy
	 */
	public function setOrderBy($orderBy) {
		$this->orderBy = $orderBy;
	}

	
}
