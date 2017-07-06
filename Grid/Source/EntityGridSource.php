<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ORM\EntityManager;
use Dtc\GridBundle\Grid\Column\GridColumn;

class EntityGridSource extends AbstractGridSource
{
    protected $em;
    protected $entityName;

    public function __construct(EntityManager $em, $entityName)
    {
        $this->em = $em;
        $this->entityName = $entityName;
    }

    public function autoDiscoverColumns()
    {
        $this->setColumns($this->getReflectionColumns());
    }

    protected function getQueryBuilder()
    {
        $qb = $this->em->createQueryBuilder();
        $orderBy = array();
        foreach ($this->orderBy as $key => $value) {
            $orderBy[] = "u.{$key} {$value}";
        }

        $qb->add('select', 'u')
            ->add('from', "{$this->entityName} u")
            ->setFirstResult($this->offset)
            ->setMaxResults($this->limit);

        if ($this->orderBy) {
            $orderByStr = implode(',', $orderBy);
            $qb->add('orderBy', $orderByStr);
        }

        if ($this->filter) {
            $classMetaData = $this->getClassMetadata();
            $classFields = $classMetaData->fieldMappings;

            $validFilters = array_intersect_key($this->filter, $classFields);

            $query = array();
            foreach ($validFilters as $key => $value) {
                if (is_array($value)) {
                    $query[] = "u.{$key} IN :{$key}";
                } else {
                    $query[] = "u.{$key} = :{$key}";
                }

                $qb->setParameter($key, $value);
            }
            if ($query) {
                $qb->add('where', implode(' and ', $query));
            } else {
                $starFilter = array_intersect_key($this->filter, ['*' => null]);
                if ($starFilter) {
                    $value = current($starFilter);
                    $starQuery = [];
                    foreach (array_keys($classFields) as $key) {
                        $starQuery[] = "u.{$key} like :{$key}";
                        $qb->setParameter($key, $value);
                    }

                    $star = implode(' or ', $starQuery);
                    if ($query) {
                        $qb->andWhere($star);
                    } else {
                        $qb->add('where', $star);
                    }
                }
            }
        }

        return $qb;
    }

    /**
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->em->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->entityName);

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
        $qb = $this->getQueryBuilder();
        $qb->add('select', 'count(u)')
            ->setFirstResult(null)
            ->setMaxResults(null);

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function getRecords()
    {
        return $this->getQueryBuilder()->getQuery()
            ->getResult();
    }
}
