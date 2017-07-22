<?php

namespace Dtc\GridBundle\Grid\Source;

use Doctrine\ODM\MongoDB\Mapping\ClassMetadata;
use Doctrine\ODM\MongoDB\DocumentManager;

class DocumentGridSource extends AbstractGridSource
{
    use MetadataTrait;

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
        $metaFactory = $this->dm->getMetadataFactory();
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
}
