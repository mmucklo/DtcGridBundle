<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Dtc\GridBundle\Grid\Column\GridColumn;
use Doctrine\ODM\MongoDB\DocumentManager;

class DocumentGridSource extends AbstractGridSource
{
    protected $dm;
    protected $documentName;
    protected $repository;
    protected $findCache;

    public function __construct(DocumentManager $dm, $documentName)
    {
        $this->dm = $dm;
        $this->repository = $dm->getRepository($documentName);
        $this->documentName = $documentName;
    }

    public function autoDiscoverColumns()
    {
        $this->setColumns($this->getReflectionColumns());
    }

    protected function getQueryBuilder()
    {
        $qb = $this->dm->createQueryBuilder($this->documentName);

        /** @var ClassMetadata $classMetaData */
        $classMetaData = $this->getClassMetadata();
        $classFields = $classMetaData->fieldMappings;

        if ($this->filter) {
            $validFilters = array_intersect_key($this->filter, $classFields);

            $query = array();
            foreach ($validFilters as $key => $value) {
                if (is_array($value)) {
                    $qb->field($key)->in($value);
                } else {
                    $qb->field($key)->equals($value);
                }
            }
            if (!$query) {
                $starFilter = array_intersect_key($this->filter, ['*' => null]);
                if ($starFilter) {
                    $value = current($starFilter);
                    foreach ($classFields as $key => $info) {
                        $expr = $qb->expr()->field($key);
                        switch ($info['type']) {
                            case 'integer':
                                $expr = $expr->equals(intval($value));
                                break;
                            default:
                                $expr = $expr->equals($value);
                        }
                        $qb->addOr($expr);
                        // new \MongoRegex('/.*'.$value.'.*/') - > maybe use some day?
                    }
                }
            }
        }
        $qb->limit($this->limit);
        $qb->skip($this->offset);

        return $qb;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->dm->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->documentName);

        return $classInfo;
    }

    /**
     * Generate Columns based on document's Metadata.
     */
    public function getReflectionColumns()
    {
        $metaClass = $this->getClassMetadata();

        $columns = array();
        foreach ($metaClass->fieldMappings as $fieldInfo) {
            $field = $fieldInfo['fieldName'];
            if (isset($fieldInfo['options']) && isset($fieldInfo['options']['label'])) {
                $label = $fieldInfo['options']['label'];
            } else {
                $label = $this->fromCamelCase($field);
            }

            $columns[$field] = new GridColumn($field, $label);
        }

        return $columns;
    }

    protected function fromCamelCase($str)
    {
        $func = function ($str) {
            return ' '.$str[0];
        };

        $value = preg_replace_callback('/([A-Z])/', $func, $str);
        $value = ucfirst($value);

        return $value;
    }

    public function getCount()
    {
        $result = $this->getQueryBuilder()->limit(0)->skip(0)->count()->getQuery()->execute();

        return $result;
    }

    public function getRecords()
    {
        return $this->getQueryBuilder()->getQuery()->execute()->toArray(false);
    }
}
