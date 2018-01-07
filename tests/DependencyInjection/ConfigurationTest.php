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

namespace Camelot\GenkgoMailBundle\Tests\DependencyInjection;

use Camelot\GenkgoMailBundle\DependencyInjection\Configuration;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends KernelTestCase
{
    private static $expectedConfig = [
        'url' => 'smtp://localhost:25',
        'transport' => [
            'service_id' => 'Genkgo\Mail\Transport\SmtpTransport',
            'retry_count' => 0,
        ],
        'queue' => [
            'memory' => [
                'enabled' => false,
            ],
            'file' => [
                'enabled' => false,
                'directory' => '%kernel.cache_dir%/spool',
                'mode' => 0750,
            ],
            'redis' => [
                'enabled' => false,
                'service' => 'Predis\ClientInterface',
                'key' => 'genkgo-mail',
            ],
        ],
    ];

    public function testValidSubClass(): void
    {
        $configuration = new Configuration(true);

        self::assertInstanceOf(ConfigurationInterface::class, $configuration);
    }

    public function testConfiguration(): void
    {
        $processor = new Processor();
        $configuration = new Configuration(true);
        $configs = $processor->processConfiguration($configuration, []);

        self::assertSame(static::$expectedConfig, $configs);
    }
}
