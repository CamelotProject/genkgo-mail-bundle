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

namespace Camelot\GenkgoMailBundle\Bin;

use Camelot\GenkgoMailBundle\Command\ServerInputDefinition as InputDefinition;
use Genkgo\Mail\Protocol\PlainTcpConnectionListener;
use Genkgo\Mail\Protocol\Smtp\Backend\ConsoleBackend;
use Genkgo\Mail\Protocol\Smtp\Capability\DataCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\MailFromCapability;
use Genkgo\Mail\Protocol\Smtp\Capability\RcptToCapability;
use Genkgo\Mail\Protocol\Smtp\GreyList\ArrayGreyList;
use Genkgo\Mail\Protocol\Smtp\Server;
use Genkgo\Mail\Protocol\Smtp\SpamDecideScore;
use Genkgo\Mail\Protocol\Smtp\SpamScore\FixedSpamScore;
use Symfony\Component\Console\Input\ArgvInput;

(new class() {
    public function __construct()
    {
        if (PHP_SAPI !== 'cli') {
            throw new \RuntimeException('Only invokable on the command line.');
        }
    }

    public function run(): void
    {
        $dir = $this->getRootDir();
        require_once "$dir/vendor/autoload.php";

        $input = new ArgvInput(null, new InputDefinition(InputDefinition::ADDR_PORT));
        list($address, $port) = \explode(':', $input->getArgument('address-port'));
        if ($address === null || $port === null) {
            throw new \RuntimeException(\sprintf('Input expected in the format of address:port but "%s" was given', $input->getArgument('address-port')));
        }
        $this->getServer($address, (int) $port)->start();
    }

    private function getServer(string $address, int $port): Server
    {
        $backend = new ConsoleBackend();
        $spam = new FixedSpamScore(0);
        $capabilities = [
            new MailFromCapability(),
            new RcptToCapability($backend),
            new DataCapability($backend, $spam, new ArrayGreyList(), new SpamDecideScore(1, 100)),
        ];

        return new Server(new PlainTcpConnectionListener($address, $port), $capabilities, $address);
    }

    private function getRootDir(): string
    {
        $dir = __DIR__;
        while (!\file_exists($dir . '/composer.json')) {
            if ($dir === \dirname($dir)) {
                throw new \RuntimeException('Unable to find project root.');
            }
            $dir = \dirname($dir);
        }

        return $dir;
    }
})->run();
