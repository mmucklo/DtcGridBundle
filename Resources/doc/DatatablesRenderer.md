Render using jQuery Data Table
==============================

You can render your grid using jQuery Data table in just two easy steps

### 1. Create a GridSource service

    XML:
        <service id="grid.source.character" class="Dtc\GridBundle\Grid\Source\DocumentGridSource" public="true">
            <argument type="service" id="doctrine_mongodb.odm.default_document_manager"></argument>
            <argument>Odl\ShadowBundle\Documents\Character</argument>
            <argument>grid.source.character</argument>
            <call method="autoDiscoverColumns"></call>
        </service>
    
    YAML:
        grid.source.user:
            class: Dtc\GridBundle\Grid\Source\DocumentGridSource
            arguments: ['@doctrine_mongodb.odm.default_document_manager', AppBundle\Document\User]
            tags: [{ name: dtc_grid.source }]
            calls: [[autoDiscoverColumns]]

 * For ORMs, use
    * class: Dtc\GridBundle\Grid\Source\EntityGridSource
    * arguments: @doctrine.orm.default_entity_manager (instead of @doctrine_mongodb.odm.default_document_manager)

### 2. Use the JQueryDataTable Renderer to render the table.

In your controller:

    /**
     * @Route("/");
     * @Template()
     */
    public function indexAction()
    {
        $renderer = $this->get('dtc_grid.renderer.datatables');
        $gridSource = $this->get('grid.source.character');
        $renderer->bind($gridSource);

        /* To enable sorting, uncomment below
        $columns = $gridSource->getColumns();
        foreach ($columns as $column) {
            $column->setOption('sortable', true);
        }*/

        $view = '::grid.html.twig';

        return $this->renderView($view, array(
            'dtc_grid' => $renderer
        ));
    }


In your template file:

    <!DOCTYPE html>
    <html>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            {% block stylesheets %}
                {% for stylesheet in dtc_grid_theme_css %}
                    {% if stylesheet.url is defined %}
                        <link rel="stylesheet" href="{{ stylesheet.url }}"
                            {% if stylesheet.integrity is not empty %} integrity="{{ stylesheet.integrity }}"{% endif %}
                            {% if stylesheet.crossorigin is not empty %} crossorigin="{{ stylesheet.crossorigin }}"{% endif %}
                        >
                    {% else %}
                        <link rel="stylesheet" href="{{ stylesheet }}">
                    {% endif %}
                {% endfor %}
                {% for stylesheet in dtc_grid_local_css %}
                    <link rel="stylesheet" href="{{ app.request.baseUrl }}{{ stylesheet }}" />
                {% endfor %}
            {% endblock %}
        
            {% for stylesheet in dtc_grid_datatables_css %}
                {% if stylesheet.url is defined %}
                    <link rel="stylesheet" href="{{ stylesheet.url }}"
                            {% if stylesheet.integrity is not empty %} integrity="{{ stylesheet.integrity }}"{% endif %}
                            {% if stylesheet.crossorigin is not empty %} crossorigin="{{ stylesheet.crossorigin }}"{% endif %}
                    >
                {% else %}
                    <link rel="stylesheet" href="{{ stylesheet }}">
                {% endif %}
            {% endfor %}
            
            {% block dtc_grid_javascripts %}
                <script src="{{ dtc_grid_jquery.url }}"
                    {% if dtc_grid_jquery.integrity is not empty  %} integrity="{{ dtc_grid_jquery.integrity }}"{% endif %}
                    {% if dtc_grid_jquery.crossorigin is not empty  %} crossorigin="{{ dtc_grid_jquery.crossorigin }}"{% endif %}>
                </script>
                <script src="{{ dtc_grid_purl }}"></script>
                {% for javascript in dtc_grid_datatables_js %}
                    {% if javascript.url is defined %}
                        <script src="{{ javascript.url }}"
                                {% if javascript.integrity is not empty %} integrity="{{ javascript.integrity }}"{% endif %}
                                {% if javascript.crossorigin is not empty %} crossorigin="{{ javascript.crossorigin }}"{% endif %}
                        ></script>
                    {% else %}
                        <script src="{{ javascript }}"></script>
                    {% endif %}
                {% endfor %}
            {% endblock %}
            
            {% block javascripts %}
                {% for javascript in dtc_grid_theme_js %}
                    {% if javascript.url is defined %}
                        <script src="{{ javascript.url }}"
                            {% if javascript.integrity is not empty %} integrity="{{ javascript.integrity }}"{% endif %}
                            {% if javascript.crossorigin is not empty %} crossorigin="{{ javascript.crossorigin }}"{% endif %}
                        ></script>
                    {% else %}
                        <script src="{{ javascript }}"></script>
                    {% endif %}
                {% endfor %}
                {% for javascript in dtc_grid_local_js %}
                    <script src="{{ app.request.baseUrl }}{{ javascript }}"></script>
                {% endfor %}
            {% endblock javascripts %}
        </head>
        <body>
            {{ dtc_grid.render | raw }}
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

### Registering event listeners

You can set any options supported by jquery Data Table:

    $('[data-jqtable]').each(function() {
        var options = {};
        if (this.id == 'adminusergrid') {
            options.fnRowCallback = function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
                var $row = $(nRow);
                $row.find('button.btn.remove').click(function() {
                    var id = $(this).data('id');
                    alert('remove item ' + id +' button clicked');
                    $('#admingridsourceskn').jqtable('reload');
                });
            };
        }
        $(this).jqtable(options);
    });

