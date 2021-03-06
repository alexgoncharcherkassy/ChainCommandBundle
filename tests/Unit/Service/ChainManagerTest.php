<?php

namespace AlexGoncharCK\ChainCommandBundle\tests\Unit\Service;

use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\ByeCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HelloCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HiCommand;
use AlexGoncharCK\ChainCommandBundle\Service\ChainManager;
use AlexGoncharCK\ChainCommandBundle\Service\ChainManagerInterface;
use AlexGoncharCK\ChainCommandBundle\Service\ChainValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;

class ChainManagerTest extends TestCase
{
    private ?ChainManagerInterface $manager = null;
    private Command $helloCommand;
    private Command $hiCommand;
    private Command $byeCommand;
    private array $commands = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = new ChainManager(new ChainValidator());
        $this->helloCommand = new HelloCommand();
        $this->hiCommand = new HiCommand();
        $this->byeCommand = new ByeCommand();
        $this->commands = [
            [
                'command' => $this->helloCommand,
                'master' => true,
            ],
            [
                'command' => $this->hiCommand,
                'parent' => 'fixtures:hello',
            ],
            [
                'command' => $this->byeCommand,
                'parent' => 'fixtures:hi',
            ],
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->manager = null;
    }

    public function testAddCommands()
    {
        $this->manager->addCommands($this->commands);

        // includes master command
        $numberOfCommands = 1;

        foreach ($this->manager->getMembers() as $member) {
            ++$numberOfCommands;
        }

        $this->assertEquals(3, $numberOfCommands);
    }

    public function testAddCommandsFailCommandNotFound()
    {
        $commands = [
            [
                'command' => $this->helloCommand,
                'master' => true,
            ],
            [
                'command' => $this->hiCommand,
                'master' => false,
                'parent' => 'fixtures:bad',
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Command with name: 'fixtures:bad' not found in chain");

        $this->manager->addCommands($commands);
    }

    public function testAddCommandsFailNotSingleMasterCommand()
    {
        $commands = [
            [
                'command' => $this->helloCommand,
                'master' => true,
            ],
            [
                'command' => $this->hiCommand,
                'master' => true,
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Master command should be single');

        $this->manager->addCommands($commands);
    }

    public function testAddCommandsFailTheSameParent()
    {
        $commands = [
            [
                'command' => $this->helloCommand,
                'master' => true,
            ],
            [
                'command' => $this->hiCommand,
                'parent' => 'fixtures:hello',
            ],
            [
                'command' => $this->byeCommand,
                'parent' => 'fixtures:hello',
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            "Parent command with name: 'fixtures:hello' already have member command with name: 'fixtures:hi'"
        );

        $this->manager->addCommands($commands);
    }

    public function testGetMembers()
    {
        $commandNames = [
            'fixtures:hi',
            'fixtures:bye',
        ];

        $this->manager->addCommands($this->commands);

        foreach ($this->manager->getMembers() as $key => $member) {
            $this->assertEquals($commandNames[$key], $member->getCommand()->getName());
        }
    }

    public function testIsMasterCommand()
    {
        $this->manager->addCommands($this->commands);

        $this->assertTrue($this->manager->isMasterCommand('fixtures:hello'));
    }

    public function testIsMasterCommandFail()
    {
        $this->manager->addCommands($this->commands);

        $this->assertFalse($this->manager->isMasterCommand('fixtures:hi'));
    }

    public function testIsMemberCommand()
    {
        $this->manager->addCommands($this->commands);

        $this->assertTrue($this->manager->isMemberCommand('fixtures:hi'));
    }

    public function testIsMemberCommandFail()
    {
        $this->manager->addCommands($this->commands);

        $this->assertFalse($this->manager->isMemberCommand('fixtures:hello'));
    }

    public function testGetMasterCommand()
    {
        $this->manager->addCommands($this->commands);

        $master = $this->manager->getMasterCommand();

        $this->assertEquals($this->helloCommand->getName(), $master->getCommand()->getName());
        $this->assertEquals($this->hiCommand->getName(), $master->getMember()->getCommand()->getName());
        $this->assertNull($master->getParentCommandName());
        $this->assertTrue($this->manager->getMasterCommand()->isMaster());
    }
}
