<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager;
use Dtc\GridBundle\Grid\Column\GridColumn;

class DocumentGridSource extends AbstractGridSource
{
    use ColumnExtractionTrait;

    protected $documentManager;
    protected $repository;
    protected $findCache;
    protected $documentName;

    public function __construct(DocumentManager $documentManager, $documentName)
    {
        $this->documentManager = $documentManager;
        $this->repository = $documentManager->getRepository($documentName);
        $this->documentName = $documentName;
    }

    /**
     * @return \Doctrine\ODM\MongoDB\Query\Builder
     */
    protected function getQueryBuilder()
    {
        $qb = $this->documentManager->createQueryBuilder($this->documentName);

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
     */
    public function getClassMetadata()
    {
        $metaFactory = $this->documentManager->getMetadataFactory();
        $classInfo = $metaFactory->getMetadataFor($this->documentName);

        return $classInfo;
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

    public function find($id)
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->documentName);
        }
        $qb = $this->documentManager->createQueryBuilder($this->documentName);
        $idColumn = $this->getIdColumn();
        $qb->field($idColumn)->equals($id);
        $result = $qb->getQuery()->execute()->toArray(false);
        if (isset($result[0])) {
            return $result[0];
        }
    }

    public function remove($id, $soft = false, $softColumn = 'deletedAt', $softColumnType = 'datetime')
    {
        if (!$this->hasIdColumn()) {
            throw new \Exception('No id column found for '.$this->documentName);
        }
        $repository = $this->documentManager->getRepository($this->documentName);
        $document = $repository->find($id);
        if ($document) {
            $this->documentManager->remove($document);
            $this->documentManager->flush();

            return true;
        }

        return false;
    }
}
