<?php
namespace Dtc\GridBundle\Manager;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridSourceManager
{
	protected $sources;

	public function __construct() {
		$this->sources = array();
	}

	public function add($id, GridSourceInterface $gridSource) {
		$this->sources[$id] = $gridSource;
	}

	public function get($id) {
		if (isset($this->sources[$id])) {
			return $this->sources[$id];
		}
	}

	public function all() {
		return $this->sources;
	}
}