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

use Camelot\GenkgoMailBundle\Server\SmtpServerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class ServerStatusCommand extends Command
{
    protected static $defaultName = 'genkgo-mail:server:status';

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
            ->setDefinition(new ServerInputDefinition(ServerInputDefinition::PID_FILE | ServerInputDefinition::FILTER))
            ->setDescription('Outputs the status of the local SMTP server for the given address')
            ->setHelp(<<<'EOF'
<info>%command.name%</info> shows the details of the given local SMTP
server, such as the address and port where it is listening to:

  <info>php %command.full_name%</info>

To get the information as a machine readable format, use the
<comment>--filter</comment> option:

<info>php %command.full_name% --filter=port</info>

Supported values are <comment>port</comment>, <comment>host</comment>, and <comment>address</comment>.
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
        if ($filter = $input->getOption('filter')) {
            if ($this->server->isRunning($input->getOption('pid-file'))) {
                $address = $this->server->getAddress($input->getOption('pid-file'));
                [$host, $port] = \explode(':', $address);
                if ('address' === $filter) {
                    $output->write($address);
                } elseif ('host' === $filter) {
                    $output->write($host);
                } elseif ('port' === $filter) {
                    $output->write($port);
                } else {
                    throw new \InvalidArgumentException(\sprintf('"%s" is not a valid filter.', $filter));
                }
            } else {
                return 1;
            }
        } else {
            if ($this->server->isRunning($input->getOption('pid-file'))) {
                $io->success('SMTP server still listening on smtp://' . $this->server->getAddress($input->getOption('pid-file')));
            } else {
                $io->warning('No SMTP server is listening.');

                return 1;
            }
        }

        return 0;
    }
}
