<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\Router;
use Symfony\Bundle\TwigBundle\TwigEngine;

class RendererFactory
{
    protected $twigEngine;
    protected $router;
    protected $bootstrapCss;
    protected $bootstrapJs;
    protected $pageDivStyle;
    protected $javascripts;
    protected $stylesheets;

    public function __construct(TwigEngine $twigEngine,
                                Router $router,
                                $bootstrapCss,
                                $bootstrapJs,
                                $pageDivStyle,
                                $stylesheets,
                                $javascripts)
    {
        $this->twigEngine = $twigEngine;
        $this->router = $router;
        $this->bootstrapCss = $bootstrapCss;
        $this->bootstrapJs = $bootstrapJs;
        $this->pageDivStyle = $pageDivStyle;
        $this->stylesheets = $stylesheets;
        $this->javascripts = $javascripts;
    }

    /**
     * Creates a new renderer of type $type, throws an exception if it's not known how to create a renderer of type $type.
     *
     * @param $type
     *
     * @return AbstractRenderer
     */
    public function create($type)
    {
        switch ($type) {
            case 'datatables':
                $renderer = new DataTablesRenderer($this->twigEngine, $this->router);
                break;
            case 'jq_grid':
                $renderer = new JQGridRenderer($this->twigEngine, $this->router);
                break;
            case 'table':
                $renderer = new TableGridRenderer($this->twigEngine, $this->router);
                break;
            default:
                throw new \Exception("No renderer for type '$type''");
        }

        if (method_exists($renderer, 'setBootstrapCss')) {
            $renderer->setBootstrapCss($this->bootstrapCss);
        }

        if (method_exists($renderer, 'setBootstrapJs')) {
            $renderer->setBootstrapJs($this->bootstrapJs);
        }

        if (method_exists($renderer, 'setPageDivStyle')) {
            $renderer->setPageDivStyle($this->pageDivStyle);
        }

        if (method_exists($renderer, 'setStylesheets')) {
            $renderer->setStylesheets($this->stylesheets);
        }

        if (method_exists($renderer, 'setJavascripts')) {
            $renderer->setJavascripts($this->javascripts);
        }

        return $renderer;
    }
}
