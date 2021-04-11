<?php

namespace AlexGoncharCK\ChainCommandBundle\tests\Service;

use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HelloCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HiCommand;
use AlexGoncharCK\ChainCommandBundle\Service\ChainManager;
use AlexGoncharCK\ChainCommandBundle\Service\ChainManagerInterface;
use AlexGoncharCK\ChainCommandBundle\Service\ChainValidator;
use PHPUnit\Framework\TestCase;

class ChainManagerTest extends TestCase
{
    private ChainManagerInterface $manager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new ChainManager(new ChainValidator());
    }

    public function testAddCommands()
    {
        $helloCommand = new HelloCommand();
        $hiCommand = new HiCommand();
        $commands = [
            [
                'command' => $helloCommand,
                'master' => true,
                'parent' => null,
            ],
            [
                'command' => $hiCommand,
                'master' => false,
                'parent' => 'bar:hello',
            ],
        ];

        $this->manager->addCommands($commands);
    }
}
