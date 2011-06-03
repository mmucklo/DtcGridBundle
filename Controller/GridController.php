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
	 * @Route("/data/{id}")
	 */
	public function dataAction($id) {
		$request = $this->get('request');
		$renderer = $this->get('grid.renderer.jq_grid');

	    $id = base64_decode($id);
		$gridSource = $this->get($id);

		$response = new Response();
		$response->setLastModified($gridSource->getLastModified());
        if ($response->isNotModified($request)) {
            return $response;
        }
        else {
    		$data = $renderer->bind($gridSource)->getData();
    		$content = json_encode($data);
		    $response->setContent($content);
		}

		return $response;
	}
}
