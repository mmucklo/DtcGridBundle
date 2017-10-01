3.0.0
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
   * Added @Grid\Grid annotation for actions
  
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
    
    
    