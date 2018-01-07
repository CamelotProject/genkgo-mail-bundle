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

use Camelot\GenkgoMailBundle\Command\ServerStartCommand;
use Camelot\GenkgoMailBundle\Server\SmtpServerConfig;
use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ServerStartCommandTest extends KernelTestCase
{
    private const COMMAND = 'genkgo-mail:server:start';

    public function testExecuteDefaults(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $server
            ->expects($this->atLeastOnce())
            ->method('start')
            ->with($config)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStartCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Server listening on smtp://127.0.0.1:25', $output);
    }

    public function testExecuteWithAddress(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig('koala:42');
        $server
            ->expects($this->atLeastOnce())
            ->method('start')
            ->with($config)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStartCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'address-port' => 'koala:42',
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Server listening on smtp://koala:42', $output);
    }

    public function testExecuteWithPid(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $pidFile = '.dropbear.pid';
        $server
            ->expects($this->atLeastOnce())
            ->method('start')
            ->with($config, $pidFile)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStartCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Server listening on smtp://127.0.0.1:25', $output);
    }

    public function testExecuteWithAlreadyRunningServer(): void
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

        $application->add(new ServerStartCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('The SMTP server is already running (listening on smtp://127.0.0.1:25).', $output);
    }

    public function testExecuteWithException(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $server
            ->expects($this->atLeastOnce())
            ->method('start')
            ->with($config)
            ->willThrowException(new \Exception('Test generated exception'))
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStartCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Test generated exception', $output);
    }
}
