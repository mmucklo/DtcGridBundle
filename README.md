DtcGridBundle
==============

Render customizable tables using jqGrid, or jquery Data Tables.

Supports both Doctrine ORM and Doctrine MongoDB ODM

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

	./app/console dtc:grid:source:generate <entity_or_document>

You can use symfony's console to view registered grid sources:

	./app/console dtc:grid:source:list

Documentation
-------------

There is additional documentation stored in `Resources/doc/`

License
-------
This bundle is under the MIT license (see LICENSE file under [Resources/meta/LICENSE](Resources/meta/LICENSE)).

Credit
------
Originally written by @dtee
