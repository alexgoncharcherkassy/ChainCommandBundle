<?php

namespace AlexGoncharCK\ChainCommandBundle\Fixtures\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class HelloCommand extends Command
{
    public function configure()
    {
        $this->setName('bar:hello');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Hello from Bar!');

        return 0;
    }
}
