<?php
namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Grid;

abstract class AbstractRenderer 
{
	protected $grid;
	
	public function bind(Grid $grid) {
		$this->grid = $grid;
		$this->afterBind();
	}
	
	protected function afterBind() {
		
	}
	
	public abstract function render();
}
