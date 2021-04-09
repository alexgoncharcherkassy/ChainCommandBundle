<?php

namespace AlexGoncharCK\ChainCommandBundle\Event;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use Symfony\Component\Console\Output\BufferedOutput;

class CommandEvent
{
    /**
     * @var ChainCommand
     */
    private ChainCommand $command;

    /**
     * @var BufferedOutput
     */
    private BufferedOutput $output;

    /**
     * CommandEvent constructor.
     * @param ChainCommand $command
     * @param BufferedOutput $output
     */
    public function __construct(ChainCommand $command, BufferedOutput $output)
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
     * @return BufferedOutput
     */
    public function getOutput(): BufferedOutput
    {
        return $this->output;
    }
}
