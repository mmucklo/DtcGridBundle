<?php
namespace Dtc\GridBundle\Grid\Column;

class GridColumn
{
	protected $formatter;
	protected $field;
	protected $label;
	
	public function __construct($field, $label, $formatter = null) {
		$this->field = $field;
		$this->label = $label;
		$this->formatter = $formatter;
	}
	
	public function format($object) {
		if ($this->formatter) {
			
		}
		else {
			return $this->_format($object);
		}
	}
	
	protected function _format($object) {
		if (is_array($object)){
			if (isset($object[$this->field]))
			{
				return $object[$this->field];
			}
			else
			{
				return null;
			}
		}
		else if (is_object($object)) {
			$funcPrefix = array('get', 'is', 'has');
			foreach ($funcPrefix as $prefix)
			{
				$methodName = $prefix . $this->field;
				if (method_exists($object, $methodName)) {
					return $object->$methodName();
				}
			}
			
			return null;
		}
		
		return null;
	}
	
	/**
	 * @return the $formatter
	 */
	public function getFormatter() {
		return $this->formatter;
	}

	/**
	 * @return the $field
	 */
	public function getField() {
		return $this->field;
	}

	/**
	 * @return the $label
	 */
	public function getLabel() {
		return $this->label;
	}

	/**
	 * @param field_type $formatter
	 */
	public function setFormatter($formatter) {
		$this->formatter = $formatter;
	}

	/**
	 * @param field_type $field
	 */
	public function setField($field) {
		$this->field = $field;
	}

	/**
	 * @param field_type $label
	 */
	public function setLabel($label) {
		$this->label = $label;
	}

	public function toArray() {
		$retVal = (array) $this;
		unset($retVal['formatter']);
		return $retVal;
	}
}
