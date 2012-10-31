<?php
namespace Dtc\GridBundle\Grid\Column;

/**
 * Standard options
 */
abstract class AbstractGridColumn
{
    protected $field;
    protected $label;
    protected $options = array();

    abstract function format($object);

    public function setOption($key, $value) {
        $this->options[$key] = $value;
    }

    public function getOption($key) {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return null;
    }

    public function setOptions(array $options) {
        $this->options = $options;
    }

    public function getOptions() {
        return $this->options;
    }

    /**
     * @return the $field
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return the $label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param field_type $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param field_type $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}