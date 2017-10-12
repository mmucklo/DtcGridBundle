<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;

class TableGridRenderer extends AbstractRenderer
{
    protected $twigEngine;
    protected $router;
    protected $options = array(
            'table_attr' => array(
                    'class' => 'display table table-striped table-bordered small-font',
                ),
        );

    public function __construct(TwigEngine $twigEngine, Router $router)
    {
        $this->twigEngine = $twigEngine;
        $this->router = $router;
    }

    public function render()
    {
        $params = array(
                'records' => $this->gridSource->getRecords(),
                'columns' => $this->gridSource->getColumns(),
                'options' => $this->options,
                'source' => $this->gridSource,
        );

        $template = 'DtcGridBundle:Grid:table.html.twig';

        return $this->twigEngine->render($template, $params);
    }

    /**
     * @param array|null $params
     */
    public function getParams(array &$params = null)
    {
        if (null === $params) {
            $params = [];
        }
        parent::getParams($params);

        return $params;
    }
}
