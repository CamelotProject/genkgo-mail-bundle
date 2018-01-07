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

use Camelot\GenkgoMailBundle\Command\ServerInputDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ServerInputDefinitionTest extends TestCase
{
    public function testAddressOnly(): void
    {
        $definition = new ServerInputDefinition(ServerInputDefinition::ADDR_PORT);

        self::assertInstanceOf(InputArgument::class, $definition->getArgument('address-port'));
        self::assertFalse($definition->hasOption('pid-file'));
        self::assertFalse($definition->hasOption('filter'));
    }

    public function testPidFileOnly(): void
    {
        $definition = new ServerInputDefinition(ServerInputDefinition::PID_FILE);

        self::assertInstanceOf(InputOption::class, $definition->getOption('pid-file'));
        self::assertFalse($definition->hasArgument('address-port'));
        self::assertFalse($definition->hasOption('filter'));
    }

    public function testFilterOnly(): void
    {
        $definition = new ServerInputDefinition(ServerInputDefinition::FILTER);

        self::assertInstanceOf(InputOption::class, $definition->getOption('filter'));
        self::assertFalse($definition->hasArgument('address-port'));
        self::assertFalse($definition->hasOption('pid-file'));
    }

    public function testAddressPid(): void
    {
        $definition = new ServerInputDefinition(
            ServerInputDefinition::ADDR_PORT | ServerInputDefinition::PID_FILE
        );

        self::assertInstanceOf(InputArgument::class, $definition->getArgument('address-port'));
        self::assertInstanceOf(InputOption::class, $definition->getOption('pid-file'));
        self::assertFalse($definition->hasOption('filter'));
    }

    public function testAddressFilter(): void
    {
        $definition = new ServerInputDefinition(
            ServerInputDefinition::ADDR_PORT | ServerInputDefinition::FILTER
        );

        self::assertInstanceOf(InputArgument::class, $definition->getArgument('address-port'));
        self::assertInstanceOf(InputOption::class, $definition->getOption('filter'));
        self::assertFalse($definition->hasOption('pid-file'));
    }

    public function testPidFilter(): void
    {
        $definition = new ServerInputDefinition(
            ServerInputDefinition::PID_FILE | ServerInputDefinition::FILTER
        );

        self::assertInstanceOf(InputOption::class, $definition->getOption('pid-file'));
        self::assertInstanceOf(InputOption::class, $definition->getOption('filter'));
        self::assertFalse($definition->hasArgument('address-port'));
    }

    public function testAllOptions(): void
    {
        $definition = new ServerInputDefinition(
            ServerInputDefinition::ADDR_PORT | ServerInputDefinition::PID_FILE | ServerInputDefinition::FILTER
        );

        self::assertInstanceOf(InputArgument::class, $definition->getArgument('address-port'));
        self::assertInstanceOf(InputOption::class, $definition->getOption('pid-file'));
        self::assertInstanceOf(InputOption::class, $definition->getOption('filter'));
    }
}
