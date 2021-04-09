<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;

interface ChainValidatorInterface
{
    public function validate(array $commands, ChainCommand $masterCommand): void;
}