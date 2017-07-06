<?php

namespace Dtc\GridBundle\Command;

use Dtc\GridBundle\Generator\GridSourceGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Sensio\Bundle\GeneratorBundle\Command\Validators;

class GenerateGridSourceCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('dtc:grid:source:generate')
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
                new InputArgument('class_name', InputArgument::OPTIONAL, 'Name of GridSource - camel case, no space.'),
            ))
        ->setDescription('Generate a class for GridSource, GridColumn and template file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle = $this->getApplication()->getKernel()->getBundle($bundle);

        $skeletonDir = __DIR__.'/../Resources/skeleton';
        $columnGenerator = new GridSourceGenerator($skeletonDir, $this->getContainer());

        $columnGenerator->generate($bundle, $entity, $metadata);
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
}
