<?php

namespace AlexGoncharCK\ChainCommandBundle\Event;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use Symfony\Component\Console\Output\OutputInterface;

class CommandEvent
{
    /**
     * @var ChainCommand
     */
    private ChainCommand $command;

    /**
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * CommandEvent constructor.
     * @param ChainCommand $command
     * @param OutputInterface $output
     */
    public function __construct(ChainCommand $command, OutputInterface $output)
    {
        $this->command = $command;
        $this->output = $output;
    }

    /**
     * @return ChainCommand
     */
    public function getCommand(): ChainCommand
    {
        return $this->command;
    }

    /**
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }
}
