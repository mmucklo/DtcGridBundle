<?php
namespace Dtc\GridBundle\Grid\Column;

abstract class AbstractGridColumn
{
    protected $field;
    protected $label;

    abstract function format($object);

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