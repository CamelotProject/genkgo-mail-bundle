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

use Genkgo\Mail\Transport\FileTransport;
use Genkgo\Mail\Transport\NullTransport;
use Genkgo\Mail\Transport\PhpMailTransport;
use Genkgo\Mail\Transport\SmtpTransport;
use Predis\ClientInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Gawain Lynch <gawain.lynch@gmail.com>
 */
final class Configuration implements ConfigurationInterface
{
    /** @var bool bool The kernel.debug value */
    private $debug;

    public function __construct(bool $debug)
    {
        $this->debug = $debug;
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('genko_mail');

        $rootNode
            ->children()
                ->scalarNode('url')
                    ->defaultValue('smtp://localhost:25')
                ->end()
                ->append($this->getTransportNode())
                ->append($this->getQueueNode())
            ->end()
        ;

        return $treeBuilder;
    }

    private function getTransportNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('transport');

        $supportedTransports = [
            SmtpTransport::class,
            FileTransport::class,
            NullTransport::class,
            PhpMailTransport::class,
        ];

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('service_id')
                    ->defaultValue(SmtpTransport::class)
                    ->info('Container service ID (usually FQCN) for the desired transport')
                    ->example($supportedTransports)
                ->end()
                ->arrayNode('service_parameters')->end()
                ->scalarNode('retry_count')
                    ->defaultValue(0)
                    ->info('Number of times to attempt to send a message before aborting, or saving to a storage queue')
                ->end()
            ->end()
        ;

        return $node;
    }

    private function getQueueNode(): ArrayNodeDefinition
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('queue');

        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('memory')
                    ->canBeEnabled()
                    ->treatFalseLike(['enabled' => false])
                    ->treatTrueLike(['enabled' => true])
                    ->treatNullLike(['enabled' => false])
                ->end()
                ->arrayNode('file')
                    ->canBeEnabled()
                    ->treatNullLike(['enabled' => false])
                    ->children()
                        ->scalarNode('directory')
                            ->defaultValue('%kernel.cache_dir%/spool')
                            ->info('Directory to store queued messages')
                        ->end()
                        ->scalarNode('mode')
                            ->defaultValue(0750)
                            ->info('Octal filesystem mode for file creation')
                            ->example(['0700', '0750', '0770'])
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->canBeEnabled()
                    ->treatNullLike(['enabled' => false])
                    ->children()
                        ->scalarNode('service')
                            ->defaultValue(ClientInterface::class)
                            ->info('Container service class name for the desired Predis client')
                        ->end()
                        ->scalarNode('key')
                            ->defaultValue('genkgo-mail')
                            ->info('Predis key to use')
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
