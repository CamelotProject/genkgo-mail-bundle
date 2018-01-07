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

use Camelot\GenkgoMailBundle\Command\ServerStopCommand;
use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class ServerStopCommandTest extends KernelTestCase
{
    private const COMMAND = 'genkgo-mail:server:stop';

    public function testExecuteDefaults(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $server
            ->expects($this->atLeastOnce())
            ->method('stop')
            ->with(null)
            ->willReturn(true)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStopCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Stopped the SMTP server', $output);
    }

    public function testExecuteWithPid(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $pidFile = '.dropbear.pid';
        $server
            ->expects($this->atLeastOnce())
            ->method('stop')
            ->with($pidFile)
            ->willReturn(true)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStopCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            '--pid-file' => $pidFile,
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Stopped the SMTP server', $output);
    }

    public function testExecuteWithException(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $server
            ->expects($this->atLeastOnce())
            ->method('stop')
            ->with(null)
            ->willThrowException(new \Exception('Test generated exception'))
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerStopCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Test generated exception', $output);
    }
}
