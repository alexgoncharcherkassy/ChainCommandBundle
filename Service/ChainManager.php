<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use Symfony\Component\Console\Command\Command;

class ChainManager implements ChainManagerInterface
{
    /**
     * @var array<ChainCommand>
     */
    private array $commands = [];

    private ?ChainCommand $master = null;

    private ChainValidatorInterface $validator;

    /**
     * ChainManager constructor.
     */
    public function __construct(ChainValidatorInterface $chainValidator)
    {
        $this->validator = $chainValidator;
    }

    /**
     * Add commands from bundle compiler.
     *
     * @throws \Exception
     */
    public function addCommands(array $commands): void
    {
        foreach ($commands as $command) {
            $this->createCommand(
               $command['command'],
               $command['master'],
               $command['parent']
           );
        }

        $this->assignCommandsToParent();

        $this->validator->validate($this->commands);
    }

    /**
     * Return master command.
     */
    public function getMasterCommand(): ChainCommand
    {
        return $this->master;
    }

    /**
     * Return member commands.
     */
    public function getMembers(): iterable
    {
        /** @var ChainCommand $member */
        $member = $this->master->getMember();

        while (null !== $member) {
            yield $member;
            $member = $member->getMember() ? $this->findCommand($member->getMember()) : null;
        }
    }

    /**
     * @throws \Exception
     */
    private function createCommand(Command $command, bool $master, ?string $parent): void
    {
        $chainCommand = new ChainCommand($command, $master, $parent);

        if ($this->findCommand($chainCommand)) {
            throw new \Exception(sprintf("Command with name: '%s' is already present on chain", $chainCommand->getCommand()->getName()));
        }

        if ($master) {
            $this->master = $chainCommand;
        }

        $this->commands[] = $chainCommand;
    }

    /**
     * Check is master command by name.
     */
    public function isMasterCommand(string $name): bool
    {
        if (!$this->master) {
            return false;
        }

        return $this->master->getCommand()->getName() === $name;
    }

    /**
     * Check is member command by name.
     */
    public function isMemberCommand(string $name): bool
    {
        $command = $this->findCommandByName($name);

        return $command ? !$command->isMaster() : false;
    }

    /**
     * Assign member commands to their parent commands.
     */
    private function assignCommandsToParent(): void
    {
        /** @var ChainCommand $command */
        foreach ($this->commands as $command) {
            if ($command->isMaster()) {
                continue;
            }

            $parentCommand = $this->findCommandByName($command->getParentCommandName());

            if (!$parentCommand) {
                throw new \Exception(sprintf("Command with name: '%s' not found in chain", $command->getParentCommandName()));
            }

            if ($parentCommand->getMember()) {
                throw new \Exception(sprintf("Parent command with name: '%s' already have member command with name: '%s'", $parentCommand->getCommand()->getName(), $parentCommand->getMember()->getCommand()->getName()));
            }

            $parentCommand->setMember($command);
        }
    }

    /**
     * Find command by ChainCommand model.
     */
    private function findCommand(ChainCommand $chainCommand): ?ChainCommand
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) use ($chainCommand) {
            return $command->getCommand()->getName() === $chainCommand->getCommand()->getName();
        });

        $first = reset($filtered);

        return $first ?: null;
    }

    /**
     * Find command by name.
     */
    private function findCommandByName(?string $name): ?ChainCommand
    {
        $filtered = array_filter($this->commands, function (ChainCommand $command) use ($name) {
            return $command->getCommand()->getName() === $name;
        });

        $first = reset($filtered);

        return $first ?: null;
    }
}
