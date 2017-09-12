DtcGridBundle
==============
[![Build Status](https://travis-ci.org/mmucklo/DtcGridBundle.svg?branch=master)](https://travis-ci.org/mmucklo/DtcGridBundle)

Generate a searchable Grid from a Doctrine ORM Entity or Doctrine MongoDB Document

  * Utilize jQuery [DataTables](https://datatables.net), [jqGrid](http://www.trirand.com/blog/)(*), or a Styled HTML Table(*)
  * Easily styled using Bootstrap
  * Customize columns and more...
  * (new as of 2.0): Easy to install, easy get started

(*) search supported on DataTables only

Render customizable tables using jqGrid, or jQuery DataTables, or in a Styled HTML Table.

Supports both Doctrine ORM and Doctrine MongoDB ODM

![Screenshot](/Resources/doc/img/screenshot.png?raw=true "Screenshot")

Installation
------------
    
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

Usage
-----

### Quick Datatables example

```php
/**
 * @Route("/users", name="app_grid_users")
 */
public function usersAction(Request $request) {
    $renderer = $this->get('dtc_grid.renderer.datatables');
    $gridSource = $this->get('dtc_grid.manager.source')->get('AppBundle:User');
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
    $renderer = $this->get('dtc_grid.renderer.table');
    $gridSource = $this->get('dtc_grid.manager.source')->get('AppBundle:User');
    $renderer->bind($gridSource);
    return $this->render('@DtcGrid/Page/datatables.html.twig', $renderer->getParams());
}
```

### Customize Columns

There's a @GridColumn annotation that lives in Dtc\GridBundle\Annotation that you place on each column you want to be visible.  Then you can specify a custom label, and or sortability if you want.  If there's no @GridColumn annotations at all, it will default to show all the columns.

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

    /**
     * @GridColumn(label="Last",sortable=true)
     * @ORM\Column(type="string")
     */
    protected $lastName;
```

### JQ Grid

To use JQ Grid, you need to specify the absolute URL, or relative/absolute path to the JQGrid files.


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
        $renderer = $this->get('dtc_grid.renderer.jq_grid');
        $gridSource = $this->get('dtc_grid.manager.source')->get('AppBundle:User');
        $renderer->bind($gridSource);
        return $this->render('@DtcGrid/Page/datatables.html.twig', $renderer->getParams());
    }
```

### Custom Entity or Document Managers

The EntityManager or DocumentManger can be customized if it's a non-default one.  Presently it has to be specified in the config.yml with one line per entity / document.
```yaml
    # config.yml
    dtc_grid:
        custom_managers:
            AppBundle\Entity\User: doctrine.orm.some_other_entity_manager
            AppBundle\Document\Event: doctrine_mongodb.odm.some_other_document_manager
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
    path('dtc_grid_bundle_dataTables_extension_css', { 'type': 'bootstrap' }),
    ] %}
        <link rel="stylesheet" href="{{ stylesheet }}" />
    {% endfor %}
    {% for stylesheet in jq_grid_stylesheets %}
        <link rel="stylesheet" href="{{ stylesheet }}" />
    {% endfor %}
{% endblock %}

{% block dtc_grid_javascripts %}
    {% for javascript in [
    path('dtc_grid_bundle_jquery'),
    path('dtc_grid_bundle_purl'),
    path('dtc_grid_bundle_dataTables'),
    path('dtc_grid_bundle_dataTables_extension', { 'type': 'bootstrap' }),
    '/bundles/dtcgrid/js/jquery.datatable/DT_bootstrap.js',
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

    $renderer = $this->get('dtc_grid.renderer.datatables'); // or whichever renderer you want to use

    $gridSource = $this->get('dtc_grid.manager.source')->get('AppBundle:User');
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
        path('dtc_grid_bundle_dataTables_extension_css', { 'type': 'bootstrap' }),
        ] %}
            <link rel="stylesheet" href="{{ stylesheet }}" />
        {% endfor %}
    {% endblock %}
    
    {% block dtc_grid_javascripts %}
        {% for javascript in [
        path('dtc_grid_bundle_jquery'),
        path('dtc_grid_bundle_purl'),
        path('dtc_grid_bundle_dataTables'),
        path('dtc_grid_bundle_dataTables_extension', { 'type': 'bootstrap' }),
        'bundles/dtcgrid/js/jquery.datatable/DT_bootstrap.js',
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
        path('dtc_grid_bundle_jquery'),
        path('dtc_grid_bundle_purl'),
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
There is additional documentation stored in `Resources/doc/`

#### Legacy:
You used to have to use a "Generator" to create the grid.source service, however this is no longer necessary, however documentation on how to use it is still available in the Resources/doc/ area.

License
-------
This bundle is under the MIT license (see LICENSE file under [Resources/meta/LICENSE](Resources/meta/LICENSE)).

Credit
------
Originally written by @dtee
Maintained by @mmucklo
