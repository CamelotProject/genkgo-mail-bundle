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

use Genkgo\Mail\Protocol;
use Genkgo\Mail\Transport\EnvelopeFactory;
use Genkgo\Mail\Transport\PhpMailTransport;
use Genkgo\Mail\Transport\QueueIfFailedTransport;
use Genkgo\Mail\Transport\RetryIfFailedTransport;
use Genkgo\Mail\Transport\SmtpTransport;
use Genkgo\Mail\TransportInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

return function (ContainerConfigurator $container): void {
    $pattern = '%kernel.project_dir%/vendor/genkgo/mail/src/Transport/*';
    $exclude = '%kernel.project_dir%/vendor/genkgo/mail/src/Transport/*{Factory}';

    $container->services()
        ->defaults()
            ->private()
            ->autowire()
            ->autoconfigure()
        ->load('Genkgo\\Mail\\Transport\\', $pattern)
            ->exclude($exclude)
        ->set(EnvelopeFactory::class)
            ->factory([EnvelopeFactory::class, 'useExtractedHeader'])
        ->set(SmtpTransport::class)
            ->args([
                '$client' => ref(Protocol\Smtp\Client::class),
                '$envelopeFactory' => ref(EnvelopeFactory::class),
            ])
        ->set(PhpMailTransport::class)
            ->args([
                '$envelopeFactory' => ref(EnvelopeFactory::class),
                '$parameters' => null,
            ])
        ->set(QueueIfFailedTransport::class)
            ->args([
                '$transports' => null,
                '$queueStorage' => null,
            ])
        ->set(RetryIfFailedTransport::class)
            ->args([
                '$transport' => ref(TransportInterface::class),
                '$retryCount' => null,
            ])
    ;
};
