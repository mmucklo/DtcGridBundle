4.2.0
   * Hack to fix on postgres
   * Create a default yaml file to be used with symfony-recipies
4.1.0
   * Bootstrap 4 full compatibility
   * Default columns when showing grid
4.0.6
   * fix null in show
4.0.5
   * More reactive Refresh button
4.0.4
   * Update exception message
4.0.3
   * Make twig engine dependency injected during the compiler pass so that just requiring the service wont break a fresh Symfony 4 install.
4.0.2
   * Require templating, fix a text wrap issue during "show"
4.0.1
   * Make services public except the twig extension
4.0.0
   * Remove SensioGeneratorBundle dependency for symfony4 support
3.3.0
   * Expose some of the compiler pass logic as a static method
3.2.2
   * Fix bug in error message
3.2.1
   * Fix 7.0 build
3.2.0
   * Support specifying an order for columns in the Column annotation
   * Support a default sort column and direction in the Grid annotation
3.1.3
   * Support for sensio/framework-extra-bundle 5
3.1.2
   * Change to templating.engine.twig due to bug
3.1.1
   * Added some css for the buttons
   * move dtc_grid_spinner.css to dtc_grid.css
3.1.0
   * Added a spinner to the delete action
   * Added new Refresh button to bottom on datatables grid

3.0.0
   * Requirement - add Dtc\GridBundle\Annotation\Grid for all auto-detected Entities or Documents
   * Remove custom_managers section from config.yml
     * Now auto-detected
        * Custom document/entity managers should now be auto-detected based on what's registered in the doctrine registry
     * Remove "custom_managers:" section from your config.yml (if present)
   * New grid route for auto-rendering of grid pages based on parameters
     * Example:
   * Removed renderer services as they can be accessed via the RendrerFactory
     * $container->get('dtc_grid.renderer.datatables') -> $container->get('dtc_grid.renderer.factory')->create('datatables');
     * $container->get('dtc_grid.renderer.jq_grid') -> $container->get('dtc_grid.renderer.factory')->create('jq_grid');
     * $container->get('dtc_grid.renderer.table') -> $container->get('dtc_grid.renderer.factory')->create('table');
   * Mark old generator code as deprecated
   * Changing bootstrap_css and boostrap_js to theme_css array and theme_js array
     * In config.yml you can specify the location of the theme_css (formerly bootstrap_css)

Before:
```yaml
dtc_grid:
    bootstrap:
        css: http://some_relative_or_fully_qualified_url
        js: http://some_relative_or_fully_qualified_url
```

After:
```yaml
    theme:
        css:
            - http://some_relative_or_fully_qualified_url
            - http://some_other_relative_or_fully_qualified_url
        js:
            - http://some_relative_or_fully_qualified_url
            - http://some_other_relative_or_fully_qualified_url
```

   *
     * It still defaults to bootstrap 3.3.7 on maxcdn for the time being
   * Added a type parameter for DataTables so that other types of styling (bootstrap4, etc) can be used
   * Added the ability to override the url of jQuery used
   * Removed embedded purl, Datatables, and jQuery
      * Added ability to change URLs for purl, jQuery, and DataTables via config, defaulting to CDN versions
   * Renamed annotations
Old:
```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation\GridColumn;

/**
 * Class User
 * @ORM\Entity
 * @package AppBundle\Entity
 */
class User {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @GridColumn
     * @ORM\Column(type="string")
     */
    protected $firstName;

```

New:
```php
<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation as Grid;

/**
 * Class User
 * @ORM\Entity
 * @Grid\Grid()
 * @package AppBundle\Entity
 */
class User {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Grid\Column
     * @ORM\Column(type="string")
     */
    protected $firstName;

```
   * Added @Grid\Grid annotation
   * Added action Annotations
      * Added a ShowAction
      * Added a DeleteAction
   * Deleted a bunch of images / css for doing pagination, and old formatting code (now uses bootstrap)
   * Created a new CSS file for spinner on Show
   * If you want to able searching you have to set searchable=true on the @Grid\Column annotation
Example:
```php
    /**
     * @Grid\Column(searchable=true)
     * @ORM\Column(type="string")
     */
    protected $firstName;

```
   
   
   
2.3.0
   * Refactor: rename setColumns to addColumns and add it to GridSourceInterface

2.2.0
   * Add a new factory RendererFactory to support multiple grid renderers on the same page

2.1.0
   * Add page_div_style parameter for customizing the style of the main div when rendering by page
   * Prefix all template parameters with dtc_grid_ as a form of namespacing

2.0.0
   * Reads Annotations for Entity column customization
   * Smart GridSourceManager to autogenerate grid sources
   * Refactor the templates to take advantage of common code
   * Can use default templates instead of creating ones own each time
    
    
    
