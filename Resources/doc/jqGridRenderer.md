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
    
        {% for stylesheet in dtc_grid_jq_grid_css %}
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
            {% for javascript in dtc_grid_jq_grid_js %}
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