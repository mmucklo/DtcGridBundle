<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class RendererFactory
{
    protected $twig;
    protected $router;
    protected $translator;
    protected $themeCss;
    protected $themeJs;
    protected $pageDivStyle;
    protected $jqGridJs;
    protected $jqGridCss;
    protected $jqGridOptions;
    protected $tableOptions;
    protected $dataTablesCss;
    protected $dataTablesJs;
    protected $dataTablesClass;
    protected $dataTablesOptions;
    protected $jQuery;
    protected $purl;

    public function __construct(
        RouterInterface $router,
        $translator,
        array $config
    ) {
        $this->router = $router;
        $this->translator = $translator;
        $this->themeCss = $config['theme.css'];
        $this->themeJs = $config['theme.js'];
        $this->pageDivStyle = $config['page_div_style'];
        $this->jQuery = $config['jquery'];
        $this->purl = $config['purl'];
        $this->dataTablesCss = $config['datatables.css'];
        $this->dataTablesJs = $config['datatables.js'];
        $this->dataTablesClass = $config['datatables.class'];
        $this->dataTablesOptions = $config['datatables.options'];
        $this->jqGridCss = $config['jq_grid.css'];
        $this->jqGridJs = $config['jq_grid.js'];
        $this->jqGridOptions = $config['jq_grid.options'];
        $this->tableOptions = $config['table.options'];
    }

    public function setTwigEnvironment(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function getTwigEnvironment()
    {
        return $this->twig;
    }

    /**
     * Creates a new renderer of type $type, throws an exception if it's not known how to create a renderer of type $type.
     *
     * @param $type
     *
     * @return AbstractRenderer
     *
     * @throws \Exception
     */
    public function create($type)
    {
        $twig = $this->getTwigEnvironment();
        if (!$twig) {
            throw new \Exception('Twig Engine not found.  Please see https://github.com/mmucklo/DtcGridBundle/README.md for instructions.');
        }
        switch ($type) {
            case 'datatables':
                $renderer = new DataTablesRenderer($this->twig, $this->router, $this->translator, $this->dataTablesOptions);
                break;
            case 'jq_grid':
                $renderer = new JQGridRenderer($this->twig, $this->router, $this->translator, $this->jqGridOptions);
                break;
            case 'table':
                $renderer = new TableGridRenderer($this->twig, $this->router, $this->translator, $this->tableOptions);
                break;
            default:
                throw new \Exception("No renderer for type '$type''");
        }

        if (method_exists($renderer, 'setThemeCss')) {
            $renderer->setThemeCss($this->themeCss);
        }

        if (method_exists($renderer, 'setThemeJs')) {
            $renderer->setThemeJs($this->themeJs);
        }

        if (method_exists($renderer, 'setJQuery')) {
            $renderer->setJQuery($this->jQuery);
        }

        if (method_exists($renderer, 'setPurl')) {
            $renderer->setPurl($this->purl);
        }

        if (method_exists($renderer, 'setPageDivStyle')) {
            $renderer->setPageDivStyle($this->pageDivStyle);
        }

        if (method_exists($renderer, 'setJqGridCss')) {
            $renderer->setJqGridCss($this->jqGridCss);
        }

        if (method_exists($renderer, 'setJqGridJs')) {
            $renderer->setJqGridJs($this->jqGridJs);
        }

        if (method_exists($renderer, 'setDataTablesCss')) {
            $renderer->setDataTablesCss($this->dataTablesCss);
        }

        if (method_exists($renderer, 'setDataTablesJs')) {
            $renderer->setDataTablesJs($this->dataTablesJs);
        }

        if (method_exists($renderer, 'setDatatablesClass') && $this->dataTablesClass) {
            $renderer->setDatatablesClass($this->dataTablesClass);
        }

        return $renderer;
    }
}
