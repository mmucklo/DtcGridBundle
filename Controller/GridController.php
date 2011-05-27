<?php
namespace Dtc\GridBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Response;

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
		$renderer = $this->get('grid.renderer.jq_grid');

		$gridSource = new DocumentGridSource($dm, $documentName);
		$content = $renderer->bind($gridSource)->render();
		echo $content;
		exit();


		$col = new GridColumn('test', 'test');
		v($char);
		v($col->format($char));

		v($count);
		ve(count($arr));
	}

	/**
	 * @Route("/data/{id}",
	 * 	defaults={"id"="Odl\ShadowBundle\Documents\Character"}
	 * )
	 */
	public function dataAction($id) {
		$dm = $this->get('doctrine.odm.mongodb.default_document_manager');
		$documentName = 'Odl\ShadowBundle\Documents\Character';
		$renderer = $this->get('grid.renderer.jq_grid');

		$gridSource = new DocumentGridSource($dm, $documentName);
		$data = $renderer->bind($gridSource)->getData();
		return new Response(json_encode($data));
	}


	/**
	 * @Route("/game-create");
	 */
	public function render($alias) {

	}
}
