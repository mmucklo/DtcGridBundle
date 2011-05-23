<?php
namespace Dtc\GridBundle\Controller;

use Dtc\GridBundle\Grid\Renderer\TwigGridRenderer;
use Dtc\GridBundle\Grid\Renderer\JQueryGridRenderer;
use Dtc\GridBundle\Grid\Grid;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Dtc\GridBundle\Grid\Source\DocumentGridSource;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GridController 
	extends Controller
{
	/**
	 * @Route("/test");
	 */
	public function test() {
		$dm = $this->get('doctrine.odm.mongodb.default_document_manager');
		$documentName = 'Odl\ShadowBundle\Documents\Character';
		$template = $this->get('templating');
		
		$gridSource = new DocumentGridSource($dm, $documentName);
		$renderer = new TwigGridRenderer($template);
		$renderer = new JQueryGridRenderer($template);
		
		$grid = new Grid($gridSource);
		$grid->setColumns($gridSource->getReflectionColumns());
		$grid->bindRenderer($renderer);
		
		$content = $grid->render();
		echo $content;
		exit();
		
		
		$col = new GridColumn('test', 'test');
		v($char);
		v($col->format($char));
		
		v($count);
		ve(count($arr));
	}
	

	/**
	 * @Route("/game-create");
	 */
	public function render($alias) {
		
	}
}
