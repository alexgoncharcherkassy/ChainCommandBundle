<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Reference;

class ChainManager implements ChainManagerInterface
{
    /**
     * @var array<ChainCommand> $commands
     */
    private array $commands = [];

    /**
     * @var ChainCommand|null
     */
    private ?ChainCommand $master = null;

    /**
     * @var ChainValidatorInterface
     */
    private ChainValidatorInterface $validator;

    /**
     * ChainManager constructor.
     * @param ChainValidatorInterface $chainValidator
     */
    public function __construct(ChainValidatorInterface $chainValidator)
    {
        $this->validator = $chainValidator;
    }

    /**
     * Add commands from bundle compiler
     *
     * @param array $commands
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
     * Return master command
     *
     * @return ChainCommand
     */
    public function getMasterCommand(): ChainCommand
    {
        return $this->master;
    }

    /**
     * Return member commands
     *
     * @return iterable
     */
    public function getMembers(): iterable
    {
        /** @var ChainCommand $member */
        $member = $this->master->getMember();

        while ($member !== null) {
            $member = $this->findCommand($member->getMember());
            yield $member;
        }
    }

    /**
     * @param Command $command
     * @param bool $master
     * @param string|null $parent
     * @throws \Exception
     */
    private function createCommand(Command $command, bool $master, ?string $parent): void
    {
        $chainCommand = new ChainCommand($command, $master, $parent);

        if ($this->findCommand($chainCommand)) {
            throw new \Exception(
                sprintf(
                    "Command with name: '%s' is already present on chain",
                    $chainCommand->getCommand()->getName()
                )
            );
        }

        if ($master) {
            $this->master = $chainCommand;
        }

        $this->commands[] = $chainCommand;
    }

    /**
     * Check is master command by name
     *
     * @param string $name
     * @return bool
     */
    public function isMasterCommand(string $name): bool
    {
        if (!$this->master) {
            return false;
        }
        return $this->master->getCommand()->getName() === $name;
    }

    /**
     * Check is member command by name
     *
     * @param string $name
     * @return bool
     */
    public function isMemberCommand(string $name): bool
    {
        $command = $this->findCommandByName($name);

        return $command ? !$command->isMaster() : false;
    }

    /**
     * Assign member commands to their parent commands
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
                throw new \Exception(
                    sprintf(
                        "Command with name: '%s' not found in chain",
                        $command->getParentCommandName()
                    )
                );
            }

            if ($parentCommand->getMember()) {
                throw new \Exception(
                    sprintf(
                        "Parent command with name: '%s' already have member command with name: '%s'",
                        $parentCommand->getCommand()->getName(),
                        $parentCommand->getMember()->getCommand()->getName()
                    )
                );
            }

            $parentCommand->setMember($command);
        }
    }

    /**
     * Find command by ChainCommand model
     *
     * @param ChainCommand $chainCommand
     * @return ChainCommand|null
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
     * Find command by name
     *
     * @param string|null $name
     * @return ChainCommand|null
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
