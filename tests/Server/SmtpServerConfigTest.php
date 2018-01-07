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

namespace Camelot\GenkgoMailBundle\Tests\Server;

use Camelot\GenkgoMailBundle\Server\SmtpServerConfig;
use Generator;
use PHPUnit\Framework\TestCase;

class SmtpServerConfigTest extends TestCase
{
    public function testEmptyDsn(): void
    {
        $config = new SmtpServerConfig();

        self::assertSame('127.0.0.1:2525', $config->getAddress());
        self::assertSame('127.0.0.1', $config->getHostname());
        self::assertSame(2525, $config->getPort());
    }

    public function providerDsn(): Generator
    {
        yield ['localhost', 'localhost:2525', 'localhost', 2525];
        yield ['localhost:25', 'localhost:25', 'localhost', 25];
        yield ['*:42', '0.0.0.0:42', '0.0.0.0', 42];
        yield [42, '127.0.0.1:42', '127.0.0.1', 42];
    }

    /**
     * @dataProvider providerDsn
     */
    public function testDsn($dsn, $expectedAddress, $expectedHostname, $expectedPort): void
    {
        $config = new SmtpServerConfig($dsn);

        self::assertSame($expectedAddress, $config->getAddress());
        self::assertSame($expectedHostname, $config->getHostname());
        self::assertSame($expectedPort, $config->getPort());
    }
}
