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

namespace Camelot\GenkgoMailBundle\Tests\DependencyInjection\Compiler;

use Camelot\GenkgoMailBundle\DependencyInjection\Compiler\TransportCompilerPass;
use Genkgo\Mail\Queue;
use Genkgo\Mail\Transport\QueueIfFailedTransport;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class TransportCompilerPassTest extends AbstractCompilerPassTestCase
{
    public function testProcess(): void
    {
        $queueService = new Definition();
        $this->setDefinition(Queue\ArrayObjectQueue::class, $queueService);
        $queueService->addTag('gengko_mail.queue');

        $queueStorageService = new Definition();
        $this->setDefinition(QueueIfFailedTransport::class, $queueStorageService);
        $queueStorageService->setArgument('$queueStorage', null);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithArgument(
            QueueIfFailedTransport::class,
            '$queueStorage',
            [new Reference(Queue\ArrayObjectQueue::class)]
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function registerCompilerPass(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TransportCompilerPass());
    }
}
