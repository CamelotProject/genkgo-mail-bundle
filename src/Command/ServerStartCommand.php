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

namespace Camelot\GenkgoMailBundle\Command;

use Camelot\GenkgoMailBundle\Server\SmtpServerConfig;
use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Process\Process;

final class ServerStartCommand extends Command
{
    protected static $defaultName = 'genkgo-mail:server:start';

    private $server;

    public function __construct(SmtpServerInterface $server)
    {
        $this->server = $server;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return \class_exists(Process::class);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDefinition(new ServerInputDefinition(ServerInputDefinition::ADDR_PORT | ServerInputDefinition::PID_FILE))
            ->setDescription('Starts a local SMTP server in the background')
            ->setHelp(
                <<<'EOF'
<info>%command.name%</info> runs a local SMTP server: By default, the server
listens on <comment>127.0.0.1</comment> address and the port number is automatically selected
as the first free port starting from <comment>2525</comment>:

  <info>php %command.full_name%</info>

The server is run in the background and you can keep executing other commands.
Execute <comment>server:stop</comment> to stop it.

Change the default address and port by passing them as an argument:

  <info>php %command.full_name% 127.0.0.1:2525</info>

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output instanceof ConsoleOutputInterface ? $output->getErrorOutput() : $output);

        if (!\extension_loaded('pcntl')) {
            // @codeCoverageIgnoreStart
            $io->error(['This command needs the pcntl extension to run.']);

            return 1;
            // @codeCoverageIgnoreEnd
        }
        $this->getApplication()->setDispatcher(new EventDispatcher());

        try {
            if ($this->server->isRunning($input->getOption('pid-file'))) {
                $io->error(\sprintf('The SMTP server is already running (listening on smtp://%s).', $this->server->getAddress($input->getOption('pid-file'))));

                return 1;
            }

            $config = new SmtpServerConfig($input->getArgument('address-port'));
            $result = $this->server->start($config, $input->getOption('pid-file'));
            if ($result === SmtpServerInterface::STARTED) {
                $io->success('Server listening on smtp://' . $config->getAddress());
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
