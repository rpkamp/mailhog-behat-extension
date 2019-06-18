<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
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

    public function load(ContainerBuilder $container, array $config): void
    {
        $container->setParameter('mailhog.base_url', $config['base_url']);
        $container->setParameter('mailhog.purge_tag', $config['purge_tag']);

        $this->registerHttpClient($container);
        $this->registerHttpMessageFactory($container);
        $this->registerMailhogClient($container);
        $this->registerContextInitializer($container);
        $this->registerPurgeListener($container);

        $this->registerOpenedEmailStorage($container);
        $this->registerOpenedEmailStorageContextInitializer($container);
    }

    private function registerHttpClient(ContainerBuilder $container): void
    {
        $httpClient = new Definition(HttpClient::class);
        $httpClient->setFactory([HttpClientDiscovery::class, 'find']);
        $this->markServicePrivate($httpClient);

        $container->setDefinition('mailhog.http_client', $httpClient);
    }

    private function registerHttpMessageFactory(ContainerBuilder $container): void
    {
        $httpMessageFactory = new Definition(MessageFactory::class);
        $httpMessageFactory->setFactory([MessageFactoryDiscovery::class, 'find']);
        $this->markServicePrivate($httpMessageFactory);

        $container->setDefinition('mailhog.http_message_factory', $httpMessageFactory);
    }

    private function registerMailhogClient(ContainerBuilder $container): void
    {
        $mailhogClient = new Definition(MailhogClient::class, [
            new Reference('mailhog.http_client'),
            new Reference('mailhog.http_message_factory'),
            '%mailhog.base_url%'
        ]);
        $this->markServicePublic($mailhogClient);

        $container->setDefinition('mailhog.client', $mailhogClient);
    }

    private function registerContextInitializer(ContainerBuilder $container): void
    {
        $contextInitializer = new Definition(MailhogAwareInitializer::class, [
            new Reference('mailhog.client'),
        ]);

        $contextInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $this->markServicePrivate($contextInitializer);
        $container->setDefinition('mailhog.context_initializer', $contextInitializer);
    }

    private function registerOpenedEmailStorage(ContainerBuilder $container): void
    {
        $openedEmailStorage = new Definition(OpenedEmailStorage::class);
        $this->markServicePrivate($openedEmailStorage);

        $container->setDefinition('mailhog.opened_email_storage', $openedEmailStorage);
    }

    private function registerOpenedEmailStorageContextInitializer(ContainerBuilder $container): void
    {
        $openMailInitializer = new Definition(OpenedEmailStorageContextInitializer::class, [
            new Reference('mailhog.opened_email_storage')
        ]);
        $openMailInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $this->markServicePrivate($openMailInitializer);

        $container->setDefinition('mailhog.opened_email_storage.context_initializer', $openMailInitializer);
    }

    private function registerPurgeListener(ContainerBuilder $container): void
    {
        $listener = new Definition(EmailPurgeListener::class, [
            new Reference('mailhog.client'),
            '%mailhog.purge_tag%'
        ]);
        $listener->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);
        $this->markServicePrivate($listener);

        $container->setDefinition('mailhog.purge_listener', $listener);
    }

    private function markServicePrivate(Definition $definition): void
    {
        if (!method_exists($definition, 'setPrivate')) {
            // symfony/dependency-injection >= 3.4 where
            // all services are private by default
            return;
        }

        $definition->setPrivate(true);
    }

    public function markServicePublic(Definition $definition): void
    {
        if (!method_exists($definition, 'setPublic')) {
            // symfony/dependency-injection < 3.4 where
            // all services are public by default
            return;
        }

        $definition->setPublic(true);
    }

    public function process(ContainerBuilder $container): void
    {
    }

    public function initialize(ExtensionManager $extensionManager): void
    {
    }
}
