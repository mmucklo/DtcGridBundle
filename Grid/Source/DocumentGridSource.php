<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager;
use Dtc\GridBundle\Grid\Column\GridColumn;

class DocumentGridSource extends AbstractDoctrineGridSource
{
    protected $repository;
    protected $findCache;

    public function __construct(DocumentManager $documentManager, $documentName, $idColumn)
    {
        parent::__construct($documentManager, $documentName, $idColumn);
        $this->repository = $documentManager->getRepository($documentName);
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     *
     * @throws \Exception
     */
    protected function getQueryBuilder()
    {
        if (!$this->objectManager instanceof DocumentManager) {
            throw new \Exception("Should be DocumentManager, instead it's ".get_class($this->objectManager));
        }
        $qb = $this->objectManager->createQueryBuilder($this->objectName);

        /** @var ClassMetadata $classMetaData */
        $classMetaData = $this->getClassMetadata();
        $classFields = $classMetaData->fieldMappings;

        $columns = $this->getColumns();
        $fieldList = [];
        foreach ($columns as $column) {
            if ($column instanceof GridColumn && $column->isSearchable()) {
                $fieldList[$column->getField()] = true;
            }
        }
        if ($this->filter) {
            $validFilters = array_intersect_key($this->filter, $classFields);

            $query = array();
            foreach ($validFilters as $key => $value) {
                if (isset($fieldList[$key])) {
                    if (is_array($value)) {
                        $qb->field($key)->in($value);
                    } else {
                        $qb->field($key)->equals($value);
                    }
                }
            }
            if (!$query) {
                $starFilter = array_intersect_key($this->filter, ['*' => null]);
                if ($starFilter) {
                    $value = current($starFilter);
                    foreach ($classFields as $key => $info) {
                        if (isset($fieldList[$key])) {
                            $expr = $qb->expr()->field($key);
                            switch ($info['type']) {
                                case 'integer':
                                    $expr = $expr->equals(intval($value));
                                    break;
                                default:
                                    $expr = $expr->equals($value);
                            }
                            $qb->addOr($expr);
                        }
                        // @TODO - maybe allow pattern searches some day: new \MongoRegex('/.*'.$value.'.*/')
                    }
                }
            }
        }
        if ($this->orderBy) {
            foreach ($this->orderBy as $key => $direction) {
                $qb->sort($key, $direction);
            }
        }

        $qb->limit($this->limit);
        $qb->skip($this->offset);

        return $qb;
    }

    /**
     * @return mixed
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
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function getCount()
    {
        $result = $this->getQueryBuilder()->limit(0)->skip(0)->count()->getQuery()->execute();

        return $result;
    }

    /**
     * @return mixed
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @throws \Exception
     */
    public function getRecords()
    {
        return $this->getQueryBuilder()->getQuery()->execute()->toArray(false);
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
        if (!$this->objectManager instanceof DocumentManager) {
            throw new \Exception('should be DocumentManager, instead'.get_class($this->objectManager));
        }
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->objectName);
        }
        $qb = $this->objectManager->createQueryBuilder($this->objectName);
        $qb->field($this->idColumn)->equals($id);
        $result = $qb->getQuery()->execute()->toArray(false);
        if (isset($result[0])) {
            return $result[0];
        }
    }

    /**
     * @param $id
     * @param bool   $soft
     * @param string $softColumn
     * @param string $softColumnType
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function remove($id, $soft = false, $softColumn = 'deletedAt', $softColumnType = 'datetime')
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->objectName);
        }
        $repository = $this->objectManager->getRepository($this->objectName);
        $document = $repository->find($id);
        if ($document) {
            $this->objectManager->remove($document);
            $this->objectManager->flush();

            return true;
        }

        return false;
    }
}
