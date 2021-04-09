<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use http\Exception\InvalidArgumentException;

class ChainValidator implements ChainValidatorInterface
{
    private array $commands = [];
    private ChainCommand $master;

    public function validate(array $commands, ChainCommand $masterCommand): void
    {
        $this->commands = $commands;
        $this->master = $masterCommand;

        $this
            ->checkMasterCommand()
            ->checkFullChain();
    }

    private function checkMasterCommand(): self
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) {
            return $command->isMaster();
        });

        if (count($filtered) === 0) {
            throw new InvalidArgumentException("Master command not present");
        }

        if (count($filtered) > 1) {
            throw new InvalidArgumentException("Master command should be single");
        }

        return $this;
    }

    private function checkFullChain(): self
    {
        // includes master command
        $numberOfCommands = 1;

        /** @var ChainCommand $command */
        $command = $this->master->getMember();

        while ($command === null) {
            ++$numberOfCommands;
            $command = $this->findCommand($command->getMember());
        }

        if ($numberOfCommands !== count($this->commands)) {
            throw new InvalidArgumentException("Chain is broken. Some commands");
        }

        return $this;
    }

    private function findCommand(ChainCommand $chainCommand): ?ChainCommand
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) use ($chainCommand) {
            return $command->getCommand()->getName() === $chainCommand->getCommand()->getName();
        });

        $first = reset($filtered);

        return $first ?? null;
    }
}
