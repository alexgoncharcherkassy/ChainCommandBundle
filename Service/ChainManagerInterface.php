<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;

interface ChainManagerInterface
{
    public function addCommands(array $services): void;
    public function getMasterCommand(): ChainCommand;
    public function getMembers(): iterable;
    public function isMasterCommand(string $name): bool;
    public function isMemberCommand(string $name): bool;
}