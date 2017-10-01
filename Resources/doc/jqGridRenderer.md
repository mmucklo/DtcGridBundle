# JqGrid Renderer

## 1. Create a GridSource service

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
    * Dtc\GridBundle\Grid\Source\EntityGridSource
    * @doctrine.orm.default_entity_manager

## For your controller:

Add the following code to your controller:

    /**
     * @Template
     * @Route("/users_jq", name="dtc_grid_users_jq")
     */
    public function usersJqAction(Request $request) {

        $renderer = $this->get('grid.renderer.jq_grid');
        $gridSource = $this->get('grid.source.user');
        $renderer->bind($gridSource);

        return array('dtc_grid' => $renderer);
    }

# A sample template

Resources/views/your_controller/usersJq.html.twig

    <!DOCTYPE html>
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
        {% block stylesheets %}
            {% for stylesheet in [
            'path/to/prettify.css',
            'path/to/ui.jqgrid-bootstrap.css',
            ] %}
                <link rel="stylesheet" href="{{ stylesheet }}" />
            {% endfor %}
        {% endblock %}
        {% block javascripts %}
            {% for javascript in [
            path('dtc_grid_jquery'),
            path('dtc_grid_purl'),
            'path/to/i18n/grid.locale-en.js',
            'path/to/jquery.jqGrid.js'
            ] %}
                <script type="text/javascript" src="{{ javascript }}"></script>
            {% endfor %}
        {% endblock %}
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    
    </head>
    <body>
    {{ dtc_grid.render | raw }}
    </body>
    </html>