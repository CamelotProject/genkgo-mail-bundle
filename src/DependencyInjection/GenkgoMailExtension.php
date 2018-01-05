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

namespace Camelot\GenkgoMailBundle\DependencyInjection;

use Genkgo\Mail\Protocol;
use Genkgo\Mail\Queue;
use Genkgo\Mail\Transport;
use Genkgo\Mail\TransportInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class GenkgoMailExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__) . '/Resources/config'));
        $loader->load('commands.php');
        $loader->load('protocols.php');
        $loader->load('queues.php');
        $loader->load('server.php');
        $loader->load('transports.php');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        /*
         * Queues
         */
        $queue = false;
        if ($config['queue']['memory']['enabled']) {
            $queue = true;
        } else {
            $container->removeDefinition(Queue\ArrayObjectQueue::class);
        }
        if ($config['queue']['file']['enabled']) {
            $queue = true;
            $directory = $config['queue']['file']['directory'];
            $mode = $config['queue']['file']['mode'];

            $def = $container->getDefinition(Queue\FilesystemQueue::class);
            $def->replaceArgument('$directory', $directory);
            $def->replaceArgument('$mode', $mode);
        } else {
            $container->removeDefinition(Queue\FilesystemQueue::class);
        }
        if ($config['queue']['redis']['enabled']) {
            $queue = true;
            $def = $container->getDefinition(Queue\RedisQueue::class);
            $def->replaceArgument('$client', new Reference($config['queue']['redis']['service']));
            $def->replaceArgument('$key', $config['queue']['redis']['key']);
        } else {
            $container->removeDefinition(Queue\RedisQueue::class);
        }

        /*
         * Transports
         */
        $transport = $config['transport']['service_id'];

        $def = $container->getDefinition(Protocol\Smtp\ClientFactory::class);
        $def->replaceArgument('$dataSourceName', $config['url']);

        if ($config['transport']['retry_count'] > 0) {
            $def = $container->getDefinition(Transport\RetryIfFailedTransport::class);
            $def->replaceArgument('$retryCount', $config['transport']['retry_count']);
            $def->replaceArgument('$transport', new Reference($transport));

            $transport = Transport\RetryIfFailedTransport::class;
        }

        if ($config['transport']['service_id'] === Transport\PhpMailTransport::class) {
            $def = $container->getDefinition(Transport\PhpMailTransport::class);
            $def->replaceArgument('$parameters', $config['transport']['parameters']);
        }

        if ($queue) {
            $def = $container->getDefinition(Transport\QueueIfFailedTransport::class);
            $def->replaceArgument('$transports', [new Reference($transport)]);

            $transport = Transport\QueueIfFailedTransport::class;
        }

        $container->setAlias(TransportInterface::class, $transport)->setPublic(true);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): Configuration
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }
}
