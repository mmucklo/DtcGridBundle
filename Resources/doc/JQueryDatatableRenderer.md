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
	            'bundles/odlshadow/lib/bootstrap/css/*min.css'
	            'bundles/dtcgrid/lib/DataTables/media/jquery.dataTables*.css'
	            'bundles/odlshadow/lib/jquery-ui/css/ui-lightness/*min.css'
	            'bundles/dtcgrid/css/datatable.bootstrap.css'
	            'bundles/odlshadow/css/*.less'

	            combine=false
	            filter='cssrewrite, lessphp'
	            output="generated/css/shadow_*.css"
	        %}
	            <link rel="stylesheet" href="{{ asset_url }}" />
	        {% endstylesheets %}

	        {% javascripts
	            'bundles/odlshadow/lib/jquery-ui/js/jquery-1.8.2.js'
	            'bundles/odlshadow/lib/highcharts/js/highcharts.js'
	            'bundles/odlshadow/lib/jquery-ui/js/jquery-ui-1.9.0.custom.min.js'

	            'bundles/odlshadow/lib/ajaxform/jquery.ajaxform.js'
	            'bundles/odlshadow/lib/ajaxform/jquery.ajaxform.twitterError.js'

	            'bundles/dtcgrid/lib/DataTables/media/js/jquery.dataTables.min.js'

	            'bundles/dtcgrid/js/jquery.datatable/DT_bootstrap.js'
	            'bundles/dtcgrid/js/jquery.datatable/jquery.jqtable.js'

	            'bundles/odlshadow/js/*.js'

	            combine=false
	            output="generated/js/shadow_*.js"
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
