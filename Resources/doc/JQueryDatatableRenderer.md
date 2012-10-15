Render using jQuery Data Table
==============================

You can render your grid using jQuery Data table in just two easy steps

### 1. Create a GridSource service

    <service id="grid.source.character" class="Dtc\GridBundle\Grid\Source\DocumentGridSource" public="true">
        <argument type="service" id="shadow.document_manager"></argument>
        <argument>Odl\ShadowBundle\Documents\Character</argument>
        <argument>grid.source.character</argument>

        <call method="autoDiscoverColumns"></call>
    </service>

### 2. Use the JQueryDatatable Renderer to render table.

In your controller:

    /**
     * @Route("/");
     * @Template()
     */
    public function indexAction()
    {
        $renderer = $this->get('grid.renderer.jq_table_grid');
        $gridSource = $this->get('grid.source.character');
        $renderer->bind($gridSource);

        $view = '::grid.html.twig';

        return $this->renderView($view, array(
            'grid' => $renderer
        ));
    }


In your template file:

    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title>{% block title %}Shadow Hunters Stats Tracker{% endblock %}</title>
            {% stylesheets
                'bundles/dtcgrid/lib/DataTables/media/jquery.dataTables*.css'
                'bundles/dtcgrid/css/datatable.bootstrap.css'

                combine=false
                filter='cssrewrite, lessphp'
                output="generated/css/grid_*.css"
            %}
                <link rel="stylesheet" href="{{ asset_url }}" />
            {% endstylesheets %}

            {% javascripts
                'bundles/dtcgrid/lib/purl.js'
                'bundles/dtcgrid/lib/DataTables/media/js/jquery.dataTables.min.js'
                'bundles/dtcgrid/js/jquery.datatable/DT_bootstrap.js'
                'bundles/dtcgrid/js/jquery.datatable/jquery.jqtable.js'

                combine=false
                output="generated/js/grid_*.js"
            %}
                <script type="text/javascript" src="{{ asset_url }}"></script>
            {% endjavascripts %}

            <link rel="shortcut icon" href="{{ asset('favicon.ico') }}" />
        </head>
        <body>
            {{ grid.render | raw }}
            <script>
            $(document).ready(function() {
                $('[data-jqtable]').jqtable();
            })
            </script>
        </body>
    </html>

### Customize jQuery Data Table

You can customize the Data Table by setting options that jQuery Data Table
supports. For example, you can hide the search bar by doing:

    $renderer = $this->get('...');
    $gridSource = $this->get('...');

    $renderer->setOption('table_attr', array(
            'class' => 'display table table-striped table-bordered small-font',
            'data-reload-url' => json_encode(array('event' => 'dtc.project'))
    ));

    $renderer->setOption('aoColumnDefs', array(
            array(
                "bSortable" => true,
                "sWidth" => "150px",
                "aTargets" => array(0)
            ),
            array(
                "bSortable" => true,
                "sWidth" => "340px",
                "aTargets" => array(1)
            ),
            array(
                "bSortable" => true,
                "sWidth" => "80px",
                "aTargets" => array(2,3)
            ),
            array(
                "bSortable" => false,
                "sWidth" => "110px",
                "aTargets" => array(-1)
            )
        ));

    $renderer->bind($gridSource);

For more Data Table setting, refer to Data Table documentations:
http://datatables.net/examples/

You can use table_attr for setting Attributes for Table Element.

### Customize jQuery Data Table columns

Look at the example above.

### Filtering the grid

You can filter the grid using jQuery Data Table's build in search filter or you
filter the data in the grid using server.

    $filter = array();
    if ($projectId = $request->get('project')) {
        $filter['project'] = $projectId;
    }
    if ($userId = $request->get('user')) {
        $filter['owner'] = $userId;
    }
    $gridSource->setFilter($filter);

If you are developing a custom Grid Source, you can use the filter array set here
to define custom query.

### Filter the grid via ajax

    $('#table').jqtable('filter', {filter1: value, filter2: value....});

You can change the filters via ajax.

