<?php
namespace Dtc\GridBundle\Grid\Renderer;

class JQueryGridRenderer 
	extends TwigGridRenderer
{
	private $options = array(
		'datatype' => 'json',
		'jsonReader' => array(
			'repeatitems' => false
		),
		
		'url' => null,
		'root' => 'rows',
		'total' => 'total',
		'records' => 'records',
		'cell' => '',
		'height' => '500',
		'loadui' => 'block',
		'altRows' => true,
		'viewrecords' => true,
		'multiselect' => true,
		
		// Paging params
		'prmNames' => array(
			'page' => "grid_page",
			'rows' => "grid_limit",
			'sort' => "grid_sort_column",
			'order' => "grid_sort_order"
		),
		
		// Pager Config
		'pager' => "grid-pager",
		'recordtext' => "View {0} - {1} of {2}",
		'emptyrecords'=> "No records to view",
		'loadtext' => "Loading...",
		'pgtext' => "Page {0} of {1}"
	);
	
	protected function afterBind() {
		$id = $this->grid->getId();
		
		$this->options['prmNames'] = array(
			'page' => "{$id}_page",
			'rows' => "{$id}_limit",
			'sort' => "{$id}_sort_column",
			'order' => "{$id}_sort_order"
		);
		
		$this->options['pager'] = "{$id}-pager";
		
		foreach ($this->grid->getColumns() as $column) {
			$info['name'] = $column->getLabel();
			$info['field'] = $column->getField();
			
			$this->options['colModel'][] = $info;
		}
	}
	
	public function getGridOptions() {
		return $this->options;
	}
	
	public function getData() {
		$columns = $this->grid->getColumns();
		$gridSource = $this->grid->getGridSource();
		$records = $gridSource->getRecords();
		
		$retVal = array(
			'page' => $gridSource->getPager()->getCurrentPage(),
			'total' => $gridSource->getPager()->getTotalPages(),
			'records' => count($records),
			'id' => 'name',		// unique id
			
		);
		
		foreach ($records as $record)
		{
			$info = array();
			foreach ($columns as $column) {
				$info[$column->getField()] = $column->format($record);
			}
			
			$retVal['rows'][] = $info;
		}
		
		return $retVal;
	}
	
	public function render() {
		
		$params = array(
			'options' => $this->options,
			'id' => $this->grid->getId()
		);
		
		$template = 'DtcGridBundle:Grid:jquery_grid.html.twig';
		return $this->twigEngine->render($template, $params);
	}
}
