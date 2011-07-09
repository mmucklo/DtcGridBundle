<?php
namespace Dtc\GridBundle\Mapper;

use Exception;
use ArrayObject;

class ArrayValueObject
    extends ArrayObject
{

    public function getValueByArray(array $params)
    {
        $data = &$this;
        $total = count($params);
        $key = current($params);

        if ($total === 0)
        {
            throw new Exception('requires at least 1 arg');
        }

        if ($key === NULL)
            throw new Exception('requires non NULL args');

        if (!is_scalar($key))
            throw new Exception('requires scalar args');

        if (!isset($data[$key]))
            return NULL;

        if ($total === 1)
        {
            return $data[$key];
        }

        $data = &$data[$key];
        $args = array_slice($params, 1);

        $count = 0;
        foreach ( $args as $key )
        {
            if ($count++ > 100)
                exit();

            if (is_array($data))
            {
                if (!isset($data[$key]))
                {
                    return NULL;
                }
                else
                {
                    $data = &$data[$key];
                }
            }
            else
            {
                return NULL;
            }
        }

        return $data;
    }

    public function getValue()
    {
        return $this->getValueByArray(func_get_args());
    }
}
