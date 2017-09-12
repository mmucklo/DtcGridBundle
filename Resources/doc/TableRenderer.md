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
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    </head>
    <body>
    {{ dtc_grid.render | raw }}
    </body>
    </html>