<?php
namespace Dtc\GridBundle\Grid\Renderer;

class JQueryGridRenderer
	extends TwigGridRenderer
{
	private $options = array(
		'datatype' => 'json',
		'jsonReader' => array(
    		'root' => 'rows',
    		'total' => 'total',
    		'records' => 'records',
	        'page' => 'page',
			'repeatitems' => false
		),

		'url' => null,
		'cell' => '',
		'width' => '840',
		'height' => '500',
		'loadui' => 'disable',
		'altRows' => true,
		'viewrecords' => true,
		'multiselect' => true,

		// Paging params
		'prmNames' => array(
			'page' => "page",
			'rows' => "limit",
			'sort' => "sort_column",
			'order' => "sort_order"
		),

		// Pager Config
		'pager' => "grid-pager",
		'recordtext' => "View {0} - {1} of {2}",
		'emptyrecords'=> "No records to view",
		'loadtext' => "Loading...",
		'pgtext' => "Page {0} of {1}"
	);

	protected function afterBind() {
		$id = $this->gridSource->getId();

/*		$this->options['prmNames'] = array(
			'page' => "{$id}_page",
			'rows' => "{$id}_limit",
			'sort' => "{$id}_sort_column",
			'order' => "{$id}_sort_order"
		); */

		$this->options['pager'] = "{$id}-pager";

		$params = array('id' => $id);
		$url = $this->router->generate('dtc_grid_grid_data', $params);
		$this->options['url'] = $url;

		foreach ($this->gridSource->getColumns() as $column) {
			$info['label'] = $column->getLabel();
			$info['name'] = $column->getField();

			$this->options['colModel'][] = $info;
		}
	}

	public function getGridOptions() {
		return $this->options;
	}

	public function getData() {
		$columns = $this->gridSource->getColumns();
		$gridSource = $this->gridSource;
		$records = $gridSource->getRecords();

		$retVal = array(
			'page' => $gridSource->getPager()->getCurrentPage(),
			'total' => $gridSource->getPager()->getTotalPages(),
			'records' => $gridSource->getCount(),
			'id' => '0',		// unique id
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
		$id = $this->gridSource->getId();

		$params = array(
			'options' => $this->options,
			'id' => $id
		);

		$template = 'DtcGridBundle:Grid:jquery_grid.html.twig';
		return $this->twigEngine->render($template, $params);
	}
}
