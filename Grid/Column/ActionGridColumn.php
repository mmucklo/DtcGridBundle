<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;
use Symfony\Component\Routing\RouterInterface;

class ActionGridColumn extends AbstractGridColumn
{
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
            switch ($options['action']) {
                case 'show':
                    $route = $this->router->generate('dtc_grid_show', ['identifier' => $id, 'id' => $this->gridSourceId]);
                    $route = htmlspecialchars($route);
                    $content .= "<button class=\"btn btn-primary grid-show\" data-route=\"$route\" data-id=\"$idHtml\"";
                    $content .= "onclick=\"dtc_grid_show(this)\">$label</button>";
                    break;
                case 'delete':
                    $route = $this->router->generate('dtc_grid_delete', ['identifier' => $id, 'id' => $this->gridSourceId]);
                    $route = htmlspecialchars($route);
                    $content .= "<button class=\"btn btn-primary grid-delete\" data-route=\"$route\" data-id=\"$idHtml\"";
                    $content .= "onclick=\"dtc_grid_delete(this)\"><i class=\"fa fa-circle-o-notch fa-spin dtc-grid-hidden\"></i> $label</button>";
                    break;
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
