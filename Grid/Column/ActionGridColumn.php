<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionGridColumn extends AbstractGridColumn
{
    static protected $spinnerHtml = '<i class="fa fa-circle-o-notch fa-spin dtc-grid-hidden"></i> ';

    protected $actions;
    protected $idField;

    /** @var RouterInterface */
    protected $router;
    protected $gridSourceId;

    public function __construct($field, array $actions, $idField = 'id')
    {
        $this->actions = $actions;
        $this->idField = $idField;
        $this->label = 'Actions';
        $this->field = $field;
    }

    public function format($object, GridSourceInterface $gridsource)
    {
        $method = 'get'.ucfirst($this->idField);
        $id = $object->$method();
        $idHtml = htmlspecialchars($id);
        $content = '';
        foreach ($this->actions as $action => $options) {
            $label = $options['label'];
            if ($content) {
                $content .= ' ';
            }
            $route = isset($options['route']) ? $options['route'] : '';
            switch ($options['action']) {
                case 'show':
                    if (!$route) {
                        $route = 'dtc_grid_show';
                    }
                    $uri = $this->router->generate($route, ['identifier' => $id, 'id' => $this->gridSourceId]);
                    $uri = htmlspecialchars($uri);
                    $content .= "<button class=\"btn btn-primary grid-show\" data-route=\"$uri\" data-id=\"$idHtml\"";
                    $content .= "onclick=\"dtc_grid_show(this)\">$label</button>";
                    break;
                case 'delete':
                    if (!$route) {
                        $route = 'dtc_grid_delete';
                    }
                    $uri = $this->router->generate($route, ['identifier' => $id, 'id' => $this->gridSourceId]);
                    $uri = htmlspecialchars($uri);
                    $content .= "<button class=\"btn btn-primary grid-delete\" data-route=\"$uri\" data-id=\"$idHtml\"";
                    $content .= "onclick=\"dtc_grid_delete(this)\">" . static::$spinnerHtml . "$label</button>";
                    break;
                default:
                    $uri = $this->router->generate($route, ['identifier' => $id, 'id' => $this->gridSourceId]);
                    $uri = htmlspecialchars($uri);
                    $content .= "<button class \"";
                    if (isset($options['button_class'])) {
                        $content .= " " . $options['button_class'];
                    }
                    $content .= " data-route=\"$uri\" data-id=\"$idHtml\"";
                    if (isset($options['onclick'])) {
                        $content .= " onclick=\"" . htmlspecialchars($options['onclick']) . "\"";
                    }
                    $content .= ">$label</button>";
            }
        }

        return $content;
    }

    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function setGridSourceId($gridSourceId)
    {
        $this->gridSourceId = $gridSourceId;
    }
}
