<?php

namespace AlexGoncharCK\ChainCommandBundle\Service\Model;

use Symfony\Component\Console\Command\Command;

class ChainCommand
{
    private Command $command;

    /**
     * @var ChainCommand | null
     */
    private ?ChainCommand $member = null;

    private bool $master = false;

    /**
     * @var string | null
     */
    private ?string $parentCommandName = null;

    /**
     * ChainCommand constructor.
     */
    public function __construct(Command $command, bool $master, ?string $name)
    {
        $this->command = $command;
        $this->master = $master;
        $this->parentCommandName = $name;
    }

    public function getCommand(): Command
    {
        return $this->command;
    }

    /**
     * @return ChainCommand
     */
    public function setCommand(Command $command): self
    {
        $this->command = $command;

        return $this;
    }

    public function getMember(): ?ChainCommand
    {
        return $this->member;
    }

    /**
     * @return ChainCommand
     */
    public function setMember(?ChainCommand $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function isMaster(): bool
    {
        return $this->master;
    }

    /**
     * @return ChainCommand
     */
    public function setMaster(bool $master): self
    {
        $this->master = $master;

        return $this;
    }

    public function getParentCommandName(): ?string
    {
        return $this->parentCommandName;
    }
}
