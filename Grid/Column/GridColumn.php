<?php

namespace Dtc\GridBundle\Grid\Column;

use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridColumn extends AbstractGridColumn
{
    protected $formatter;
    protected $field;
    protected $label;
    protected $searchable;
    protected $order;

    /**
     * GridColumn constructor.
     *
     * @param $field
     * @param string|null $label
     * @param mixed       $formatter
     * @param array|null  $options
     * @param bool        $searchable
     * @param int|null    $order      If there are columns that have an order mixed with columns of 'null' order, the null ones will appear last
     */
    public function __construct(
        $field,
            $label = null,
            $formatter = null,
            array $options = null,
            $searchable = true,
            $order = null
    ) {
        $this->field = $field;

        if (!$label) {
            $label = ucwords($field);
        }

        $this->label = $label;
        $this->formatter = $formatter;
        if ($options) {
            $this->setOptions($options);
        }

        $this->searchable = $searchable;

        $this->order = $order;

        if (null !== $this->order && !is_int($this->order)) {
            throw new \InvalidArgumentException('$order must be an integer or null');
        }
    }

    public function isSearchable()
    {
        return $this->searchable;
    }

    public function format($object, GridSourceInterface $gridsource)
    {
        if ($this->formatter) {
            return call_user_func($this->formatter, $object, $this);
        } else {
            return $this->_format($object);
        }
    }

    protected function _format($object)
    {
        $value = null;
        if (is_array($object)) {
            if (isset($object[$this->field])) {
                $value = $object[$this->field];
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
                    $value = $object->$methodName();
                    break;
                }
            }
        }
        if (is_object($value)) {
            if ($value instanceof \DateTime) {
                return $value->format(\DateTime::ISO8601);
            }

            return 'object: '.get_class($value);
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_array($value)) {
            return 'array: '.print_r($value, true);
        }

        return $value;
    }

    /**
     * @return $formatter
     */
    public function getFormatter()
    {
        return $this->formatter;
    }

    /**
     * @param $formatter
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
