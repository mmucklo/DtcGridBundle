<?php

namespace Dtc\GridBundle\Manager;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Dtc\GridBundle\Grid\Source\ColumnSource;
use Dtc\GridBundle\Grid\Source\DocumentGridSource;
use Dtc\GridBundle\Grid\Source\EntityGridSource;
use Dtc\GridBundle\Grid\Source\GridSourceInterface;

class GridSourceManager
{
    protected $sourcesByClass;
    protected $sourcesByName;

    protected $reader;
    protected $cacheDir;
    protected $debug;

    /** @var AbstractManagerRegistry */
    protected $registry;

    /** @var AbstractManagerRegistry */
    protected $mongodbRegistry;

    protected $customManagerMappings;

    protected $extraGridSources;

    /**
     * @var array|null Null means all entities allowed, empty array means no entities allowed
     */
    protected $reflectionAllowedEntities;

    /**
     * GridSourceManager constructor.
     *
     * @param string $cacheDir
     * @param bool   $debug
     */
    public function __construct(Reader $reader, $allowedEntities, $cacheDir, $debug = false)
    {
        $this->cacheDir = $cacheDir;
        $this->reader = $reader;
        $this->reflectionAllowedEntities = is_array($allowedEntities) ? array_flip($allowedEntities) : ('*' === $allowedEntities ? null : []);
        $this->debug = $debug;
        $this->sources = array();
    }

    public function setRegistry(AbstractManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function setMongodbRegistry(AbstractManagerRegistry $registry)
    {
        $this->mongodbRegistry = $registry;
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * @return bool
     */
    protected function getDebug()
    {
        return $this->debug;
    }

    public function add($id, GridSourceInterface $gridSource)
    {
        $this->extraGridSources[$id] = $gridSource;
    }

    /**
     * @param ObjectManager $manager
     * @param $entityOrDocument
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
            $name = $classMetadata->getName();
            $reflectionClass = $classMetadata->getReflectionClass();
            $annotation = $this->reader->getClassAnnotation($reflectionClass, 'Dtc\GridBundle\Annotation\Grid');
            if (!$annotation && !isset($this->reflectionAllowedEntities[$entityOrDocument]) && null !== $this->reflectionAllowedEntities) {
                throw new \Exception("GridSource requested for '$entityOrDocument' but no Grid annotation found");
            }
            $columnSource = new ColumnSource($manager, $name);
            $columnSource->setAnnotationReader($this->reader);
            $columnSource->setCacheDir($this->cacheDir);
            $columnSource->setDebug($this->debug);

            if ($manager instanceof EntityManagerInterface) {
                $gridSource = new EntityGridSource($manager, $name, $columnSource->getIdColumn());
            } else {
                if (!$manager instanceof DocumentManager) {
                    throw new \Exception('Unknown ObjectManager type: '.get_class($manager));
                }
                $gridSource = new DocumentGridSource($manager, $name, $columnSource->getIdColumn());
            }
            $gridSource->setColumns($columnSource->getColumns());
            $this->sourcesByName[$name] = $gridSource;
            $this->sourcesByClass[$className] = $gridSource;
            $gridSource->setId($className);

            return $gridSource;
        }

        return null;
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
