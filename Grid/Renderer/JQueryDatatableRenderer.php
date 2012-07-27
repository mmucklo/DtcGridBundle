<?php
namespace Dtc\GridBundle\Grid\Renderer;

class JQueryDatatableRenderer
    extends TwigGridRenderer
{
    protected $options = array(
            'bProcessing' => true,
            'table_attr' => array(
                    'class' => 'display table table-striped table-bordered small-font'
                ),
            "sDom" => "<'row-fluid'<'span6'l><'span6'f>r>t<'row-fluid'<'span4'i><'span8'p>>",
            "sPaginationType" => "bootstrap",
            "oLanguage" => array(
                "sLengthMenu" => "_MENU_ records per page"
            ),
            "aoColumnDefs" => array(
                "bSortable" => false,
                "sWidth" => "20%",
                "aTargets" => array(-1)
            )
        );


    const MODE_AJAX = 1;
    const MODE_SERVER = 2;

    protected $mode = 1;

    public function setMode($mode) {
        $this->mode = $mode;
    }

    protected function afterBind()
    {
        $id = $this->gridSource->getDivId();
        $this->options['pager'] = "{$id}-pager";

        $params = array(
               'id' => $this->gridSource->getId(),
               'renderer' => 'grid.renderer.jq_table_grid'
        );

        $url = $this->router->generate('dtc_grid_grid_data', $params);
        $this->options['sAjaxSource'] = $url;

        /* foreach ( $this->gridSource->getColumns() as $column )
        {
            $info = array();
            $info['sTitle'] = $column->getLabel();
            $info['name'] = $column->getField();
            $info['index'] = $column->getField();
            $info = array_merge($info, $column->getOptions());

            $this->options['aoColumns'][] = $info;
        } */
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
                'id' => $gridSource->getId() // unique id
        );

        $data = array();
        foreach ( $records as $record )
        {
            $info = array();
            foreach ( $columns as $column )
            {
                $info[] = $column->format($record);
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
                'id' => $id
        );

        $template = 'DtcGridBundle:Grid:jquery_datatable.html.twig';
        return $this->twigEngine->render($template, $params);
    }
}