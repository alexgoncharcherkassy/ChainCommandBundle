<?php

namespace AlexGoncharCK\ChainCommandBundle\tests\Functional\Service;

use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\ByeCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HelloCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HiCommand;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ChainProcessorTest extends KernelTestCase
{
    private Application $app;
    private OutputInterface $output;
    private array $commands = [];
    private Command $helloCommand;
    private Command $hiCommand;
    private Command $byeCommand;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->app = new Application(self::$kernel);
        $this->app->setAutoExit(false);
        $this->output = new BufferedOutput();
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
        $this->app->add($this->helloCommand);
        $this->app->add($this->hiCommand);
        $this->app->add($this->byeCommand);
    }

    public function testRun()
    {
        $container = self::$container;
        $manager = $container->get('chain_command_bundle.chain_manager');
        $manager->addCommands($this->commands);

        $code = $this->app->run(new ArrayInput([$this->helloCommand->getName()]), $this->output);
        $this->assertEquals(113, $code);
        $this->assertEquals($this->output->fetch(), "Hello from Bar!\nHi from Bar!\nBye from Bar!\n");
    }

    public function testRunFailMember()
    {
        $container = self::$container;
        $manager = $container->get('chain_command_bundle.chain_manager');
        $manager->addCommands($this->commands);

        $code = $this->app->run(new ArrayInput([$this->hiCommand->getName()]), $this->output);
        $this->assertEquals(113, $code);
        $this->assertEquals(
            $this->output->fetch(),
            "Error: fixtures:hi command is a member of fixtures:hello command chain and cannot be executed on its own.\n"
        );
    }

    public function testRunNotMember()
    {
        $container = self::$container;
        $manager = $container->get('chain_command_bundle.chain_manager');
        $commands = $this->commands;
        array_splice($commands, -1);
        $manager->addCommands($commands);

        $code = $this->app->run(new ArrayInput([$this->byeCommand->getName()]), $this->output);
        $this->assertEquals(0, $code);
        $this->assertEquals($this->output->fetch(), "Bye from Bar!\n");
    }
}
