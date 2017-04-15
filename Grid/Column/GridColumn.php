<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\AbstractGridSource;

class GridColumn extends AbstractGridColumn
{
    protected $formatter;
    protected $field;
    protected $label;

    public function __construct($field, $label = null, $formatter = null)
    {
        $this->field = $field;

        if (!$label) {
            $label = ucwords($field);
        }

        $this->label = $label;
        $this->formatter = $formatter;
    }

    public function format($object, AbstractGridSource $gridsource)
    {
        if ($this->formatter) {
            return call_user_func($this->formatter, $object, $this);
        } else {
            return $this->_format($object);
        }
    }

    protected function _format($object)
    {
        if (is_array($object)) {
            if (isset($object[$this->field])) {
                return $object[$this->field];
            } else {
                return null;
            }
        } elseif (is_object($object)) {
            $funcPrefix = array(
                    'get',
                    'is',
                    'has',
            );
            foreach ($funcPrefix as $prefix) {
                $methodName = $prefix.$this->field;
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
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param field_type $formatter
     */
    public function setFormatter($formatter)
    {
        $this->formatter = $formatter;
    }

    public function toArray()
    {
        $retVal = (array) $this;
        unset($retVal['formatter']);

        return $retVal;
    }
}
