<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;

class TwigGridRenderer extends AbstractRenderer
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
        );

        $template = 'DtcGridBundle:Grid:grid.html.twig';

        return $this->twigEngine->render($template, $params);
    }
}
