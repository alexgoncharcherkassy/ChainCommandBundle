<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use Symfony\Component\Console\Event\ConsoleCommandEvent;

interface ChainProcessorInterface
{
    public function run(ConsoleCommandEvent $event): void;
}
