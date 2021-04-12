<?php

namespace AlexGoncharCK\ChainCommandBundle\Fixtures\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ByeCommand extends Command
{
    public function configure()
    {
        $this->setName('bar:bye');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Bye from Bar!');

        return 0;
    }
}
