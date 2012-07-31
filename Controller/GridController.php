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

class GridController extends Controller
{

    /**
     * @Route("/data/")
     */
    public function dataAction()
    {
        $request = $this->get('request');
        $rendererService = $request->get('renderer', 'grid.renderer.jq_grid');
        $renderer = $this->get($rendererService);
        $gridSource = $this->get($request->get('id'));

        $response = new Response();
        $gridSource->bind($request); // Sets limit, offset, sort, filter, etc
        $renderer->bind($gridSource); // Sets grid to renderer


        $content = null;
        // If changes to data is kept track using update_time, then we
        //   can skip querying for all data.
        if ($lastModified = $gridSource->getLastModified())
        {
            $response->setLastModified($lastModified);
        }
        else
        {
            // generate etag from data
            $data = $renderer->getData();
            $content = json_encode($data);
            $eTag = hash('sha256', $content);
            $response->setEtag($eTag);
        }

        $response->setPublic();
        if ($response->isNotModified($request))
        {
            return $response;
        }
        else
        {
            if (!$content)
            {
                $data = $renderer->getData();
                $content = json_encode($data);
            }

            $response->headers->set('Content-type', 'application/json');
            $response->setContent($content);
        }

        return $response;
    }
}
