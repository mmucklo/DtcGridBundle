<?php

namespace Dtc\GridBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Bundle\MongoDBBundle\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Dtc\GridBundle\Generator\GridSourceGenerator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 *
 * Class GenerateGridSourceCommand
 */
class GenerateGridSourceCommand extends Command
{
    protected $registry;
    protected $mongodbRegistry;
    protected $entityManager;
    protected $documentManager;

    protected function configure()
    {
        $this
        ->setName('dtc:grid:source:generate')
            ->setDefinition(array(
                new InputArgument('entity_or_document', InputArgument::REQUIRED, 'The entity or document class name to initialize (shortcut notation)'),
                new InputArgument('class_name', InputArgument::OPTIONAL, 'Name of GridSource - camel case, no space.'),
                new InputOption('columns', null, InputOption::VALUE_NONE, 'Generate column files.'),
            ))
        ->setDescription('Generate a class for GridSource, GridColumn and template file')
        ;
    }

    public function setRegistry(Registry $registry)
    {
        $this->registry = $registry;
    }

    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    public function setMongoDBRegistry(ManagerRegistry $mongoDBRegistry)
    {
        $this->mongoDBRegistry = $mongoDBRegistry;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Taken from SensioGeneratorBundle: class Command\Validators (see LICENSE)
        if (!preg_match('{^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*:[a-zA-Z0-9_\x7f-\xff\\\/]+$}', $entity = $input->getArgument('entity_or_document'))) {
            throw new \InvalidArgumentException(sprintf('The entity name isn\'t valid ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        if ($this->registry) {
            $entityClass = $this->registry->getAliasNamespace($bundle).'\\'.$entity;
        }
        if ($this->mongodbRegistry) {
            $documentClass = $this->mongodbRegistry->getAliasNamespace($bundle).'\\'.$entity;
        }

        if (isset($entityClass) && $this->entityManager) {
            try {
                $metadata = $this->entityManager->getClassMetadata($entityClass);
            } catch (\Exception $exception) {
                if (!preg_match('/does not exist/', $exception->getMessage())) {
                    throw $exception;
                }
            }
        }
        if (!isset($metadata) && $this->documentManager) {
            if (!isset($documentClass)) {
                throw new \Exception("Could not get metadata for $entity");
            }
            $metadata = $this->documentManager->getClassMetadata($documentClass);
        }

        $application = $this->getApplication();
        if ($application instanceof Application) {
            $bundle = $application->getKernel()->getBundle($bundle);
        } else {
            throw new \Exception("Can't lookup bundle for $bundle");
        }
        $skeletonDir = __DIR__.'/../Resources/skeleton';
        $columnGenerator = new GridSourceGenerator($skeletonDir);

        $columnGenerator->generate($bundle, $entity, $metadata, $input->getOption('columns'));
    }

    protected function parseShortcutNotation($shortcut)
    {
        $entity = str_replace('/', '\\', $shortcut);

        if (false === $pos = strpos($entity, ':')) {
            throw new \InvalidArgumentException(sprintf('The entity name must contain a : ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        return array(substr($entity, 0, $pos), substr($entity, $pos + 1));
    }
}
