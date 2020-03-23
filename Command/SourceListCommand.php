<?php

namespace Dtc\GridBundle\Command;

use Dtc\GridBundle\Manager\GridSourceManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @deprecated
 *
 * Class SourceListCommand
 */
class SourceListCommand extends Command
{
    private $gridSourceManager;

    protected function configure()
    {
        $this
        ->setName('dtc:grid:source:list')
        ->setDescription('List avaliable Grid Sources')
        ;
    }

    public function setGridSourceManager(GridSourceManager $gridSourceManager) {
        $this->gridSourceManager = $gridSourceManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gridSources = $this->gridSourceManager->all();

        $output->writeln('Avaliable Grid Sources: ');
        foreach ($gridSources as $id => $source) {
            $output->writeln("{$id} => ".get_class($source));
        }
    }
}
