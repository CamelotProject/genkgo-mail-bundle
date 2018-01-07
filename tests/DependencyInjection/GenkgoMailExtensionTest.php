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

use Camelot\GenkgoMailBundle\DependencyInjection\GenkgoMailExtension;
use Generator;
use Genkgo\Mail\Protocol;
use Genkgo\Mail\Queue;
use Genkgo\Mail\Transport;
use Genkgo\Mail\TransportInterface;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Yaml\Yaml;

class GenkgoMailExtensionTest extends AbstractExtensionTestCase
{
    public function testValidSubClass(): void
    {
        self::assertInstanceOf(Extension::class, new GenkgoMailExtension());
    }

    public function testAlias(): void
    {
        self::assertSame('genkgo_mail', (new GenkgoMailExtension())->getAlias());
    }

    /**
     * @dataProvider providerRegisteredServices
     */
    public function testHasServices(string $serviceId): void
    {
        $this->container->setParameter('kernel.project_dir', __DIR__ . '/../../');
        $this->container->setParameter('kernel.debug', true);
        $configs = Yaml::parseFile(__DIR__ . '/../Fixtures/App/config/packages/genkgo_mail.yaml');
        $this->load($configs['genkgo_mail']);
        $this->assertContainerBuilderHasService($serviceId);
    }

    public function providerRegisteredServices(): Generator
    {
        yield [Protocol\AppendCrlfConnection::class];
        yield [Protocol\AutomaticConnection::class];
        yield [Protocol\ConnectionListenerInterface::class];
        yield [Protocol\CryptoConstant::class];
        yield [Protocol\PlainTcpConnection::class];
        yield [Protocol\PlainTcpConnectionListener::class];
        yield [Protocol\SecureConnection::class];
        yield [Protocol\SecureConnectionOptions::class];
        yield [Protocol\TrimCrlfConnection::class];

        yield [Protocol\Smtp\AuthenticationInterface::class];
        yield [Protocol\Smtp\Authentication\ArrayAuthentication::class];
        yield [Protocol\Smtp\Backend\ArrayBackend::class];
        yield [Protocol\Smtp\Backend\ConsoleBackend::class];
        yield [Protocol\Smtp\Backend\DevNullBackend::class];
        yield [Protocol\Smtp\Backend\QueueBackend::class];
        yield [Protocol\Smtp\Backend\UnknownUserBackend::class];
        yield [Protocol\Smtp\Capability\AuthLoginCapability::class];
        yield [Protocol\Smtp\Capability\DataCapability::class];
        yield [Protocol\Smtp\Capability\EhloCapability::class];
        yield [Protocol\Smtp\Capability\MailFromCapability::class];
        yield [Protocol\Smtp\Capability\QuitCapability::class];
        yield [Protocol\Smtp\Capability\RcptToCapability::class];
        yield [Protocol\Smtp\Capability\ResetCapability::class];
        yield [Protocol\Smtp\Client::class];
        yield [Protocol\Smtp\ClientFactory::class];
        yield [Protocol\Smtp\GreyListInterface::class];
        yield [Protocol\Smtp\GreyList\ArrayGreyList::class];
        yield [Protocol\Smtp\Negotiation\AuthNegotiation::class];
        yield [Protocol\Smtp\Negotiation\ForceTlsUpgradeNegotiation::class];
        yield [Protocol\Smtp\Negotiation\ReceiveWelcomeNegotiation::class];
        yield [Protocol\Smtp\Negotiation\TryTlsUpgradeNegotiation::class];
        yield [Protocol\Smtp\NullConnection::class];
        yield [Protocol\Smtp\Reply::class];
        yield [Protocol\Smtp\Request\AuthLoginCommand::class];
        yield [Protocol\Smtp\Request\AuthLoginPasswordRequest::class];
        yield [Protocol\Smtp\Request\AuthLoginUsernameRequest::class];
        yield [Protocol\Smtp\Request\AuthPlainCommand::class];
        yield [Protocol\Smtp\Request\AuthPlainCredentialsRequest::class];
        yield [Protocol\Smtp\Request\DataCommand::class];
        yield [Protocol\Smtp\Request\DataRequest::class];
        yield [Protocol\Smtp\Request\EhloCommand::class];
        yield [Protocol\Smtp\Request\HeloCommand::class];
        yield [Protocol\Smtp\Request\MailFromCommand::class];
        yield [Protocol\Smtp\Request\NoopCommand::class];
        yield [Protocol\Smtp\Request\QuitCommand::class];
        yield [Protocol\Smtp\Request\RcptToCommand::class];
        yield [Protocol\Smtp\Request\RsetCommand::class];
        yield [Protocol\Smtp\Request\StartTlsCommand::class];
        yield [Protocol\Smtp\Response\EhloResponse::class];
        yield [Protocol\Smtp\Server::class];
        yield [Protocol\Smtp\Session::class];
        yield [Protocol\Smtp\SpamDecideScore::class];
        yield [Protocol\Smtp\SpamScore\AggregateSpamScore::class];
        yield [Protocol\Smtp\SpamScore\FixedSpamScore::class];
        yield [Protocol\Smtp\SpamScore\ForbiddenWordSpamScore::class];

        yield [Queue\QueueProcessor::class];
        yield [Queue\ArrayObjectQueue::class];
        yield [Queue\FilesystemQueue::class];
        yield [Queue\RedisQueue::class];

        yield [TransportInterface::class];
        yield [Transport\AggregateTransport::class];
        yield [Transport\ArrayObjectTransport::class];
        yield [Transport\DkimV1SignedTransport::class];
        yield [Transport\EnvelopeFactory::class];
        yield [Transport\FileTransport::class];
        yield [Transport\FileTransportOptions::class];
        yield [Transport\InjectDateHeaderTransport::class];
        yield [Transport\InjectMessageIdHeaderTransport::class];
        yield [Transport\InjectSenderHeaderTransport::class];
        yield [Transport\InjectStandardHeadersTransport::class];
        yield [Transport\NullTransport::class];
        yield [Transport\PhpMailTransport::class];
        yield [Transport\QueueIfFailedTransport::class];
        yield [Transport\RetryIfFailedTransport::class];
        yield [Transport\SmtpTransport::class];
    }

    /**
     * {@inheritdoc}
     */
    protected function getContainerExtensions()
    {
        return [
            new GenkgoMailExtension(),
        ];
    }
}
