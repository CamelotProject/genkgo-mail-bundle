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

use ArrayObject;
use Genkgo\Mail\Queue;
use Predis\ClientInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container): void {
    $pattern = '%kernel.project_dir%/vendor/genkgo/mail/src/Queue/*';

    $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
        ->load('Genkgo\\Mail\\Queue\\', $pattern)
        ->set('gengko_mail.queue.memory.storage')
            ->class(ArrayObject::class)
        ->set(Queue\ArrayObjectQueue::class)
            ->args([
                '$storage' => ref('gengko_mail.queue.memory.storage'),
            ])
            ->tag('gengko_mail.queue')
        ->set(Queue\FilesystemQueue::class)
            ->args([
                '$directory' => null,
                '$mode' => 0750,
            ])
            ->tag('gengko_mail.queue')
        ->set(Queue\RedisQueue::class)
            ->args([
                 '$client' => ref(ClientInterface::class),
                 '$key' => 'gengko-mail',
            ])
            ->tag('gengko_mail.queue')
    ;
};
