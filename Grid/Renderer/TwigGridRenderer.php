<?php
namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\Router;

use Symfony\Bundle\TwigBundle\TwigEngine;

class TwigGridRenderer
	extends AbstractRenderer
{
	protected $twigEngine;
	protected $router;
	
	public function __construct(
		TwigEngine $twigEngine,
		Router $router)
	{
		$this->twigEngine = $twigEngine;
		$this->router = $router;
	}
	
	public function render() {
		$params = array(
			'records' => $this->gridSource->getRecords(),
			'columns' => $this->gridSource->getColumns()
		);
		
		$template = 'DtcGridBundle:Grid:grid.html.twig';
		return $this->twigEngine->render($template, $params);
	}
}