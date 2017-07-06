<?php

namespace Dtc\GridBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Dtc\GridBundle\Grid\Grid;
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
            $gridSource->selectColums($fields);
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
     * @Route("/purl", name="dtc_grid_bundle_purl")
     */
    public function purlAction()
    {
        $filePath = __DIR__.'/../Resources/external/purl/purl.js';
        $filePath = realpath($filePath);
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException();
        }

        return new Response(file_get_contents($filePath), 200, ['Content-Type' => 'application/javascript']);
    }

    /**
     * @Route("/dt_media/{rest}", requirements={"rest" = ".+"}, name="dtc_grid_bundle_media")
     */
    public function dtMediaAction($rest)
    {
        $parts = explode('/', $rest);
        $part0 = $parts[0];
        switch ($part0) {
            case 'css':
                $mimeType = 'text/css';
                break;
            case 'js':
                $mimeType = 'application/javascript';
                break;
            case 'images':
                $pathInfo = pathinfo($rest);
                if ($pathInfo['extension'] !== 'png') {
                    throw $this->createNotFoundException();
                }
                $mimeType = 'image/png';
                break;
            default:
                throw $this->createNotFoundException();
        }

        $newParts = [];
        foreach ($parts as $part) {
            $part = preg_replace('/[^a-zA-Z\.\_\-]/', '', $part);
            if (!$part || $part === '..' || $part === '.') {
                throw $this->createNotFoundException();
            }
            $newParts[] = $part;
        }

        $path = implode('/', $newParts);
        $path = __DIR__.'/../Resources/external/DataTables/media/'.$path;
        $path = realpath($path);
        if (!$path || !file_exists($path)) {
            throw $this->createNotFoundException();
        }

        return new Response(file_get_contents($path), 200, ['Content-Type' => $mimeType]);
    }
}
