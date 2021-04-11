<?php

namespace AlexGoncharCK\ChainCommandBundle\Event;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use Symfony\Component\Console\Output\OutputInterface;

class CommandEvent
{
    private ChainCommand $command;

    private OutputInterface $output;

    /**
     * CommandEvent constructor.
     */
    public function __construct(ChainCommand $command, OutputInterface $output)
    {
        $this->command = $command;
        $this->output = $output;
    }

    public function getCommand(): ChainCommand
    {
        return $this->command;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
