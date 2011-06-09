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
		$gridSource->bind($request);        // Sets limit, offset, sort, filter, etc
    	$renderer->bind($gridSource);       // Sets grid to renderer

    	$content = null;
    	// If changes to data is kept track using update_time, then we
    	//   can skip querying for all data.
    	if ($lastModified = $gridSource->getLastModified()) {
		    $response->setLastModified($lastModified);

		    // Etag should be a function of url (sort, limit, offset, filters)
		    //    Best implementation would requires GridSourceRequest object ->hash()
		    // $eTag = hash('sha256', $content);
    	}
    	else {
    	    // generate etag from data
    	    $data = $renderer->getData();
    		$content = json_encode($data);
    		$eTag = hash('sha256', $content);
    		$response->setEtag($eTag);
    	}

    	$response->setPublic();
		if ($response->isNotModified($request)) {
            return $response;
        }
        else {
            if (!$content)
            {
        	    $data = $renderer->getData();
        		$content = json_encode($data);
            }
            $response->setContent($content);
		}

		return $response;
	}
}
