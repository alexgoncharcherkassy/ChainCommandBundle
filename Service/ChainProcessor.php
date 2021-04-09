<?php

namespace AlexGoncharCK\ChainCommandBundle\Service;

use AlexGoncharCK\ChainCommandBundle\Event\CommandEvent;
use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use AlexGoncharCK\ChainCommandBundle\Subscriber\CommandSubscriber;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChainProcessor implements ChainProcessorInterface
{
    /**
     * @var ChainManagerInterface
     */
    private ChainManagerInterface $manager;

    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var InputInterface
     */
    private InputInterface $input;

    /** Console output
     *
     * @var OutputInterface
     */
    private OutputInterface $output;

    /**
     * ChainProcessor constructor.
     * @param ChainManagerInterface $manager
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ChainManagerInterface $manager, EventDispatcherInterface $eventDispatcher)
    {
        $this->manager = $manager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * If master command then run member,
     * if command is on chain then throw exception
     * @param ConsoleCommandEvent $event
     */
    public function run(ConsoleCommandEvent $event): void
    {
        $command = $event->getCommand();
        $name = $command->getName();

        if ($this->manager->isMemberCommand($name)) {
            $event->disableCommand();
            $event->stopPropagation();

            $event->getOutput()->writeln(
                sprintf(
                    'Error: %s command is a member of %s command chain and cannot be executed on its own.',
                    $name,
                    $this->manager->getMasterCommand()->getCommand()->getName()
                )
            );
        }

        if ($this->manager->isMasterCommand($name)) {
            $event->disableCommand();

            $this->input = $event->getInput();
            $this->output = $event->getOutput();

            $this->start($this->manager->getMasterCommand());
        }
    }

    /** Start chain
     *
     * @param ChainCommand $command
     */
    public function start(ChainCommand $command): void
    {
        $this->runEvent(CommandSubscriber::CHAIN_COMMAND_START, $command);

        $this->registerMembers($command);

        $this->runMaster($command);
        $this->runMembers($command);
    }

    /** register members
     *
     * @param ChainCommand $main
     * @return array|mixed
     */
    private function registerMembers(ChainCommand $main): void
    {
        foreach ($this->manager->getMembers() as $child) {
            $this->runEvent(
                CommandSubscriber::CHAIN_COMMAND_MEMBER_REGISTERED,
                $main,
                $child['command']
            );
        }
    }

    /** Run master command
     *
     * @param ChainCommand $command
     */
    private function runMaster(ChainCommand $command)
    {
        $this->runEvent(CommandSubscriber::CHAIN_COMMAND_BEFORE_MASTER_EXECUTED, $command);
        $buffedOutput = $this->runCommand($command, $this->input);
        $this->runEvent(CommandSubscriber::CHAIN_COMMAND_AFTER_MASTER_EXECUTED, $command, $buffedOutput);
    }

    /** Run member commands
     *
     * @param ChainCommand $command
     */
    private function runMembers(ChainCommand $command)
    {
        $this->runEvent(CommandSubscriber::CHAIN_COMMAND_BEFORE_MEMBER_EXECUTED, $command);

        /** @var ChainCommand $member */
        foreach ($this->manager->getMembers() as $member) {
            $buffedOutput = $this->runCommand($member, new ArrayInput([]));
            $this->runEvent(CommandSubscriber::CHAIN_COMMAND_AFTER_MEMBER_EXECUTED, $command, $buffedOutput);
        }

        $this->runEvent(CommandSubscriber::CHAIN_COMMAND_FINISHED, $command);
    }

    /** Run single command
     *
     * @param ChainCommand $command
     * @param InputInterface $input
     * @return BufferedOutput
     */
    private function runCommand(ChainCommand $command, InputInterface $input): BufferedOutput
    {
        $buffer = new BufferedOutput();
        $command->getCommand()->run($input, $buffer);

        $output = $buffer->fetch();
        $buffer->write($output);
        $this->output->write($output);

        return $buffer;
    }

    /** Run event
     *
     * @param string $name
     * @param ChainCommand $command
     * @param BufferedOutput | null $output
     */
    private function runEvent(string $name, ChainCommand $command, $output = null): void
    {
        $event = new CommandEvent($command, $output ?? $this->output);
        $this->eventDispatcher->dispatch($event, $name);
    }
}
