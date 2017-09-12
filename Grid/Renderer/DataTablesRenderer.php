<?php

namespace Dtc\GridBundle\Grid\Renderer;

use Dtc\GridBundle\Grid\Column\AbstractGridColumn;

class DataTablesRenderer extends TableGridRenderer
{
    protected $options = array(
            'bProcessing' => true,
            'searchDelay' => 350,
            'table_attr' => array(
                    'class' => 'display table table-striped table-bordered small-font',
                ),
            'sPaginationType' => 'bootstrap',
            'bServerSide' => true,
            'oLanguage' => array(
                'sLengthMenu' => '_MENU_ records per page',
            ),
            'aoColumns' => array(array(
                'bSortable' => false,
                'sWidth' => '20%',
                'aTargets' => array(-1),
            )),
        );

    const MODE_AJAX = 1;
    const MODE_SERVER = 2;

    protected $mode = 1;

    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    protected function afterBind()
    {
        $id = $this->gridSource->getDivId();
        $this->options['pager'] = "{$id}-pager";

        $fields = array_keys($this->gridSource->getColumns());

        // We need to pass filter information here.
        $params = array(
               'id' => $this->gridSource->getId(),
               'renderer' => 'dtc_grid.renderer.datatables',
               'filter' => $this->gridSource->getFilter(),
               'parameters' => $this->gridSource->getParameters(),
               'order' => $this->gridSource->getOrderBy(),
               'fields' => $fields,
        );

        $url = $this->router->generate('dtc_grid_grid_data', $params);
        $this->options['sAjaxSource'] = $url;

        $columnsDef = array();
        /** @var AbstractGridColumn $column */
        foreach ($this->gridSource->getColumns() as $index => $column) {
            $info = array();
            $info['bSortable'] = $column->getOption('sortable') ? true : false;
            $info['sName'] = $column->getField();

            if ($width = $column->getOption('width')) {
                $info['sWidth'] = $width;
            }

            $info['aTargets'] = array($index);
            $info = array_merge($info, $column->getOptions());
            $columnsDef[] = $info;
        }

        $this->options['aoColumns'] = $columnsDef;
    }

    public function getData()
    {
        $columns = $this->gridSource->getColumns();
        $gridSource = $this->gridSource;
        $records = $gridSource->getRecords();
        $count = $gridSource->getCount();

        $retVal = array(
                'page' => $gridSource->getPager()
                    ->getCurrentPage(),
                'total_pages' => $gridSource->getPager()
                    ->getTotalPages(),
                'iTotalRecords' => (int) $count,
                'iTotalDisplayRecords' => $count,
                'id' => $gridSource->getId(), // unique id
        );

        $data = array();
        foreach ($records as $record) {
            $info = array();
            /** @var AbstractGridColumn $column */
            foreach ($columns as $column) {
                $info[] = $column->format($record, $gridSource);
            }

            $data[] = $info;
        }

        $retVal['aaData'] = $data;

        return $retVal;
    }

    public function render()
    {
        $id = $this->gridSource->getDivId();

        $options = $this->options;
        unset($options['table_attr']);

        $params = array(
                'options' => $options,
                'table_attr' => $this->options['table_attr'],
                'columns' => $this->gridSource->getColumns(),
                'id' => $id,
        );

        $template = 'DtcGridBundle:Grid:datatables.html.twig';

        return $this->twigEngine->render($template, $params);
    }
}
