<?php

namespace Dtc\GridBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 *
 * Class SourceListCommand
 */
class SourceListCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
        ->setName('dtc:grid:source:list')
        ->setDescription('List avaliable Grid Sources')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $gridSourceManager = $container->get('dtc_grid.manager.source');
        $gridSources = $gridSourceManager->all();

        $output->writeln('Avaliable Grid Sources: ');
        foreach ($gridSources as $id => $source) {
            $output->writeln("{$id} => ".get_class($source));
        }
    }
}
