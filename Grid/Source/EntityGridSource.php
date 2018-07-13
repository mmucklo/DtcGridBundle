<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Dtc\GridBundle\Grid\Column\GridColumn;

class EntityGridSource extends AbstractGridSource
{
    use ColumnExtractionTrait;

    protected $entityManager;
    protected $entityName;

    public function __construct(EntityManager $entityManager, $entityName)
    {
        $this->entityManager = $entityManager;
        $this->entityName = $entityName;
    }

    protected function getQueryBuilder()
    {
        $columns = $this->getColumns();
        $fieldList = [];
        foreach ($columns as $column) {
            if ($column instanceof GridColumn && $column->isSearchable()) {
                $fieldList[$column->getField()] = true;
            }
        }

        $qb = $this->entityManager->createQueryBuilder();
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
            /** @var ClassMetadata $classMetaData */
            $classMetaData = $this->getClassMetadata();
            $classFields = $classMetaData->fieldMappings;

            $validFilters = array_intersect_key($this->filter, $classFields);

            $query = array();
            foreach ($validFilters as $key => $value) {
                if (isset($fieldList[$key])) {
                    if (is_array($value)) {
                        $query[] = "u.{$key} IN :{$key}";
                    } else {
                        $query[] = "u.{$key} = :{$key}";
                    }
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
                        if (isset($fieldList[$key])) {
                            $starQuery[] = "u.{$key} like :{$key}";
                            $qb->setParameter($key, $value);
                        }
                    }

                    if ($starQuery) {
                        $star = implode(' or ', $starQuery);

                        if ($query) {
                            $qb->andWhere($star);
                        } else {
                            $qb->add('where', $star);
                        }
                    }
                }
            }
        }

        return $qb;
    }

    /**
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->entityManager->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->entityName);

        return $classInfo;
    }

    public function getCount()
    {
        $oldOrderBy = $this->orderBy;
        $this->orderBy = [];
        $qb = $this->getQueryBuilder();
        $this->orderBy = $oldOrderBy;

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

    public function find($id)
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->entityName);
        }
        $qb = $this->entityManager->createQueryBuilder();
        $idColumn = $this->getIdColumn();
        $qb->from($this->entityName, 'a');
        $qb->select('a.'.implode(',a.', $this->getClassMetadata()->getFieldNames()));
        $qb->where('a.'.$idColumn.' = :id')->setParameter(':id', $id);
        $result = $qb->getQuery()->execute();
        if (isset($result[0])) {
            return $result[0];
        }
    }

    public function remove($id)
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->entityName);
        }

        $repository = $this->entityManager->getRepository($this->entityName);
        $entity = $repository->find($id);

        if ($entity) {
            $this->entityManager->remove($entity);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}
