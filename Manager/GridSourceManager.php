<?php

namespace Dtc\GridBundle\Manager;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Grid\Source\ColumnSourceInfo;
use Dtc\GridBundle\Grid\Source\DocumentGridSource;
use Dtc\GridBundle\Grid\Source\EntityGridSource;
use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridSourceManager
{
    protected $sourcesByClass;
    protected $sourcesByName;

    protected $reader;

    /** @var AbstractManagerRegistry */
    protected $registry;

    /** @var AbstractManagerRegistry */
    protected $mongodbRegistry;

    protected $customManagerMappings;

    protected $extraGridSources;

    protected $columnSource;
    /**
     * @var array|null Null means all entities allowed, empty array means no entities allowed
     */
    protected $reflectionAllowedEntities;

    /**
     * GridSourceManager constructor.
     */
    public function __construct(ColumnSource $columnSource)
    {
        $this->columnSource = $columnSource;
        $this->reflectionAllowedEntities = [];
        $this->sources = [];
    }

    public function setReader(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param array|string $allowedEntities Array of allowed entities or string '*' to allow all entities. Use empty array to specify no entities allowed for reflection.
     */
    public function setReflectionAllowedEntities($allowedEntities)
    {
        $this->reflectionAllowedEntities = is_array($allowedEntities) ? array_flip($allowedEntities) : ('*' === $allowedEntities ? null : []);
    }

    public function setRegistry(AbstractManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function setMongodbRegistry(AbstractManagerRegistry $registry)
    {
        $this->mongodbRegistry = $registry;
    }

    public function add($id, GridSourceInterface $gridSource)
    {
        $this->extraGridSources[$id] = $gridSource;
    }

    /**
     * @param ObjectManager $manager
     * @param $entityOrDocument
     *z
     *
     * @return DocumentGridSource|EntityGridSource|null
     *
     * @throws \Exception
     */
    protected function getGridSource($manager, $entityOrDocument)
    {
        $repository = $manager->getRepository($entityOrDocument);
        if ($repository) {
            $className = $repository->getClassName();
            $classMetadata = $manager->getClassMetadata($className);
            $params = [$manager, $entityOrDocument, null === $this->reflectionAllowedEntities || isset($this->reflectionAllowedEntities[$entityOrDocument])];
            if ($this->reader) {
                $params[] = $this->reader;
            }
            $columnSourceInfo = call_user_func_array([$this->columnSource, 'getColumnSourceInfo'], $params);
            $name = $classMetadata->getName();
            if ($columnSourceInfo) {
                return $this->getGridSourceFromColumnSourceInfo($manager, $className, $name, $columnSourceInfo);
            }
        }

        return null;
    }

    /**
     * @param $manager
     * @param $className
     * @param $name
     *
     * @return DocumentGridSource|EntityGridSource
     *
     * @throws \Exception
     */
    private function getGridSourceFromColumnSourceInfo($manager, $className, $name, ColumnSourceInfo $columnSourceInfo)
    {
        if ($manager instanceof EntityManagerInterface) {
            $gridSource = new EntityGridSource($manager, $name);
        } else {
            if (!$manager instanceof DocumentManager) {
                throw new \Exception('Unknown ObjectManager type: '.get_class($manager));
            }
            $gridSource = new DocumentGridSource($manager, $name);
        }
        $gridSource->setIdColumn($columnSourceInfo->idColumn);
        $gridSource->setColumns($columnSourceInfo->columns);
        $this->sourcesByName[$name] = $gridSource;
        $this->sourcesByClass[$className] = $gridSource;
        $gridSource->setId($className);
        $gridSource->setDefaultSort($columnSourceInfo->sort);

        return $gridSource;
    }

    /**
     * Get a gridsource.
     *
     * @param string                             $id      Entity or Document
     * @param EntityManager|DocumentManager|null $manager (optional) Entity or Document manager to use (overrides default)
     *
     * @return GridSourceInterface|null
     *
     * @throws \Exception
     */
    public function get($entityOrDocumentNameOrId)
    {
        // @Support legacy method of adding/removing grid sources
        if (isset($this->extraGridSources[$entityOrDocumentNameOrId])) {
            return $this->extraGridSources[$entityOrDocumentNameOrId];
        }

        if (isset($this->sourcesByClass[$entityOrDocumentNameOrId])) {
            return $this->sourcesByClass[$entityOrDocumentNameOrId];
        }

        if (isset($this->sourcesByName[$entityOrDocumentNameOrId])) {
            return $this->sourcesByName[$entityOrDocumentNameOrId];
        }

        try {
            if ($this->registry && ($manager = $this->registry->getManagerForClass($entityOrDocumentNameOrId)) &&
                $gridSource = $this->getGridSource($manager, $entityOrDocumentNameOrId)) {
                return $gridSource;
            }
        } catch (\ReflectionException $exception) {
        }

        if ($this->mongodbRegistry && ($manager = $this->mongodbRegistry->getManagerForClass($entityOrDocumentNameOrId)) &&
            $gridSource = $this->getGridSource($manager, $entityOrDocumentNameOrId)) {
            return $gridSource;
        }
        throw new \Exception("Can't find grid source for $entityOrDocumentNameOrId");
    }

    public function all()
    {
        return isset($this->sourcesByName) ? array_values($this->sourcesByName) : [];
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }
}
