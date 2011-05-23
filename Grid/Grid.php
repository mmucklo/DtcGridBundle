<?php
namespace Dtc\GridBundle\Grid;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

use Symfony\Component\HttpFoundation\Request;

class Grid 
{
	private $gridSource;
	private $renderer;
	private $columns;
	private $id;

	public function __construct(GridSourceInterface $gridSource, $id = 'grid') 
	{
		$this->gridSource = $gridSource;
		$this->id = $id;
	}
	
	public function getId() {
		return $this->id;
	}
	
	/**
	 * @return the $gridSource
	 */
	public function getGridSource() {
		return $this->gridSource;
	}

	/**
	 * @param GridSource $gridSource
	 */
	public function setGridSource($gridSource) {
		$this->gridSource = $gridSource;
	}

	/**
	 * @return the $renderer
	 */
	public function getRenderer() {
		return $this->renderer;
	}

	/**
	 * @param field_type $renderer
	 */
	public function setRenderer($renderer) {
		$this->renderer = $renderer;
	}

	/**
	 * @return the $columns
	 */
	public function getColumns() {
		return $this->columns;
	}

	/**
	 * @param field_type $columns
	 */
	public function setColumns($columns) {
		$this->columns = $columns;
	}
	
	public function bindRenderer($renderer) {
		$this->renderer = $renderer;
		$renderer->bind($this);
	}
	
	public function bind(Request $request) {
		// Change limit, offset
		if ($limit = $request->get('limit'))
		{
			$this->gridSource->setLimit($limit);
		}
		
		if ($offset = $request->get('offset')) {
			$this->gridSource->setOffset($offset);
		}
	}
	
	public function render() 
	{
		if (!$this->renderer) {
			throw new RuntimeException('Set renderer first');
		}
		
		return $this->renderer->render();
	}
}
