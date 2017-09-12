<?php

namespace Dtc\GridBundle\Manager;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use Dtc\GridBundle\Grid\Source\DocumentGridSource;
use Dtc\GridBundle\Grid\Source\EntityGridSource;
use Dtc\GridBundle\Grid\Source\GridSourceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GridSourceManager
{
    protected $sourcesByClass;
    protected $sourcesByName;

    protected $reader;
    protected $debug;

    protected $customManagerMappings;

    protected $extraGridSources;

    protected $container;

    /**
     * GridSourceManager constructor.
     *
     * @param string $cacheDir
     * @param bool   $debug
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->sources = array();
    }

    /**
     * @return string
     */
    protected function getCacheDir()
    {
        return $this->container->getParameter('kernel.cache_dir');
    }

    /**
     * @return bool
     */
    protected function getDebug()
    {
        return $this->container->getParameter('kernel.debug');
    }

    public function add($id, $gridSourceId)
    {
        $this->extraGridSources[$id] = $gridSourceId;
    }

    /**
     * Populates custom (Entity/Document) managers.
     *
     * @throws \Exception
     */
    protected function populateCustomManagerMappings()
    {
        $this->customManagerMappings = [];
        $customManagers = $this->container->getParameter('dtc_grid.custom_managers');
        if ($customManagers) {
            foreach ($customManagers as $key => $managerId) {
                if (!$this->container->has($managerId)) {
                    throw new \Exception("dtc_grid: can't find custom manager service: $managerId");
                }
                $manager = $this->container->get($managerId);
                if (!$manager instanceof EntityManagerInterface && !$manager instanceof DocumentManager) {
                    throw new \Exception("dtc_grid: service specified by $managerId for ($key) should be either an EntityManagerInterface or DocumentManager");
                }

                /** @var EntityManager|DocumentManager */
                $repository = $manager->getRepository($key);
                if (!$repository) {
                    throw new \Exception("dtc_grid: can't find repository for $key in service: $managerId");
                }
                $className = $repository->getClassName();
                $name = $manager->getClassMetadata($className)->getName();
                $this->customManagerMappings[$className] = $manager;
                $this->customManagerMappings[$name] = $manager;
            }
        }
    }

    /**
     * @param $entityOrClassName
     *
     * @return EntityManager|DocumentManager|null
     */
    protected function getCustomManager($entityOrClassName)
    {
        if ($this->customManagerMappings === null) {
            $this->populateCustomManagerMappings();
        }

        if (isset($this->customManagerMappings[$entityOrClassName])) {
            return $this->customManagerMappings[$entityOrClassName];
        }

        return null;
    }

    /**
     * @param EntityManager|DocumentManager $manager
     * @param $entityOrDocument
     *
     * @return DocumentGridSource|EntityGridSource|null
     */
    protected function getGridSource($manager, $entityOrDocument)
    {
        try {
            $repository = $manager->getRepository($entityOrDocument);
            if ($repository) {
                $className = $repository->getClassName();
                $name = $manager->getClassMetadata($className)->getName();
                if ($manager instanceof EntityManagerInterface) {
                    $gridSource = new EntityGridSource($manager, $name);
                } else {
                    $gridSource = new DocumentGridSource($manager, $name);
                }
                $gridSource->setAnnotationReader($this->container->get('annotation_reader'));
                $gridSource->setCacheDir($this->container->getParameter('kernel.cache_dir'));

                $gridSource->setDebug($this->debug);
                $gridSource->autoDiscoverColumns();
                $this->sourcesByName[$name] = $gridSource;
                $this->sourcesByClass[$className] = $gridSource;
                $gridSource->setId($className);

                return $gridSource;
            }
        } catch (\Exception $exception) {
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
            if ($this->extraGridSources[$entityOrDocumentNameOrId] instanceof GridSourceInterface) {
                return $this->extraGridSources[$entityOrDocumentNameOrId];
            }
            $result = $this->container->get($this->extraGridSources[$entityOrDocumentNameOrId]);
            if (!$result instanceof GridSourceInterface) {
                throw new \Exception("GridSource referenced by $entityOrDocumentNameOrId should be instance of GridSourceInterface");
            }

            return $result;
        }

        if (isset($this->sourcesByClass[$entityOrDocumentNameOrId])) {
            return $this->sourcesByClass[$entityOrDocumentNameOrId];
        }

        if (isset($this->sourcesByName[$entityOrDocumentNameOrId])) {
            return $this->sourcesByName[$entityOrDocumentNameOrId];
        }

        if ($manager = $this->getCustomManager($entityOrDocumentNameOrId)) {
            return $this->getGridSource($manager, $entityOrDocumentNameOrId);
        }

        if ($this->container->has('doctrine.orm.default_entity_manager') &&
            $gridSource = $this->getGridSource($this->container->get('doctrine.orm.default_entity_manager'), $entityOrDocumentNameOrId)) {
            return $gridSource;
        }

        if ($this->container->has('doctrine_mongodb.odm.default_document_manager') &&
            $gridSource = $this->getGridSource($this->container->get('doctrine_mongodb.odm.default_document_manager'), $entityOrDocumentNameOrId)) {
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
