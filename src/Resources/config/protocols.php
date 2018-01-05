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

namespace Camelot\GenkgoMailBundle;

use Genkgo\Mail\Protocol\Smtp;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container): void {
    $pattern = '%kernel.project_dir%/vendor/genkgo/mail/src/Protocol/*';

    $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
        ->load('Genkgo\\Mail\\Protocol\\', $pattern)
        ->set(Smtp\ClientFactory::class)
            ->factory([Smtp\ClientFactory::class, 'fromString'])
            ->arg('$dataSourceName', null)
        ->set(Smtp\Client::class)
            ->factory([ref(Smtp\ClientFactory::class), 'newClient'])
    ;
};
