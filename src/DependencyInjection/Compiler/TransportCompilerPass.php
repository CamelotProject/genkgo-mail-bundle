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

namespace Camelot\GenkgoMailBundle\DependencyInjection\Compiler;

use Genkgo\Mail\Queue\QueueInterface;
use Genkgo\Mail\Transport\QueueIfFailedTransport;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class TransportCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $this->setupQueueIfFailedTransport($container);
    }

    private function setupQueueIfFailedTransport(ContainerBuilder $container): void
    {
        if ($container->hasDefinition(QueueIfFailedTransport::class)) {
            /** @var QueueInterface[] $queueStorage */
            $queueStorage = [];
            foreach ($container->findTaggedServiceIds('gengko_mail.queue') as $id => $tags) {
                $queueStorage[] = new Reference($id);
            }
            $definition = $container->getDefinition(QueueIfFailedTransport::class);
            $definition->replaceArgument('$queueStorage', $queueStorage);
        }
    }
}
