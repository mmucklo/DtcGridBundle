DtcGridBundle
==============

[![Build Status](https://travis-ci.org/mmucklo/DtcGridBundle.svg?branch=master)](https://travis-ci.org/mmucklo/DtcGridBundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/mmucklo/DtcGridBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/mmucklo/DtcGridBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/mmucklo/DtcGridBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/mmucklo/DtcGridBundle/?branch=master)

Generate a searchable Grid from a Doctrine ORM Entity or Doctrine MongoDB Document

  * Utilize jQuery [DataTables](https://datatables.net), [jqGrid](http://www.trirand.com/blog/)(\*), or a Styled HTML Table(\*)
  * Easily styled using Bootstrap
  * Customize columns and more...
  * (new as of 2.0): Easy to install, easy get started

(*) search functionality supported on DataTables only

Render customizable tables using jqGrid, or jQuery DataTables, or in a Styled HTML Table.

Supports both Doctrine ORM and Doctrine MongoDB ODM

![Screenshot](/Resources/doc/img/screenshot.png?raw=true "Screenshot")

Installation
------------

### Symfony 4/5

```
    composer.phar require mmucklo/grid-bundle
```

You may see something like this (please answer 'y' to the question if prompted):

```
  -  WARNING  mmucklo/grid-bundle (>=5.0): From github.com/symfony/recipes-contrib:master
    The recipe for this package comes from the "contrib" repository, which is open to community contributions.
    Review the recipe at https://github.com/symfony/recipes-contrib/tree/master/mmucklo/grid-bundle/4.0

    Do you want to execute this recipe?
    [y] Yes
    [n] No
    [a] Yes for all packages, only for the current installation session
    [p] Yes permanently, never ask again for this project
    (defaults to n): y
  - Configuring mmucklo/grid-bundle (>=5.0): From github.com/symfony/recipes-contrib:master
```

### symfony 2/3
    
Add this to your AppKernel.php file:

```php
    public function registerBundles()
    {
        $bundles = [
            ...
            new \Dtc\GridBundle\DtcGridBundle(),
            ...
        ]
```

Add this to your app/config/routing.yml file:

```yaml
dtc_grid:
    resource: '@DtcGridBundle/Resources/config/routing.yml'
```

Usage
-----

### Get Started

After installation, all entities and documents that have a Grid annotation should be available off the dtc_grid route:

(NOTE: symfony5 example below, for symfony2/3, the namespace for the class may be different - e.g. AppBundle instead of App)

There are two recommended ways to setup a grid for a page, through Annotations, or through Reflection

#### Reflection

Automatic Grid setup is possible by setting the reflections: allowed_entities: [...] parameter in the config/packages/dtc_grid.yaml configuration file (or config.yml for symfony <= 3.4)

```yaml
dtc_grid:
    reflection:
        # allow any entity to be shown via the /dtc_grid route
        # allowed_entities: ~, '*', or an array of entity names [ 'App:Product', 'App:Category', ... ]
        #  ~ - no entities allowed for reflection
        #  * - all entities allowed for reflection
        #  [ 'App:Product', 'App:Category' ] - only App:Product and App:Category allowed
        allowed_entities: ~
```

#### (New in 6.0) grid yaml file definition

You can place the grid column definitions in a custom yaml file:

##### Step 1 - create the yaml file:
```yaml
# File location(s):
#   - symfony 4+: config/dtc_grid/*.yaml (will load all *.yaml files in this directory)
#   - symfony 2/3: src/*/*/Resources/config/dtc_grid.yaml (will only load files with this name or the name 'dtc_grid.yml')
#   - custom (bundles): add the following to a CompilerPass:
#        # $cacheDir = $container->getParameter('kernel.cache_dir');
#        \Dtc\GridBundle\Grid\Source\ColumnSource::cacheClassesFromFile($cacheDir, $filename);
App\User:
  columns:
    id:
      sortable: true
    username:
      sortable: true
      searchable: true
    email:
      searchable: true
    createdAt:
      sortable: true
    updatedAt:
      sortable: true
    status:
      sortable: true
      searchable: true
  actions:
    -
      label: Show
      type: show
      route: dtc_grid_show
    -
      label: Archive
      type: delete
      route: dtc_grid_delete
  sort:
    id: ASC

App\Article:
  columns:
    id:
      sortable: true
    userId:
      sortable: true
    createdAt:
      sortable: true
    updatedAt:
      sortable: true
    subject:
      searchable: true
    status:
      sortable: true
  actions:
    -
      label: Show
      type: show
      route: dtc_grid_show
  sort:
    createdAt: DESC
```

#### Annotation Simple Example

Note: this example still uses reflection to discover the columns, however if you want to customize the columns shown, and even which ones are shown, read on below in the section titled Customize Columns

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation as Grid;

/**
 * Class User
 * @Grid\Grid
 * @ORM\Entity
 * @package App\Entity
 */
class User {
    //...
}
```

Now after adding the annotation, you may need to do:

```
    bin/console cache:clear
    bin/console cache:warmup
```

## To access the grid:

You can access the grid without embedding it anywhere by going to the following url(s):

  * Route: dtc_grid/dtc_grid/grid?class=App:User
  * Parameters:
      * class=[document_or_entity]
         * This can be in either a fully-namespaced class name or symfony-style entity/document format separated by ':'
            * e.g. either: 'App:User' or 'App\Entity\User'
      * type=[datatables|table|jq_grid]

#### Examples:
```
# A default HTML-based table
# (warning: if your table is large, skip this example, and try the paginated datatables path below)


# Datatables
/dtc_grid/grid?class=App:User&type=datatables

# Full Class syntax 
/dtc_grid/grid?class=App\Entity\User&type=datatables
 
# MongoDB ODM examples
/dtc_grid/grid?class=App:Event&type=datatables
/dtc_grid/grid?class=App\Document\Event&type=datatables

# Other types of tables
/dtc_grid/grid?class=App:Event&type=jq_grid
/dtc_grid/grid?class=App:Event&type=table

 
```

#### Note - Security
For production systems you may want to add the path ^/dtc_grid to your security.yml, and make it firewalled / admin-only:

```yaml
security:
    # ...
    access_control:
        # ...
        - { path: ^/dtc_grid, roles: ROLE_ADMIN }

```

### Adding actions

There are presently several actions that you can add to your grid that will appear under a final column called "Actions"

These must be added as annotations to the Grid annotation.

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation as Grid;

/**
 * Class User
 * @Grid\Grid(actions={@Grid\ShowAction(), @Grid\DeleteAction()})
 * @ORM\Entity
 * @package App\Entity
 */
class User {
    //...
}
```

### A more custom Route

```php
/**
 * @Route("/users", name="app_grid_users")
 */
public function usersAction(Request $request) {
    $renderer = $this->get('dtc_grid.renderer.factory')->create('datatables');
    $gridSource = $this->get('dtc_grid.manager.source')->get('App:User');
    $renderer->bind($gridSource);
    return $this->render('@DtcGrid/Page/datatables.html.twig', $renderer->getParams());
}
```

### Changing the renderer

```php
/**
 * @Route("/users_table", name="app_grid_users_table")
 */
public function usersAction(Request $request) {
    $renderer = $this->get('dtc_grid.renderer.factory')->create('table');
    $gridSource = $this->get('dtc_grid.manager.source')->get('App:User');
    $renderer->bind($gridSource);
    return $this->render('@DtcGrid/Page/datatables.html.twig', $renderer->getParams());
}
```

### Customize Columns

There's a @Column annotation that lives in Dtc\GridBundle\Annotation that you place on each column you want to be visible.  Then you can specify a custom label, and or sortability if you want.  If there's no @GridColumn annotations at all, it will default to show all the columns.

```php

<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Dtc\GridBundle\Annotation as Grid;

/**
 * Class User
 * @Grid\Grid
 * @ORM\Entity
 * @package App\Entity
 */
class User {

    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Grid\Column(searchable=true)
     * @ORM\Column(type="string")
     */
    protected $firstName;

    /**
     * @Grid\Column(label="Last", sortable=true, searchable=true)
     * @ORM\Column(type="string")
     */
    protected $lastName;
```

### Customize jQuery, Purl, DataTables

Customization of the versions of jQuery, Purl, and DataTables can be done in config.yml

```yaml
dtc_grid:
    theme: # theme defaults to bootstrap 3
        css:
            - 'path_or_url_to/bootstrap_or_any_other.css'
            - 'etc. as necessary'
        js:
            - 'path_or_url_to/any_javascript_needed_for_theme.js'
            - 'etc. as necessary'            
    purl: 'path_or_url_to/purl.js' # presently defaults to v2.3.1 hosted on cdnjs
    jquery: # presently defaults to 3.2.1 hosted on jquery's code.jquery.com cdn
        url: 'path_or_url_to/jquery.js'
        integrity: ~ # or an integrity for the file
        crossorigin: ~ # or what goes in the crossorigin section of the script tag
    datatables: # presently defaults to 1.10.16 hosted on cdn.datatables.net
        css:
            - 'path_or_url_to/datatables.css'
            - 'path_or_url_to/any_other_needed.css'
        js:
            - 'path_or_url_to/datatables.js'
            - 'path_or_url_to/datatables_theme.js'
            - 'etc. as necessary'
```

### JQ Grid

To use JQ Grid, you need to specify the absolute URL, or relative/absolute path to the JQGrid files.

As JQ Grid has a different license than this bundle, there are no defaults provided.

```yaml
# config.yml
dtc_grid:
    jq_grid:
        css:
            - 'path_or_url_to/prettify.css'
            - 'path_or_url_to/ui.jqgrid-bootstrap.css'
        js:
            - 'path_or_url_to/grid.locale-en.js'
            - 'path_or_url_to/jquery.jqGrid.js'
```

```php
    /**
     * @Route("/users_table", name="app_grid_users_table")
     */
    public function usersAction(Request $request) {
        $renderer = $this->get('dtc_grid.renderer.factory')->create('jq_grid');
        $gridSource = $this->get('dtc_grid.manager.source')->get('App:User');
        $renderer->bind($gridSource);
        return $this->render('@DtcGrid/Page/datatables.html.twig', $renderer->getParams());
    }
```


### Customize Bootstrap

```yaml
    # config.yml
    dtc_grid:
        bootstrap:
            css: path_or_url_to_bootstrap.css
            js: path_or_url_to_bootstrap.js
```

### Multiple Grids on the same page

The RendererFactory needs to be used if you want to render multiple grids on the same page.

There are presently three typs of renderers it supports:

   * datatables
   * jq_grid
   * table

```php
    /**
     * List jobs in system by default.
     *
     * @Route("/jobs/")
     */
    public function jobsAction()
    {
        $rendererFactory = $this->get('dtc_grid.renderer.factory');
        $renderer = $rendererFactory->create('datatables');
        $gridSource = $this->get('dtc_grid.manager.source')->get('Dtc\\QueueBundle\\Documents\\Job');
        $renderer->bind($gridSource);
        $params = $renderer->getParams();

        $renderer2 = $rendererFactory->create('datatables');
        $gridSource2 = $this->get('dtc_grid.manager.source')->get('Dtc\\QueueBundle\\Documents\\JobArchive');
        $renderer2->bind($gridSource2);
        $params2 = $renderer2->getParams();

        $params['archive_grid'] = $params2['dtc_grid'];
        return $this->render('@DtcQueue/Queue/jobs.html.twig', $params);
    }
```


#### Twig file rendering the multiple grids on the same page

jobs.html.twig:
```twig
{% extends "DtcGridBundle:Page:datatables.html.twig" %}

{% block grid %}
    <h3>Live Jobs</h3>
    {{ dtc_grid.render | raw }}
    <h3>Archived Jobs</h3>
    {{ archive_grid.render | raw }}
{% endblock %}
```

#### You can even render multiple types of grids on the same page

```php
    /**
     * List jobs in system by default.
     *
     * @Route("/jobs/")
     */
    public function jobsAction()
    {
        $rendererFactory = $this->get('dtc_grid.renderer.factory');
        $renderer = $rendererFactory->create('jq_grid');  // NOTE THE DIFFERENT GRID TYPE
        $gridSource = $this->get('dtc_grid.manager.source')->get('Dtc\\QueueBundle\\Documents\\Job');
        $renderer->bind($gridSource);
        $params = $renderer->getParams();

        $renderer2 = $rendererFactory->create('datatables');
        $gridSource2 = $this->get('dtc_grid.manager.source')->get('Dtc\\QueueBundle\\Documents\\JobArchive');
        $renderer2->bind($gridSource2);
        $params2 = $renderer2->getParams();

        $params['archive_grid'] = $params2['dtc_grid'];
        return $this->render('@DtcQueue/Queue/jobs.html.twig', $params);
    }
```

jobs.html.twig (a little complicated as styles and javascript has to be included for both grid types, although this isn't necessary if you use the "table" type renderer as it's CSS and Javascript overlaps with the "datatables" renderer):
```twig
{% extends '@DtcGrid/layout.html.twig' %}

{% block dtc_grid_stylesheets %}
    {% for stylesheet in [
    path('dtc_grid_dataTables_extension_css', { 'type': 'bootstrap' }),
    ] %}
        <link rel="stylesheet" href="{{ stylesheet }}" />
    {% endfor %}
    {% for stylesheet in jq_grid_stylesheets %}
        <link rel="stylesheet" href="{{ stylesheet }}" />
    {% endfor %}
{% endblock %}

{% block dtc_grid_javascripts %}
    {% for javascript in [
    path('dtc_grid_jquery'),
    path('dtc_grid_purl'),
    path('dtc_grid_dataTables'),
    path('dtc_grid_dataTables_extension', { 'type': 'bootstrap' }),
    'js/jquery.datatable/DT_action.js',
    '/bundles/dtcgrid/js/jquery.datatable/jquery.jqtable.js'] %}
        <script type="text/javascript" src="{{ javascript }}"></script>
    {% endfor %}
    {% for javascript in jq_grid_javascripts %}
        <script type="text/javascript" src="{{ javascript }}"></script>
    {% endfor %}
{% endblock %}

{% block grid %}
    <h3>Live Jobs</h3>
    {{ dtc_grid.render | raw }}
    <h3>Archived Jobs</h3>
    {{ archive_grid.render | raw }}
{% endblock %}
```

### Customize/Embed Grid

To customize the grid's CSS/Javascript, or embed it into an existing page, follow the example below:

```php
/**
 * @Route("/users_custom", name="app_grid_users_custom")
 */
public function usersCustomAction(Request $request) {
    // other setup, etc.
    // Assuming you have a variable called "$params"

    $renderer = $this->get('dtc_grid.renderer.factory')->create('datatables'); // or whichever renderer you want to use

    $gridSource = $this->get('dtc_grid.manager.source')->get('App:User');
    $renderer->bind($gridSource);

    $renderer->getParams($params); // This will add the grid-specific params (mainly 'grid', and the bootstrap urls)
    
    // Alternatively you can do
    // $dataGridParams = $renderer->getParams();
    // $myParams['my_grid'] = $dataGridParams['dtc_grid'];

    // render your page
    return $this->render('@App/Something/somepage.html.twig', $params);
}
```

#### Datatables Twig Example:
```twig
<html>
<head>
    <!-- Setup all CSS and Javascript manually -->
    <!-- Any of these below could be modified / customized -->
    <link rel="stylesheet" href="{{ dtc_grid_bootstrap_css }}">
    {% block dtc_grid_stylesheets %}
        {% for stylesheet in [
        path('dtc_grid_dataTables_extension_css', { 'type': 'bootstrap' }),
        ] %}
            <link rel="stylesheet" href="{{ stylesheet }}" />
        {% endfor %}
    {% endblock %}
    
    {% block dtc_grid_javascripts %}
        {% for javascript in [
        path('dtc_grid_jquery'),
        path('dtc_grid_purl'),
        path('dtc_grid_dataTables'),
        path('dtc_grid_dataTables_extension', { 'type': 'bootstrap' }),
        'js/jquery.datatable/DT_action.js',
        'bundles/dtcgrid/js/jquery.datatable/jquery.jqtable.js'] %}
            <script type="text/javascript" src="{{ javascript }}"></script>
        {% endfor %}
    {% endblock %}
    <script src="{{ dtc_grid_bootstrap_js }}"></script>
</head>
<body>

<!-- .... -->
{# This is the most important part - 'dtc_grid.render' should be 'dtc_grid.render' or 'my_grid.render', or whatever you called it if you change the parameter's name #}
{{ dtc_grid.render | raw }}

<!-- ... -->
</body>
</html>
```

#### JQGrid Twig Example:
```twig
<html>
<head>
    <!-- Setup all CSS and Javascript manually -->
    <!-- Any of these below could be modified / customized -->
    <link rel="stylesheet" href="{{ dtc_grid_bootstrap_css }}">
    {% block dtc_grid_stylesheets %}
        {% for stylesheet in [
          'path_or_url_to/prettify.css',
          'path_or_url_to/ui.jqgrid-bootstrap.css'
        ] %}
            <link rel="stylesheet" href="{{ stylesheet }}" />
        {% endfor %}
    {% endblock %}
    
    {% block dtc_grid_javascripts %}
        {% for javascript in [
        path('dtc_grid_jquery'),
        path('dtc_grid_purl'),
        'path_or_url_to/grid.locale-en.js',
        'path_or_url_to/jquery.jqGrid.js'] %}
            <script type="text/javascript" src="{{ javascript }}"></script>
        {% endfor %}
    {% endblock %}
    <script src="{{ dtc_grid_bootstrap_js }}"></script>
</head>
<body>

<!-- .... -->
{# This is the most important part - 'dtc_grid.render' should be 'dtc_grid.render' or 'my_grid.render', or whatever you called it if you change the parameter's name #}
{{ dtc_grid.render | raw }}

<!-- ... -->
</body>
</html>
```

#### Table Twig Example:
```twig
<html>
<head>
    <!-- Setup all CSS and Javascript manually -->
    <!-- Any of these below could be modified / customized -->
    <link rel="stylesheet" href="{{ dtc_grid_bootstrap_css }}">
    <script src="{{ dtc_grid_bootstrap_js }}"></script>
</head>
<body>

<!-- .... -->
{# This is the most important part - 'dtc_grid.render' should be 'dtc_grid.render' or 'my_grid.render', or whatever you called it if you change the parameter's name #}
{{ dtc_grid.render | raw }}

<!-- ... -->
</body>
</html>
```

Documentation
-------------
There is additional (somewhat legacy at this point) documentation stored in `Resources/doc/`

#### Legacy:
You used to have to use a "Generator" to create the grid.source service, however this is no longer necessary, however documentation on how to use it is still available in the Resources/doc/ area.

License
-------
This bundle is under the MIT license (see LICENSE file under [Resources/meta/LICENSE](Resources/meta/LICENSE)).

Credit
------
Originally written by @dtee
Maintained by @mmucklo
