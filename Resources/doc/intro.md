# Introduction

## Simple Grid

If you want to simple grid with built in Grid Source, no customizations to grid columns:

It is recommended to use the generator, otherwise see one of:
    
    * [jQuery DataTables](/Resources/doc/jQueryDataTableRenderer.md)
    * [jqGrid](/Resources/doc/jqGridRenderer.md)
    * [Html](/Resources/doc/HtmlGridRenderer.md)

## Custom Columns

If you want to use the built in Grid Source with custom columns:

* First setup the custom columns such as in: [GridColumns](/Resources/doc/GridColumns.md)

Then setup your grid source as below:

    grid.source.user:
        class: Dtc\GridBundle\Grid\Source\EntityGridSource
        arguments: ['@doctrine.orm.default_entity_manager', AppBundle\Entity\User]
        tags: [{ name: dtc_grid.source }]
        calls: [[setColumns, ['@grid.source.user.columns']]]
    grid.source.user.columns:
        class: AppBundle\Grid\Columns\UserGridColumn
        arguments: ['@twig']

## Custom GridSource, Custom Columns


If you want to override the existing Grid Source, with custom columns:

    You can extend:
    
          * Dtc\GridBundle\Grid\Source\DocumentGridSource
          * Dtc\GridBundle\Grid\Source\EntityGridSource

YAML:

    grid.source.user:
        class: Path\To\Your\OverriddenGridSource
        arguments: ['@doctrine.orm.default_entity_manager', AppBundle\Entity\User]
        tags: [{ name: dtc_grid.source }]
        calls: [[setColumns, ['@grid.source.user.columns']]]
    grid.source.user.columns:
        class: AppBundle\Grid\Columns\UserGridColumn
        arguments: ['@twig']

## New GridSource

If you want to create a new Grid Source:

    You can extend:

            * Dtc\GridBundle\Grid\Source\AbstractGridSource
            
    Or you can implement

            * Dtc\GridBundle\Grid\Source\GridSourceInterface
                
YAML:
    
        grid.source.user:
            class: Path\To\Your\NewGridSource
            arguments: ['@your_services', 'etc']
            tags: [{ name: dtc_grid.source }]
            calls: [[autoDiscoverColumns]]
