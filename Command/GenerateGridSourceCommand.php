<?php
namespace Dtc\GridBundle\Command;

use Dtc\GridBundle\Generator\GridColumnGenerator;
use Dtc\GridBundle\Generator\GridConfigGenerator;

use Asc\PlatformBundle\Documents\Profile\UserProfile;
use Asc\PlatformBundle\Documents\UserAuth;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\Bundle\DoctrineBundle\Mapping\MetadataFactory;

use Sensio\Bundle\GeneratorBundle\Command\Validators;

class GenerateGridSourceCommand
    extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('dtc:grid:source:generate')
            ->setDefinition(array(
                new InputArgument('entity', InputArgument::REQUIRED, 'The entity class name to initialize (shortcut notation)'),
            	new InputArgument('class_name', InputArgument::REQUIRED, 'Name of GridSource - camel case, no space.')
            ))
        ->setDescription('Generate a class for GridSource, GridColumn and template file')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $entity = Validators::validateEntityName($input->getArgument('entity'));
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        $entityClass = $this->getContainer()->get('doctrine')->getEntityNamespace($bundle).'\\'.$entity;
        $metadata = $this->getEntityMetadata($entityClass);
        $bundle   = $this->getApplication()->getKernel()->getBundle($bundle);

        $skeletonDir = __DIR__.'/../Resources/skeleton';
        $columnGenerator = new GridColumnGenerator($skeletonDir, $this->getContainer());

        $columnGenerator->generate($bundle, $entity, $metadata[0]);
        ve('generated');

        $configGenerator = new GridConfigGenerator();
        $configGenerator->generate($bundle, $entity, $metadata[0]);


        $output->writeln(sprintf(
            'The new %s.php class file has been created under %s.',
            $generator->getClassName(),
            $generator->getClassPath()
        ));
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
        $factory = new MetadataFactory($this->getContainer()->get('doctrine'));

        return $factory->getClassMetadata($entity)->getMetadata();
    }
}
