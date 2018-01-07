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

use Camelot\GenkgoMailBundle\Command\ServerRunCommand;
use Camelot\GenkgoMailBundle\Server\SmtpServerConfig;
use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;

class ServerRunCommandTest extends KernelTestCase
{
    private const COMMAND = 'genkgo-mail:server:run';

    public function testExecuteDefaults(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $server
            ->expects($this->atLeastOnce())
            ->method('run')
            ->with($config, false)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerRunCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Server listening on smtp://127.0.0.1:25', $output);
        self::assertContains('Quit the server with CONTROL-C.', $output);
    }

    public function testExecuteWithAddress(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig('koala:42');
        $server
            ->expects($this->atLeastOnce())
            ->method('run')
            ->with($config, false)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerRunCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
            'address-port' => 'koala:42',
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Server listening on smtp://koala:42', $output);
        self::assertContains('Quit the server with CONTROL-C.', $output);
    }

    public function testExecuteSilent(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $server
            ->expects($this->atLeastOnce())
            ->method('run')
            ->with($config, true)
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerRunCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ], [
            'verbosity' => OutputInterface::VERBOSITY_QUIET,
        ]);

        $output = $commandTester->getDisplay();
        self::assertNotContains('Server listening on smtp://127.0.0.1:25', $output);
        self::assertNotContains('Quit the server with CONTROL-C.', $output);
    }

    public function testExecuteWithException(): void
    {
        $server = $this->createMock(SmtpServerInterface::class);
        $config = new SmtpServerConfig();
        $server
            ->expects($this->atLeastOnce())
            ->method('run')
            ->with($config, false)
            ->willThrowException(new \Exception('Test generated exception'))
        ;

        $kernel = self::bootKernel();
        $application = new Application($kernel);

        $application->add(new ServerRunCommand($server));

        $command = $application->find(self::COMMAND);
        $commandTester = new CommandTester($command);

        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        $output = $commandTester->getDisplay();
        self::assertContains('Test generated exception', $output);
    }
}
