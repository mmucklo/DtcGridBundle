Grid Columns
============

You can customize the grid customs for any type of renderer. You can also set
formatter to customize custom data for each columns fields. You can acheive this
by using twig or php.

Setting up Column Class
-----------------------


Setting Columns for GridSource
------------------------------
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