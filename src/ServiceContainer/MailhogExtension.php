<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use PsrDiscovery\Discover;
use rpkamp\Behat\MailhogExtension\Context\Initializer\MailhogAwareInitializer;
use rpkamp\Behat\MailhogExtension\Context\Initializer\OpenedEmailStorageContextInitializer;
use rpkamp\Behat\MailhogExtension\Listener\EmailPurgeListener;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;
use rpkamp\Mailhog\MailhogClient;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This class configures a lot of services, so needs access
 * to a lot of classes. Therefore high coupling is allowed here.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
final class MailhogExtension implements Extension
{
    public function getConfigKey(): string
    {
        return 'mailhog';
    }

    public function configure(ArrayNodeDefinition $builder): void
    {
        $builder
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('base_url')->isRequired()->end()
                ->scalarNode('purge_tag')->defaultValue('email')->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function load(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('mailhog.base_url', $config['base_url']);
        $container->setParameter('mailhog.purge_tag', $config['purge_tag']);

        $this->registerHttpClient($container);
        $this->registerHttpMessageFactory($container);
        $this->registerHttpStreamFactory($container);
        $this->registerMailhogClient($container);
        $this->registerContextInitializer($container);
        $this->registerPurgeListener($container);

        $this->registerOpenedEmailStorage($container);
        $this->registerOpenedEmailStorageContextInitializer($container);
    }

    private function registerHttpClient(ContainerBuilder $container): void
    {
        $httpClient = new Definition(ClientInterface::class);
        $httpClient->setFactory([Discover::class, 'httpClient']);

        $container->setDefinition('mailhog.http_client', $httpClient);
    }

    private function registerHttpMessageFactory(ContainerBuilder $container): void
    {
        $httpRequestFactory = new Definition(RequestFactoryInterface::class);
        $httpRequestFactory->setFactory([Discover::class, 'httpRequestFactory']);

        $container->setDefinition('mailhog.http_request_factory', $httpRequestFactory);
    }

    private function registerHttpStreamFactory(ContainerBuilder $container): void
    {
        $httpStreamFactory = new Definition(StreamFactoryInterface::class);
        $httpStreamFactory->setFactory([Discover::class, 'httpStreamFactory']);

        $container->setDefinition('mailhog.http_stream_factory', $httpStreamFactory);
    }

    private function registerMailhogClient(ContainerBuilder $container): void
    {
        $mailhogClient = new Definition(MailhogClient::class, [
            new Reference('mailhog.http_client'),
            new Reference('mailhog.http_request_factory'),
            new Reference('mailhog.http_stream_factory'),
            '%mailhog.base_url%',
        ]);
        $mailhogClient->setPublic(true);

        $container->setDefinition('mailhog.client', $mailhogClient);
    }

    private function registerContextInitializer(ContainerBuilder $container): void
    {
        $contextInitializer = new Definition(MailhogAwareInitializer::class, [
            new Reference('mailhog.client'),
        ]);

        $contextInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $container->setDefinition('mailhog.context_initializer', $contextInitializer);
    }

    private function registerOpenedEmailStorage(ContainerBuilder $container): void
    {
        $openedEmailStorage = new Definition(OpenedEmailStorage::class);

        $container->setDefinition('mailhog.opened_email_storage', $openedEmailStorage);
    }

    private function registerOpenedEmailStorageContextInitializer(ContainerBuilder $container): void
    {
        $openMailInitializer = new Definition(OpenedEmailStorageContextInitializer::class, [
            new Reference('mailhog.opened_email_storage'),
        ]);
        $openMailInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);

        $container->setDefinition('mailhog.opened_email_storage.context_initializer', $openMailInitializer);
    }

    private function registerPurgeListener(ContainerBuilder $container): void
    {
        $listener = new Definition(EmailPurgeListener::class, [
            new Reference('mailhog.client'),
            '%mailhog.purge_tag%',
        ]);
        $listener->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);

        $container->setDefinition('mailhog.purge_listener', $listener);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }
}
