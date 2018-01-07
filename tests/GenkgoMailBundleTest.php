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

namespace Camelot\GenkgoMailBundle\Tests;

use Camelot\GenkgoMailBundle\DependencyInjection\Compiler\TransportCompilerPass;
use Camelot\GenkgoMailBundle\GenkgoMailBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class GenkgoMailBundleTest extends TestCase
{
    public function testBuild(): void
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container
            ->expects($this->once())
            ->method('addCompilerPass')
            ->with(new TransportCompilerPass())
        ;

        $bundle = new GenkgoMailBundle();
        $bundle->build($container);
    }
}
