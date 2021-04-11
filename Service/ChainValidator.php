<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;

class ChainValidator implements ChainValidatorInterface
{
    private array $commands = [];

    private ChainCommand $master;

    /**
     * Validate chain.
     *
     * @throws \Exception
     */
    public function validate(array $commands): void
    {
        if (count($commands) > 0) {
            $this->commands = $commands;

            $this
                ->checkMasterCommand()
                ->checkFullChain();
        }
    }

    /**
     * Check that master command is present and single.
     *
     * @return $this
     *
     * @throws \Exception
     */
    private function checkMasterCommand(): self
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) {
            return $command->isMaster();
        });

        if (0 === count($filtered)) {
            throw new \Exception('Master command not present');
        }

        if (count($filtered) > 1) {
            throw new \Exception('Master command should be single');
        }

        $this->master = reset($filtered);

        return $this;
    }

    /**
     * Check that all commands are consecutive.
     *
     * @return $this
     *
     * @throws \Exception
     */
    private function checkFullChain(): self
    {
        // includes master command
        $numberOfCommands = 1;

        /** @var ChainCommand $member */
        $member = $this->master->getMember();

        while (null !== $member) {
            ++$numberOfCommands;
            $member = $member->getMember() ? $this->findCommand($member->getMember()) : null;
        }

        if ($numberOfCommands !== count($this->commands)) {
            throw new \Exception('Chain is broken. Some commands dont have parent command');
        }

        return $this;
    }

    /**
     * Find command by name.
     */
    private function findCommand(ChainCommand $chainCommand): ?ChainCommand
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) use ($chainCommand) {
            return $command->getCommand()->getName() === $chainCommand->getCommand()->getName();
        });

        $first = reset($filtered);

        return $first ?: null;
    }
}
