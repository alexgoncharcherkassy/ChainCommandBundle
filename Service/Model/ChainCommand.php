<?php

namespace AlexGoncharCK\ChainCommandBundle\Service\Model;

use Symfony\Component\Console\Command\Command;

class ChainCommand
{
    /**
     * @var Command
     */
    private Command $command;

    /**
     * @var ChainCommand | null
     */
    private ?ChainCommand $member = null;

    /**
     * @var bool
     */
    private bool $master = false;

    /**
     * @var string | null
     */
    private ?string $parentCommandName = null;

    /**
     * ChainCommand constructor.
     * @param Command $command
     * @param bool $master
     */
    public function __construct(Command $command, bool $master, ?string $name)
    {
        $this->command = $command;
        $this->master = $master;
        $this->parentCommandName = $name;
    }

    /**
     * @return Command
     */
    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * @param Command $command
     * @return ChainCommand
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return ChainCommand|null
     */
    public function getMember(): ?ChainCommand
    {
        return $this->member;
    }

    /**
     * @param ChainCommand|null $member
     * @return ChainCommand
     */
    public function setMember(?ChainCommand $member): self
    {
        $this->member = $member;
        return $this;
    }

    /**
     * @return bool
     */
    public function isMaster(): bool
    {
        return $this->master;
    }

    /**
     * @param bool $master
     * @return ChainCommand
     */
    public function setMaster(bool $master): self
    {
        $this->master = $master;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParentCommandName(): ?string
    {
        return $this->parentCommandName;
    }
}
