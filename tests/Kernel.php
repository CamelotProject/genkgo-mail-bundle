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

class Kernel
{
    /**
     * @var Fixtures\App\TestKernel
     */
    private static $instance;

    /**
     * @return Fixtures\App\TestKernel
     */
    public static function make()
    {
        if (null === static::$instance) {
            static::$instance = new Fixtures\App\TestKernel('test', true);

            static::$instance->boot();
        }

        return static::$instance;
    }
}
