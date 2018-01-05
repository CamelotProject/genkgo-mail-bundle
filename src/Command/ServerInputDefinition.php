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

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;

/**
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class ServerInputDefinition extends InputDefinition
{
    public const ADDR_PORT = 1;
    public const PID_FILE = 2;
    public const FILTER = 4;

    public function __construct(int $options)
    {
        $definition = [];
        if ($options & self::ADDR_PORT) {
            $definition[] = new InputArgument(
                'address-port',
                InputArgument::OPTIONAL,
                'The address to listen to (can be address:port, address, or port)',
                '127.0.0.1:2525'
            );
        }
        if ($options & self::PID_FILE) {
            $definition[] = new InputOption(
                'pid-file',
                null,
                InputOption::VALUE_REQUIRED,
                'PID file'
            );
        }
        if ($options & self::FILTER) {
            $definition[] = new InputOption(
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'The value to display (one of port, host, or address)'
            );
        }

        parent::__construct($definition);
    }
}
