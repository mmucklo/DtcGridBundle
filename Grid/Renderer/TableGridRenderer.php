<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class TableGridRenderer extends AbstractRenderer
{
    public static $defaultOptions = [
        'table_attr' => [
            'class' => 'display table table-striped table-bordered small-font',
        ],
    ];

    protected $twig;
    protected $router;
    protected $translator;
    protected $options;

    public function __construct(Environment $twig, RouterInterface $router, $translator, array $options)
    {
        $this->translator = $translator;
        $this->twig = $twig;
        $this->router = $router;
        $this->options = $options;
    }

    public function render()
    {
        $params = [
                'records' => $this->gridSource->getRecords(),
                'columns' => $this->gridSource->getColumns(),
                'options' => $this->options,
                'source' => $this->gridSource,
        ];

        $template = '@DtcGrid/Grid/table.html.twig';

        return $this->twig->render($template, $params);
    }

    public function getParams(array &$params = null)
    {
        if (null === $params) {
            $params = [];
        }
        parent::getParams($params);

        return $params;
    }
}
