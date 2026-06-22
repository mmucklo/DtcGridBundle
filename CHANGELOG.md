8.0.0
   * Add PHP 8 native attribute support for grid configuration (`#[Grid]`, `#[Column]`). Annotations remain fully supported; converting only part of a class (e.g. `#[Grid]` with `@Column` properties, or the reverse) also works.
   * Support actions and sort as class-level attributes (`#[ShowAction]`, `#[DeleteAction]`, `#[Action]`, `#[Sort]`) on any PHP 8 version; on PHP 8.1+ they can also be nested directly: `#[Grid(actions: [new ShowAction()], sort: new Sort(column: 'name', direction: 'ASC'))]`.
   * **BC break:** The annotation classes now use natural constructors via Doctrine's `@NamedArgumentConstructor` (positional attribute args like `#[Column('Name')]` work). Code constructing them directly with a values array (`new Grid(['sort' => ...])`) must switch to the real parameters. Misconfigured annotation values (unknown keys, wrong types such as `sortable="false"`) throw a clear exception.
   * In debug mode, a stale column cache for a class with no Grid annotation/attribute is served only when it originated from a dtc_grid YAML file (cache written at container compile time, with no request-time regeneration path); a stale cache from a since-removed annotation/attribute is not resurrected, so removing a Grid marker takes effect.
   * **BC break:** Remove the unused `ColumnSource::setDebug()` / `setCacheDir()` setters. The `dtc_grid.column.source` service is configured via constructor arguments; nothing in the bundle called the setters.
   * **BC break:** Remove the `dtc_grid.command.source_list`, `dtc_grid.command.generate_grid_source`, `dtc_grid.command.source.list` and `dtc_grid.command.source.generate` service definitions along with their commands. If you referenced these service IDs directly, drop the references.
   * Annotation usage (the non-attribute path) requires the consumer application to provide a Doctrine annotation reader (`doctrine/annotations`); it is a dev/optional dependency of the bundle. The native PHP 8 `#[Grid]`/`#[Column]` attribute path needs no extra package. `ColumnSource::checkTimestamps()` remains a public static helper.
   * Drop PHP 5.6/7.0/7.1 support; minimum is now PHP 7.2. Tested on PHP 7.2 - 8.4.
   * Add support for Symfony 7 and Symfony 8; declared range is `^3.4 || ^4.4 || ^5.4 || ^6.0 || ^7.0 || ^8.0`.
   * Remove the abandoned, unused `sensio/framework-extra-bundle` dependency.
   * **BC break:** Remove `ColumnExtractionTrait`, unused since the 3.x column refactor moved its logic into the `ColumnSource` service.
   * A Grid annotation/attribute with no Column definitions (and reflection unavailable) now throws a clear configuration error instead of writing a `return false` cache file and failing every request with "Bad column cache".
   * **BC break:** Remove the deprecated-since-3.0.0 console commands: `dtc:grid:source:generate` (with the `Dtc\GridBundle\Generator` classes) relied on bundle shortcut notation and Doctrine entity-namespace aliases, which were removed in modern Symfony/Doctrine — use `@Grid`/`#[Grid]` auto-detection instead; `dtc:grid:source:list` always printed an empty list because grid sources are registered lazily per request.
   * **BC break:** DataTables default integration assets switched from Bootstrap 3 (`dataTables.bootstrap.min.*`) to Bootstrap 4 (`dataTables.bootstrap4.min.*`) to match the default Bootstrap 4 theme. Override `dtc_grid.datatables.css`/`js` if you rely on the old behavior.
   * Replace Travis CI with GitHub Actions; add PHPStan + PHP CS Fixer linting and an end-to-end grid screenshot job.
   * Expand test coverage.
7.3.0
   * Support older symfony yaml libraries that don't have parseFile.
7.2.2
   * Remove ClassMetadata type hint for now to aid differing Doctrine Versions.
7.2.1
   * Remove reference to AbstractManagerRegistry type hint for now to aid differing Doctrine Versions, per [DtcQueueBundle issue #134](https://github.com/mmucklo/DtcQueueBundle/issues/134).
7.2.0
   * Remove reference to ObjectManager type hint for now to aid differing Doctrine Versions, per [DtcQueueBundle issue #134](https://github.com/mmucklo/DtcQueueBundle/issues/134).
7.1.0
   * Issue #23 - add custom actions.
7.0.1
   * Issue #24 - fix class conflict issue.
7.0.0    
   * Issue #19 - fix deprecation notice.
   * Issue #24 - fix class load issue.
6.0.9
   * Issue #19 - fix spaceless block for twig 3.
   * Fix issues with table_attr possibly not being an array.
6.0.8
   * Issue #19 - fix typo.
6.0.7
   * Issue #19 - fix TwigExtension.
6.0.6
   * Issue #19 - fix TreeBuilder instantiation.
6.0.5
   * Issue #19 - twig 3.
6.0.4
   * Issue #19 - more Symfony 5 fixes around Commands.
   * Fix documentation around css and js.
   * Move css/js into more logical directories and rename some variables.
   * Add the ability to override local css/js via config.
   * Update css/js default versions.
   * Refactor jq_grid and datatables startup code into main.js files for each.
   * Allow for integrity and crossorigin on theme files.
6.0.3
   * Issue #19 - more fixes for Symfony 5.
6.0.2
   * Fix column source issue with timestamps.
6.0.1
   * Fix compiler pass error.
6.0.0
   * Issue #19 - Symfony 5.0 support.
   * Support for specifying grid definitions via Yaml.
   * Fix sorting so that default sorting by multiple columns should now work properly.
   * Allow formatter to be specified in annotations.
   * Deprecate ColumnSourceTrait.
5.1.1
   * Fix travis and scrutinizer.
5.1.0
   * Backwards compatibility fix for reflection allowed entities.
5.0.0
   * Support for other languages (Issue #14) - see [dtc_grid.yaml](Resources/config/dtc_grid.yaml) for more info
   * Default reflection support for all entities (using an allow_entities config - see [dtc_grid.yaml](Resources/config/dtc_grid.yaml))
   * Push grid options up into configuration file with defaults set for backwards-compatibility
   * Bootstrap4 support for jqGrid
4.2.2
   * Build for 7.3
4.2.1
   * Change way to get baseUrl for assets: PR #12
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
    
    
    
