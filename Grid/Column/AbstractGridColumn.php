<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

/**
 * Standard options.
 */
abstract class AbstractGridColumn
{
    protected $field;
    protected $label;
    protected $options = array();
    protected $order;

    abstract public function format($object, GridSourceInterface $gridsource);

    public function setOption($key, $value)
    {
        $this->options[$key] = $value;
    }

    public function getOption($key)
    {
        if (isset($this->options[$key])) {
            return $this->options[$key];
        }

        return null;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }
}
