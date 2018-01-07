<?php

declare(strict_types=1);

/*
 * This file is part of the Camelot Genkgo Mail bundle.
 *
 * (c) Gawain Lynch <gawain.lynch@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Camelot\GenkgoMailBundle\Tests\Command;

use Camelot\GenkgoMailBundle\Command\ServerStatusCommand;
use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ServerStatusCommandTest extends KernelTestCase
{
    private const COMMAND = 'genkgo-mail:server:status';

    public function testExecuteDefaultsServerRunning(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with(null)
            ->willReturn(true)
        ;
        $server
            ->expects($this->atLeastOnce())
            ->method('getAddress')
            ->willReturn('127.0.0.1:25')
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('SMTP server still listening on smtp://127.0.0.1:25', $output);
    }

    public function testExecuteDefaultsServerNotRunning(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with(null)
            ->willReturn(false)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('No SMTP server is listening', $output);
    }

    public function testExecuteWithPid(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $pidFile = '.dropbear.pid';
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with($pidFile)
            ->willReturn(true)
        ;
        $server
            ->expects($this->atLeastOnce())
            ->method('getAddress')
            ->willReturn('127.0.0.1:25')
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('SMTP server still listening on smtp://127.0.0.1:25', $output);
    }

    public function testExecuteWithFilterAddress(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $pidFile = '.dropbear.pid';
        $filter = 'address';
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with($pidFile)
            ->willReturn(true)
        ;
        $server
            ->expects($this->atLeastOnce())
            ->method('getAddress')
            ->willReturn('127.0.0.1:25')
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
            '--filter' => $filter,
        ]);

        $output = $commandTester->getDisplay();
        self::assertSame('127.0.0.1:25', $output);
    }

    public function testExecuteWithFilterHost(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $pidFile = '.dropbear.pid';
        $filter = 'host';
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with($pidFile)
            ->willReturn(true)
        ;
        $server
            ->expects($this->atLeastOnce())
            ->method('getAddress')
            ->willReturn('127.0.0.1:25')
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
            '--filter' => $filter,
        ]);

        $output = $commandTester->getDisplay();
        self::assertSame('127.0.0.1', $output);
    }

    public function testExecuteWithFilterPort(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $pidFile = '.dropbear.pid';
        $filter = 'port';
        $server
            ->expects($this->atLeastOnce())
            ->method('isRunning')
            ->with($pidFile)
            ->willReturn(true)
        ;
        $server
            ->expects($this->atLeastOnce())
            ->method('getAddress')
            ->willReturn('127.0.0.1:25')
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStatusCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
            '--filter' => $filter,
        ]);

        $output = $commandTester->getDisplay();
        self::assertSame('25', $output);
    }
}
