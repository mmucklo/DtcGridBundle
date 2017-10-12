<?php

namespace Dtc\GridBundle\Manager;

use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
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
     * GridSourceManager constructor.
     *
     * @param string $cacheDir
     * @param bool   $debug
     */
    public function __construct(Reader $reader, $cacheDir, $debug = false)
    {
        $this->cacheDir = $cacheDir;
        $this->reader = $reader;
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
            if (!$annotation) {
                throw new \Exception("GridSource requested for '$entityOrDocument' but no Grid annotation found");
            }
            if ($manager instanceof EntityManagerInterface) {
                $gridSource = new EntityGridSource($manager, $name);
            } else {
                $gridSource = new DocumentGridSource($manager, $name);
            }
            $gridSource->setAnnotationReader($this->reader);
            $gridSource->setCacheDir($this->cacheDir);

            $gridSource->setDebug($this->debug);
            $gridSource->autoDiscoverColumns();
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
