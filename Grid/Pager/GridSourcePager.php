<?php
namespace Dtc\GridBundle\Grid\Pager;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridSourcePager
	extends Pager 
{
	private $gridSource;
	private $totalPages;
	
	public function __construct(GridSourceInterface $gridSource)
	{
		$this->gridSource = $gridSource;
	}
	
	public function getCurrentPage() {
		$limit = $this->gridSource->getLimit();
		$offset = $this->gridSource->getOffset();
		
		return ceil(($offset / $limit) + 1);
	}
	
	public function getTotalPages() {
		$limit = $this->gridSource->getLimit();
		return ceil($this->gridSource->getCount() / $limit);
	}
	
	public function getRange($delta) {
		
	}
}