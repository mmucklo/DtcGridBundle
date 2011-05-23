<?php
namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

abstract class AbstractRenderer 
{
	protected $gridSource;
	
	public function bind(GridSourceInterface $gridSource) {
		$this->gridSource = $gridSource;
		$this->afterBind();
		
		return $this;
	}
	
	protected function afterBind() {
		
	}
	
	public abstract function render();
}
