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

class ServerStopCommand extends Command
{
    protected static $defaultName = 'genkgo-mail:server:stop';

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
            ->setDefinition(new ServerInputDefinition(ServerInputDefinition::PID_FILE))
            ->setDescription('Stops the local SMTP server that was started with the genkgo-mail:server:start command')
            ->setHelp(<<<'EOF'
<info>%command.name%</info> stops the local SMTP server:

  <info>php %command.full_name%</info>
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

        try {
            $this->server->stop($input->getOption('pid-file'));
            $io->success('Stopped the SMTP server.');
        } catch (\Exception $e) {
            $io->error($e->getMessage());

            return 1;
        }

        return 0;
    }
}
