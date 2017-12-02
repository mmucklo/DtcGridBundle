<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Column\AbstractGridColumn;

class JQGridRenderer extends AbstractJqueryRenderer
{
    protected $jqGridCss = [];
    protected $jqGridJs = [];
    protected $options = array(
            'datatype' => 'json',
            'jsonReader' => array(
                    'root' => 'rows',
                    'total' => 'total',
                    'records' => 'records',
                    'page' => 'page',
                    'repeatitems' => false,
            ),

            'url' => null,
            'cell' => '',
            'width' => '840',
            'height' => '400',
            'loadui' => 'disable',
            'altRows' => true,
            'viewrecords' => true,
            'multiselect' => true,
            'styleUI' => 'Bootstrap',

            // Paging params
            'prmNames' => array(
                    'page' => 'page',
                    'rows' => 'limit',
                    'sort' => 'sort_column',
                    'order' => 'sort_order',
                    'nd' => null,
            ),

            'ajaxGridOptions' => array(
                    'cache' => false,
                    'ifModified' => false,
            ),

            // Pager Config
            'pager' => 'grid-pager',
            'recordtext' => 'View {0} - {1} of {2}',
            'emptyrecords' => 'No records to view',
            'loadtext' => 'Loading...',
            'pgtext' => 'Page {0} of {1}',
    );

    protected function afterBind()
    {
        $id = $this->gridSource->getDivId();
        $this->options['pager'] = "{$id}-pager";

        $params = array(
                'id' => $this->gridSource->getId(),
                'renderer' => 'jq_grid',
        );

        $url = $this->router->generate('dtc_grid_data', $params);
        $this->options['url'] = $url;

        /** @var AbstractGridColumn $column */
        foreach ($this->gridSource->getColumns() as $column) {
            $info = array();
            $info['label'] = $column->getLabel();
            $info['name'] = $column->getField();
            $info['index'] = $column->getField();
            $info['sortable'] = $column->getOption('sortable') ? true : false;
            $info = array_merge($info, $column->getOptions());

            $this->options['colModel'][] = $info;
        }

        if ($sortInfo = $this->gridSource->getDefaultSort()) {
            if ($sortInfo['column']) {
                $this->options['sortname'] = $sortInfo['column'];
                $this->options['sortorder'] = strtolower($sortInfo['direction'] ?: 'ASC');
            }
        }
    }

    public function getData()
    {
        $columns = $this->gridSource->getColumns();
        $gridSource = $this->gridSource;
        $records = $gridSource->getRecords();

        $retVal = array(
                'page' => $gridSource->getPager()
                    ->getCurrentPage(),
                'total' => $gridSource->getPager()
                    ->getTotalPages(),
                'records' => $gridSource->getCount(),
                'id' => $gridSource->getId(), // unique id
        );

        foreach ($records as $record) {
            $info = array();
            /** @var AbstractGridColumn $column */
            foreach ($columns as $column) {
                if (method_exists($column, 'setRouter')) {
                    $column->setRouter($this->router);
                }
                if (method_exists($column, 'setGridSourceId')) {
                    $column->setGridSourceId($gridSource->getId());
                }
                $info[$column->getField()] = $column->format($record, $this->gridSource);
            }

            $retVal['rows'][] = $info;
        }

        return $retVal;
    }

    public function setJqGridCss(array $css)
    {
        $this->jqGridCss = $css;
    }

    public function setJqGridJs(array $js)
    {
        $this->jqGridJs = $js;
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
        $params['dtc_grid_jq_grid_css'] = $this->jqGridCss;
        $params['dtc_grid_jq_grid_js'] = $this->jqGridJs;

        return $params;
    }

    public function render()
    {
        $id = $this->gridSource->getDivId();

        $params = array(
                'options' => $this->options,
                'id' => $id,
        );

        $template = 'DtcGridBundle:Grid:jq_grid.html.twig';

        return $this->twigEngine->render($template, $params);
    }
}
