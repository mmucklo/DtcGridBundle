<?php
namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Bundle\TwigBundle\TwigEngine;

class TwigGridRenderer
	extends AbstractRenderer
{
	protected $twigEngine;
	
	public function __construct(
		TwigEngine $twigEngine)
	{
		$this->twigEngine = $twigEngine;
	}
	
	public function render() {
		$params = array(
			'records' => $this->grid->getGridSource()->getRecords(),
			'columns' => $this->grid->getColumns()
		);
		
		$template = 'DtcGridBundle:Grid:grid.html.twig';
		return $this->twigEngine->render($template, $params);
	}
}