<?php

namespace Dtc\GridBundle\Mapper;

use Dtc\MarketBundle\Mapper\ArrayValueObject;

class ArrayToObjectMapper
{
    private $classname;
    private $dataPath;
    private $fields;

    public function __construct(array $mapperSettings)
    {
        $this->classname = $mapperSettings['class_name'];
        $this->dataPath = $mapperSettings['data_path'];
        $this->fields = $mapperSettings['fields'];
    }

    public function getObjects(ArrayValueObject $globalArray)
    {
        $data = $globalArray->getValueByArray($this->dataPath);

        $retVal = array();
        if ($data) {
            foreach ($data as $field => $objectData) {
                $object = new $this->classname();
                $arrayValueObject = new ArrayValueObject($objectData);

                $this->setFields($object, $globalArray, $arrayValueObject);
                $retVal[] = $object;
            }
        }

        return $retVal;
    }

    protected function setFields($object, ArrayValueObject $globalArray, ArrayValueObject $dataArray)
    {
        foreach ($this->fields as $field => $fieldSetting) {
            $methodName = "set{$field}";

            if (isset($fieldSetting['value'])) {
                $value = $fieldSetting['value'];
            } elseif (isset($fieldSetting['data_path'])) {
                $value = $dataArray->getValueByArray($fieldSetting['data_path']);
            } elseif (isset($fieldSetting['path'])) {
                $value = $globalArray->getValueByArray($fieldSetting['path']);
            }

            $field = strtolower($field);
            if (endsWith($field, 'time') || endsWith($field, 'date') || endsWith($field, 'at')) {
                $value = substr($value, 0, 14);
                $value = \DateTime::createFromFormat('YmdHis', $value);
            }

            if (method_exists($object, $methodName)) {
                $object->$methodName($value);
            } else {
                throw new \Exception("Unable to map {$field}, {$this->classname} does not have setter function: {$methodName}");
            }
        }
    }
}
