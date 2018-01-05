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
use Symfony\Component\Process\Process;

class ServerRunCommand extends Command
{
    protected static $defaultName = 'genkgo-mail:server:run';

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
            ->setDefinition(new ServerInputDefinition(ServerInputDefinition::ADDR_PORT))
            ->setDescription('Runs a local SMTP server')
            ->setHelp(<<<'EOF'
<info>%command.name%</info> runs a local SMTP server: By default, the server
listens on <comment>127.0.0.1</comment> address and the port number is automatically
selected as the first free port starting from <comment>2525</comment>:

  <info>%command.full_name%</info>

This command blocks the console. If you want to run other commands, stop it by
pressing <comment>Control+C</comment> or use the non-blocking <comment>server:start</comment>
command instead.

Change the default address and port by passing them as an argument:

  <info>%command.full_name% 127.0.0.1:2525</info>
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

        $callback = null;
        $disableOutput = false;
        if ($output->isQuiet()) {
            $disableOutput = true;
        } else {
            // @codeCoverageIgnoreStart
            $callback = function ($type, $buffer) use ($output): void {
                if (Process::ERR === $type && $output instanceof ConsoleOutputInterface) {
                    $output = $output->getErrorOutput();
                }
                $output->write($buffer, false, OutputInterface::OUTPUT_RAW);
            };
            // @codeCoverageIgnoreEnd
        }

        try {
            $config = new SmtpServerConfig($input->getArgument('address-port'));
            $this->server->run($config, $disableOutput, $callback);

            $io->success('Server listening on smtp://' . $config->getAddress());
            $io->comment('Quit the server with CONTROL-C.');
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
