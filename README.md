DtcGridBundle
==============
[![Build Status](https://travis-ci.org/mmucklo/DtcGridBundle.svg?branch=master)](https://travis-ci.org/mmucklo/DtcGridBundle)

Generate a searchable Grid from a Doctrine ORM Entity or Doctrine MongoDB Document

  * Utilize jQuery [DataTables](https://datatables.net) or [jqGrid](http://www.trirand.com/blog/)(*)
  * Easily styled using Bootstrap
  * Customize columns and more...

(*) search supported on DataTables only

Render customizable tables using jqGrid, or jquery Data Tables.

Supports both Doctrine ORM and Doctrine MongoDB ODM

![Screenshot](/Resources/doc/img/screenshot.png?raw=true "Screenshot")

Installation
------------
    
Add this to your AppKernel.php file:

```
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

If you have your entities or documents already defined, start with this command:

	bin/console dtc:grid:source:generate <entity_or_document> [--columns]

  * Use switch --columns if you want to customize columns

You can use symfony's console to view registered grid sources:

	bin/console dtc:grid:source:list

Documentation
-------------

There is additional documentation stored in `Resources/doc/`

License
-------
This bundle is under the MIT license (see LICENSE file under [Resources/meta/LICENSE](Resources/meta/LICENSE)).

Credit
------
Originally written by @dtee
Maintained by @mmucklo