<?php

namespace Dtc\GridBundle\Command;

use Dtc\GridBundle\Generator\GridSourceGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 *
 * Class GenerateGridSourceCommand
 */
class GenerateGridSourceCommand extends ContainerAwareCommand
{
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Taken from SensioGeneratorBundle: class Command\Validators (see LICENSE)
        if (!preg_match('{^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*:[a-zA-Z0-9_\x7f-\xff\\\/]+$}', $entity = $input->getArgument('entity_or_document'))) {
            throw new \InvalidArgumentException(sprintf('The entity name isn\'t valid ("%s" given, expecting something like AcmeBlogBundle:Blog/Post)', $entity));
        }

        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $documentClass = $this->getContainer()->get('doctrine_mongodb')->getAliasNamespace($bundle).'\\'.$entity;

        try {
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $exception) {
            if (preg_match('/does not exist/', $exception->getMessage())) {
                $metadata = $this->getDocumentMetadata($documentClass);
            } else {
                throw $exception;
            }
        }

        $application = $this->getApplication();
        if ($application instanceof Application) {
            $bundle = $application->getKernel()->getBundle($bundle);
        } else {
            throw new \Exception("Can't lookup bundle for $bundle");
        }
        $skeletonDir = __DIR__.'/../Resources/skeleton';
        $columnGenerator = new GridSourceGenerator($skeletonDir, $this->getContainer());

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

    protected function getEntityMetadata($entity)
    {
        return $this->getContainer()->get('doctrine.orm.default_entity_manager')->getClassMetadata($entity);
    }

    protected function getDocumentMetadata($document)
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.default_document_manager')->getClassMetadata($document);
    }
}
