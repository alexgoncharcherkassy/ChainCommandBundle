<?php

namespace AlexGoncharCK\ChainCommandBundle\tests\Unit\Service;

use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\ByeCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HelloCommand;
use AlexGoncharCK\ChainCommandBundle\Fixtures\Command\HiCommand;
use AlexGoncharCK\ChainCommandBundle\Service\ChainValidator;
use AlexGoncharCK\ChainCommandBundle\Service\Model\ChainCommand;
use PHPUnit\Framework\TestCase;

class ChainValidatorTest extends TestCase
{
    private ?ChainValidator $validator = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ChainValidator();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->validator = null;
    }

    public function testValidate()
    {
       $helloCommand = new ChainCommand(new HelloCommand(), true, null);
       $hiCommand = new ChainCommand(new HiCommand(), false, 'bar:hello');
       $byeCommand = new ChainCommand(new ByeCommand(), false, 'bar:hi');

       $helloCommand->setMember($hiCommand);
       $hiCommand->setMember($byeCommand);

        $this->validator->validate([$helloCommand, $hiCommand, $byeCommand]);

        $this->assertTrue(true);
    }

    public function testValidateFailNotSingleMaster()
    {
        $helloCommand = new ChainCommand(new HelloCommand(), true, null);
        $hiCommand = new ChainCommand(new HiCommand(), true, 'bar:hello');
        $byeCommand = new ChainCommand(new ByeCommand(), false, 'bar:hi');

        $helloCommand->setMember($hiCommand);
        $hiCommand->setMember($byeCommand);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Master command should be single');

        $this->validator->validate([$helloCommand, $hiCommand, $byeCommand]);
    }

    public function testValidateFailNotPresentMaster()
    {
        $helloCommand = new ChainCommand(new HelloCommand(), false, null);
        $hiCommand = new ChainCommand(new HiCommand(), false, 'bar:hello');
        $byeCommand = new ChainCommand(new ByeCommand(), false, 'bar:hi');

        $helloCommand->setMember($hiCommand);
        $hiCommand->setMember($byeCommand);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Master command not present');

        $this->validator->validate([$helloCommand, $hiCommand, $byeCommand]);
    }
}
