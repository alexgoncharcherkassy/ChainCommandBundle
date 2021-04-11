<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

interface ChainValidatorInterface
{
    public function validate(array $commands): void;
}
