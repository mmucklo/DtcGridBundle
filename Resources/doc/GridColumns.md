Grid Columns
============

You can customize the grid customs for any type of renderer. You can also set
formatter to customize custom data for each columns fields. You can achieve this
by using twig or php.

Setting up Column Class
-----------------------

  * Example: AppBundle/Grid/Columns/UserGridColumn.php

        <?php
        namespace AppBundle\Grid\Columns;
        
        use Dtc\GridBundle\Grid\Column\TwigBlockGridColumn;
        
        use Twig_Environment;
        use ArrayObject;
        
        class UserGridColumn
            extends ArrayObject
        {
            public function __construct(Twig_Environment $twig)
            {
                $columns = array();
        
                $template = $twig->loadTemplate('AppBundle:User:_grid.html.twig');
                $env = $twig->getGlobals();
        
                $col = new TwigBlockGridColumn('firstName', 'First Name', $template, $env);
                $col->setOption('width', 75);
                $columns[] = $col;
        
                $col = new TwigBlockGridColumn('lastName', 'Last Name', $template, $env);
                $col->setOption('width', 75);
                $columns[] = $col;
        
                $col = new TwigBlockGridColumn('username', 'Username', $template, $env);
                $col->setOption('width', 75);
                $columns[] = $col;
        
                $col = new TwigBlockGridColumn('createdAt', 'Created At', $template, $env);
                $col->setOption('width', 75);
                $columns[] = $col;
        
                $col = new TwigBlockGridColumn('updatedAt', 'Updated At', $template, $env);
                $col->setOption('width', 75);
                $columns[] = $col;
        
                parent::__construct($columns);
            }
        }

Twig file for GridColumn
------------------------

   * Example: AppBundle/Resources/views/User/_grid.html.twig

            {% block firstName %}
            {% spaceless %}
            {{- obj. firstName | format_cell -}}
            {% endspaceless %}
            {% endblock %}
            
            {% block lastName %}
            {% spaceless %}
            {{- obj. lastName | format_cell -}}
            {% endspaceless %}
            {% endblock %}
            
            {% block username %}
            {% spaceless %}
            {{- obj. username | format_cell -}}
            {% endspaceless %}
            {% endblock %}
            
            {% block createdAt %}
            {% spaceless %}
            {{- obj. createdAt | format_cell -}}
            {% endspaceless %}
            {% endblock %}
            
            {% block updatedAt %}
            {% spaceless %}
            {{- obj. updatedAt | format_cell -}}
            {% endspaceless %}
            {% endblock %}

Setting Columns for GridSource
------------------------------

* XML

        <service id="bt.grid_source.user" class="Dtc\BriefTestBundle\Grid\Source\BaseManagerGridSource" public="true" scope="request">
            <argument type="service" id="bt.manager.user"></argument>
            <argument>bt.grid_source.user</argument>
    
            <call method="setColumns">
                <argument type="service" id="bt.grid_source.user.cols"></argument>
            </call>
        </service>
    
        <service id="bt.grid_source.user.cols" class="Dtc\BriefTestBundle\Grid\Column\UserColumns" public="false">
            <argument type="service" id="twig"></argument>
        </service>

* YAML

        services:
            grid.source.user:
                class: Dtc\GridBundle\Grid\Source\EntityGridSource
                arguments: ['@doctrine.orm.default_entity_manager', AppBundle\Entity\User]
                tags: [{ name: dtc_grid.source }]
                calls: [[setColumns, ['@grid.source.user.columns']]]
            grid.source.user.columns:
                class: AppBundle\Grid\Columns\UserGridColumn
                arguments: ['@twig']

