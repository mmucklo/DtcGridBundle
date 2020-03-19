<?php

namespace Dtc\GridBundle\Controller;

use Dtc\GridBundle\Grid\Renderer\AbstractRenderer;
use Dtc\GridBundle\Util\CamelCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class GridController
{
    private $container;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function grid(Request $request)
    {
        $class = $request->get('class');
        if (!$class) {
            throw $this->createNotFoundException('No class passed in');
        }

        if ($rendererId = $request->get('renderer')) {
            if (!$this->container->has($rendererId)) {
                throw new \Exception("No renderer found with id $rendererId");
            }
            if (!($renderer = $this->container->get($rendererId)) instanceof AbstractRenderer) {
                throw new \Exception("Rennderer $rendererId must be instanace of Dtc\GridBundle\Grid\Renderer\AbstractRenderer");
            }
            if (!($view = $request->get('view'))) {
                throw new \Exception("No view parameter specified for renderer $rendererId");
            }
        } else {
            $rendererType = $request->get('type', 'table');
            $renderer = $this->container->get('dtc_grid.renderer.factory')->create($rendererType);
            $view = '@DtcGrid/Page/'.$rendererType.'.html.twig';
        }

        $gridSource = $this->container->get('dtc_grid.manager.source')->get($class);
        $renderer->bind($gridSource);

        if ($this->container->has('templating')) {
            return new Response($this->container->get('templating')->render($view, $renderer->getParams()));
        } else if ($this->container->has('twig')) {
            return new Response($this->container->get('twig')->render($view, $renderer->getParams()));
        }

        throw new \Exception("Need Twig Bundle or Templating component installed");
    }

    public function data(Request $request)
    {
        $rendererService = $request->get('renderer', 'datatables');
        if ($this->container->has($rendererService)) {
            if (!($rendererService = $this->container->get($rendererService)) instanceof AbstractRenderer) {
                throw new \Exception("$rendererService not instance of Dtc\GridBundle\Grid\Renderer\AbstractRenderer");
            }
        } else {
            $renderer = $this->container->get('dtc_grid.renderer.factory')->create($rendererService);
        }
        $gridSource = $this->container->get('dtc_grid.manager.source')->get($request->get('id'));

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
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function show(Request $request)
    {
        $gridSource = $this->container->get('dtc_grid.manager.source')->get($request->get('id'));
        $id = $request->get('identifier');
        $result = $gridSource->find($id);

        $responseResult = [];
        if (!$result) {
            return new Response('Not Found', 404);
        }
        if (is_array($result)) {
            foreach ($result as $key => $value) {
                $responseResult[CamelCase::fromCamelCase($key)] = $value;
            }
        } elseif (method_exists($gridSource, 'getClassMetadata')) {
            $classMetadata = $gridSource->getClassMetadata();
            $fieldNames = $classMetadata->getFieldNames();
            foreach ($fieldNames as $fieldName) {
                $method = 'get'.ucfirst($fieldName);
                if (method_exists($result, $method)) {
                    $responseResult[CamelCase::fromCamelCase($fieldName)] = $result->$method();
                }
            }
        }

        return new JsonResponse($responseResult);
    }

    /**
     *
     * @param Request $request
     *
     * @return Response
     */
    public function delete(Request $request)
    {
        $gridSource = $this->container->get('dtc_grid.manager.source')->get($request->get('id'));
        $id = $request->get('identifier');
        $gridSource->remove($id);
        $response = new Response();
        $response->setStatusCode(204);

        return $response;
    }
}
