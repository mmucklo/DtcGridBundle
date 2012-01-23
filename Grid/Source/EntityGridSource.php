<?php
namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ORM\EntityManager;
use Dtc\GridBundle\Grid\Column\GridColumn;

class EntityGridSource
    extends AbstractGridSource
{
    protected $em;
    protected $entityName;

    public function __construct(EntityManager $em, $entityName, $serviceId)
    {
        $this->em = $em;
        $this->entityName = $entityName;
        $this->id = $serviceId;
    }

    public function autoDiscoverColumns() {
        $this->setColumns($this->getReflectionColumns());
    }

    protected function getQuery()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->add('select', 'u')
            ->add('from', "{$this->entityName} u")
            //->add('orderBy', 'u.name ASC')
            ->setFirstResult( $this->offset )
            ->setMaxResults( $this->limit );

        return $qb->getQuery();
    }

    /**
     *
     * @return ClassMetadata
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->em->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->entityName);

        return $classInfo;
    }

    /**
     * Generate Columns based on document's Metadata
     */
    public function getReflectionColumns()
    {
        $metaClass = $this->getClassMetadata();

        $columns = array();
        foreach ( $metaClass->fieldMappings as $fieldInfo )
        {
            $field = $fieldInfo['fieldName'];
            if (isset($fieldInfo['options']) && isset($fieldInfo['options']['label']))
            {
                $label = $fieldInfo['options']['label'];
            }
            else
            {
                $label = $this->fromCamelCase($field);
            }

            $columns[$field] = new GridColumn($field, $label);
        }

        return $columns;
    }

    protected function fromCamelCase($str)
    {
        $func = function ($str)
        {
            return ' ' . $str[0];
        };

        $value = preg_replace_callback('/([A-Z])/', $func, $str);
        $value = ucfirst($value);

        return $value;
    }

    public function getCount()
    {
        $qb = $this->em->createQueryBuilder();
        $qb->add('select', $qb->expr()->count('u.id'))
            ->add('from', "{$this->entityName} u");

        return $qb->getQuery()
            ->getSingleScalarResult();
    }

    public function getRecords()
    {
        return $this->getQuery()
            ->getResult();
    }
}
