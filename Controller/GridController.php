<?php

namespace Dtc\GridBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class GridController extends Controller
{
    /**
     * @Route("/data/")
     */
    public function dataAction(Request $request)
    {
        $rendererService = $request->get('renderer', 'grid.renderer.jq_grid');
        $renderer = $this->get($rendererService);
        $gridSource = $this->get($request->get('id'));

        $response = new Response();
        $gridSource->bind($request); // Sets limit, offset, sort, filter, etc
        $renderer->bind($gridSource); // Sets grid to renderer

        $fields = $request->get('fields', null);
        if ($fields && is_array($fields)) {
            $gridSource->selectColumns($fields);
        }

        $content = null;
        // If changes to data is kept track using update_time, then we
        //   can skip querying for all data.
        if ($lastModified = $gridSource->getLastModified()) {
            $response->setLastModified($lastModified);
        } else {
            // generate etag from data
            $data = $renderer->getData();
            $content = json_encode($data);
            $eTag = hash('sha256', $content);
            $response->setEtag($eTag);
        }

        $response->setPublic();
        if ($response->isNotModified($request)) {
            return $response;
        } else {
            if (!$content) {
                $data = $renderer->getData();
                $content = json_encode($data);
            }

            $response->headers->set('Content-type', 'application/json');
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * @Route("/purl.js", name="dtc_grid_bundle_purl")
     * @Route("/js/dataTables.{type}.js", name="dtc_grid_bundle_dataTables_extension", requirements={"type" = "\w+" })
     * @Route("/js/jquery.dataTables.js", name="dtc_grid_bundle_dataTables")
     * @Route("/css/jquery.dataTables.css", name="dtc_grid_bundle_dataTables_css")
     * @Route("/css/jquery.dataTables_themeroller.css", name="dtc_grid_bundle_dataTables_themeroller_css")
     * @Route("/js/jquery.js", name="dtc_grid_bundle_jquery")
     * @Route("/css/dataTables.{type}.css", name="dtc_grid_bundle_dataTables_extension_css", requirements={"type" = "\w+" })
     * @Route("/images/sort_{type}.png", name="dtc_grid_bundle_dataTables_images", requirements={"type" = "\w+" })
     */
    public function mediaAction(Request $request, $type = null)
    {
        $debug = $this->getParameter('kernel.debug');
        $min = $debug ? '' : '.min';

        $route = $request->get('_route');
        switch ($route) {
            case 'dtc_grid_bundle_purl':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/purl/purl.js'));
            case 'dtc_grid_bundle_dataTables':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/js/jquery.dataTables'.$min.'.js'));
            case 'dtc_grid_bundle_dataTables_css':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/css/jquery.dataTables'.$min.'.css'));
            case 'dtc_grid_bundle_dataTables_themeroller_css':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/css/jquery.dataTables_themeroller.css'));
            case 'dtc_grid_bundle_dataTables_extension':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/js/dataTables.'.$type.$min.'.js'));
            case 'dtc_grid_bundle_jquery':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/js/jquery.js'));
            case 'dtc_grid_bundle_dataTables_extension_css':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/css/dataTables.'.$type.$min.'.css'));
            case 'dtc_grid_bundle_dataTables_images':
                return $this->getResource($request, realpath(__DIR__.'/../Resources/external/DataTables/media/images/sort_'.$type.'.png'));
            default:
                $this->get('logger')->error(__METHOD__.' - Unknown route: '.$route);
                throw $this->createNotFoundException();
        }
    }

    /**
     * @param Request $request
     * @param $filename
     *
     * @return Response
     */
    protected function getResource(Request $request, $filename)
    {
        $response = new Response();
        if ($filemtime = filemtime($filename)) {
            $date = new \DateTime();
            $date->setTimestamp($filemtime);
            $response->setLastModified($date);
            if ($response->isNotModified($request)) {
                return $response;
            }
        }
        $pathInfo = pathinfo($filename);
        switch ($pathInfo['extension']) {
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'application/javascript';
                break;
            case 'png':
                $mimeType = 'image/png';
                break;
            default:
                $this->get('logger')->error(__METHOD__.' Unsupported file extension: '.$pathInfo['extension']);
                throw $this->createNotFoundException();
        }
        if (!file_exists($filename)) {
            throw $this->createNotFoundException();
        }

        $content = file_get_contents($filename);
        $response->setPublic();
        $response->setContent($content);
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', $mimeType);
        $response->setMaxAge(60);

        return $response;
    }
}
