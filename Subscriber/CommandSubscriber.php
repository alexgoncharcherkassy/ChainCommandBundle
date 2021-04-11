<?php

namespace AlexGoncharCK\ChainCommandBundle\Subscriber;

use AlexGoncharCK\ChainCommandBundle\Event\CommandEvent;
use AlexGoncharCK\ChainCommandBundle\Service\ChainProcessorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *
 * Class CommandSubscriber
 */
class CommandSubscriber implements EventSubscriberInterface
{
    /**
     * On chain command start
     */
    const CHAIN_COMMAND_START = 'chain_command.start';

    /**
     * On registering member commands
     */
    const CHAIN_COMMAND_MEMBER_REGISTERED = 'chain_command.member_registered';

    /**
     * On before execution master command
     */
    const CHAIN_COMMAND_BEFORE_MASTER_EXECUTED = 'chain_command.before_master_executed';

    /**
     * On after execution master command
     */
    const CHAIN_COMMAND_AFTER_MASTER_EXECUTED = 'chain_command.after_master_executed';

    /**
     * On before execution member command
     */
    const CHAIN_COMMAND_BEFORE_MEMBER_EXECUTED = 'chain_command.before_member_executed';

    /**
     * On after execution member command
     */
    const CHAIN_COMMAND_AFTER_MEMBER_EXECUTED = 'chain_command.after_member_executed';

    /**
     * On chain command finish
     */
    const CHAIN_COMMAND_FINISHED = 'chain_command.chan_command_finished';

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @var ChainProcessorInterface
     */
    private ChainProcessorInterface $processor;


    /**
     * CommandSubscriber constructor.
     * @param LoggerInterface $logger
     * @param ChainProcessorInterface $processor
     */
    public function __construct(LoggerInterface $logger, ChainProcessorInterface $processor)
    {
        $this->logger = $logger;
        $this->processor = $processor;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ConsoleEvents::COMMAND => 'consoleStart',
            self::CHAIN_COMMAND_START => 'start',
            self::CHAIN_COMMAND_MEMBER_REGISTERED => 'memberRegistered',
            self::CHAIN_COMMAND_BEFORE_MASTER_EXECUTED => 'beforeMasterExecuted',
            self::CHAIN_COMMAND_AFTER_MASTER_EXECUTED => 'afterMasterExecuted',
            self::CHAIN_COMMAND_BEFORE_MEMBER_EXECUTED => 'beforeMemberExecuted',
            self::CHAIN_COMMAND_AFTER_MEMBER_EXECUTED => 'afterMemberExecuted',
            self::CHAIN_COMMAND_FINISHED => 'finish',
        ];
    }

    /** Check all run commands are they related to our chain
     *
     * @param ConsoleCommandEvent $event
     */
    public function consoleStart(ConsoleCommandEvent $event): void
    {
        $this->processor->run($event);
    }

    /** Log master command
     *
     * @param CommandEvent $event
     */
    public function start(CommandEvent $event): void
    {
        $this->log(
            sprintf(
                '%s is a master command of a command chain that has registered member commands',
                $event->getCommand()->getCommand()->getName()
            )
        );
    }

    /** Log member commands
     *
     * @param CommandEvent $event
     */
    public function memberRegistered(CommandEvent $event): void
    {
        $this->log(
            sprintf(
                '%s registered as a member of %s command chain',
                $event->getCommand()->getCommand()->getName(),
                $event->getCommand()->getParentCommandName()
            )
        );
    }

    /** Log before master command executed
     *
     * @param CommandEvent $event
     */
    public function beforeMasterExecuted(CommandEvent $event): void
    {
        $this->log(
            sprintf(
                'Executing %s command itself first:',
                $event->getCommand()->getCommand()->getName()
            )
        );
    }

    /** Log master command executed
     *
     * @param CommandEvent $event
     */
    public function afterMasterExecuted(CommandEvent $event): void
    {
        $this->log($event->getOutput()->fetch());
    }

    /** Prepare to run member commands
     *
     * @param CommandEvent $event
     */
    public function beforeMemberExecuted(CommandEvent $event): void
    {
        $this->log(
            sprintf(
                'Executing %s chain members:',
                $event->getCommand()->getCommand()->getName()
            )
        );
    }

    /** Log member command executed
     *
     * @param CommandEvent $event
     */
    public function afterMemberExecuted(CommandEvent $event): void
    {
        $this->log($event->getOutput()->fetch());
    }

    /** Log finish chain commands
     *
     * @param CommandEvent $event
     */
    public function finish(CommandEvent $event): void
    {
        $this->log(
            sprintf(
                'Execution of %s chain completed.',
                $event->getCommand()->getCommand()->getName()
            )
        );
    }

    /** Save to log file
     *
     * @param string $text
     */
    private function log(string $text): void
    {
        $this->logger->info($text);
    }
}