# Html Grid Renderer

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

    /**
     * @Template
     * @Route("/users_html", name="dtc_grid_users_html")
     */
    public function usersHtmlAction(Request $request) {
        $renderer = $this->get('dtc_grid.renderer.table');
        $gridSource = $this->get('grid.source.user');
        $columns = $gridSource->getColumns();
        $renderer->bind($gridSource);

        return array('dtc_grid' => $renderer);
    }
    
    
## For your template

Resources/views/your_controller/usersHtml.html.twig

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