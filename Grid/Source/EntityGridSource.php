<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Dtc\GridBundle\Grid\Column\GridColumn;

class EntityGridSource extends AbstractGridSource
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     *
     * @throws \Exception
     */
    protected function getQueryBuilder()
    {
        if (!$this->objectManager instanceof EntityManager) {
            throw new \Exception("Expected EntityManager, instead it's: ", get_class($this->objectManager));
        }

        $columns = $this->getColumns();
        $fieldList = [];
        foreach ($columns as $column) {
            if ($column instanceof GridColumn && $column->isSearchable()) {
                $fieldList[$column->getField()] = true;
            }
        }

        $qb = $this->objectManager->createQueryBuilder();
        $orderBy = array();
        foreach ($this->orderBy as $key => $value) {
            $orderBy[] = "u.{$key} {$value}";
        }

        $qb->add('select', 'u')
            ->add('from', "{$this->objectName} u")
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
     *
     * @throws \Exception
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->objectManager->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->objectName);

        return $classInfo;
    }

    /**
     * @return mixed
     *
     * @throws \Exception
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
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

    /**
     * @return array
     *
     * @throws \Exception
     */
    public function getRecords()
    {
        return $this->getQueryBuilder()->getQuery()
            ->getResult();
    }

    /**
     * @param $id
     *
     * @return mixed|null
     *
     * @throws \Exception
     */
    public function find($id)
    {
        if (!$this->objectManager instanceof EntityManager) {
            throw new \Exception("Expected EntityManager, instead it's: ", get_class($this->objectManager));
        }

        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->objectName);
        }
        $qb = $this->objectManager->createQueryBuilder();
        $qb->from($this->objectName, 'a');
        $qb->select('a.'.implode(',a.', $this->getClassMetadata()->getFieldNames()));
        $qb->where('a.'.$this->idColumn.' = :id')->setParameter(':id', $id);
        $result = $qb->getQuery()->execute();
        if (isset($result[0])) {
            return $result[0];
        }
    }

    /**
     * @param $id
     *
     * @return bool
     *
     * @throws \Exception|\Doctrine\ORM\OptimisticLockException
     */
    public function remove($id)
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->objectName);
        }

        $repository = $this->objectManager->getRepository($this->objectName);
        $entity = $repository->find($id);

        if ($entity) {
            $this->objectManager->remove($entity);
            $this->objectManager->flush();

            return true;
        }

        return false;
    }
}
