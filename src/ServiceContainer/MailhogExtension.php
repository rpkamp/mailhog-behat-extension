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
use rpkamp\Behat\MailhogExtension\Listener\EmailPurgeListener;
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
    public function getConfigKey()
    {
        return 'mailhog';
    }

    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('base_url')->isRequired()->end()
            ->end();
    }

    public function load(ContainerBuilder $container, array $config)
    {
        $container->setParameter('mailhog.base_url', $config['base_url']);

        $this->registerHttpClient($container);
        $this->registerHttpMessageFactory($container);
        $this->registerMailhogClient($container);
        $this->registerContextInitializer($container);
        $this->registerPurgeListener($container);
    }

    public function registerHttpClient(ContainerBuilder $container): void
    {
        $httpClient = new Definition(HttpClient::class);
        $httpClient->setFactory([HttpClientDiscovery::class, 'find']);
        $httpClient->setPrivate(true);

        $container->setDefinition('mailhog.http_client', $httpClient);
    }

    public function registerHttpMessageFactory(ContainerBuilder $container): void
    {
        $httpMessageFactory = new Definition(MessageFactory::class);
        $httpMessageFactory->setFactory([MessageFactoryDiscovery::class, 'find']);
        $httpMessageFactory->setPrivate(true);

        $container->setDefinition('mailhog.http_message_factory', $httpMessageFactory);
    }

    public function registerMailhogClient(ContainerBuilder $container): void
    {
        $mailhogClient = new Definition(MailhogClient::class, [
            new Reference('mailhog.http_client'),
            new Reference('mailhog.http_message_factory'),
            '%mailhog.base_url%'
        ]);

        $container->setDefinition('mailhog.client', $mailhogClient);
    }

    public function registerContextInitializer(ContainerBuilder $container): void
    {
        $contextInitializer = new Definition(MailhogAwareInitializer::class, [
            new Reference('mailhog.client'),
        ]);

        $contextInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $contextInitializer->setPrivate(true);
        $container->setDefinition('mailhog.context_initializer', $contextInitializer);
    }

    public function registerPurgeListener(ContainerBuilder $container): void
    {
        $listener = new Definition(EmailPurgeListener::class, [new Reference('mailhog.client')]);
        $listener->addTag(EventDispatcherExtension::SUBSCRIBER_TAG, ['priority' => 0]);
        $listener->setPrivate(true);

        $container->setDefinition('mailhog.purge_listener', $listener);
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }
}
