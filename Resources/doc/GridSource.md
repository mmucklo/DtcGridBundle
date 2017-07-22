Grid Source
===========
The grid source is the glue between the renderer and your backend data.

The base AbstractGridSource class forms the foundation on which both DocumentGridSource and EntityGridSource are built.

Any of these can be overridden, or the Dtc\GridBundle\Grid\Source\GridSourceInterface.php can simply be implemented.

## Service

The generator creates a GridSource service with auto-discovered or pre-populated columns.

Example:


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
            
## Custom Columns

To use custom columns, on either 