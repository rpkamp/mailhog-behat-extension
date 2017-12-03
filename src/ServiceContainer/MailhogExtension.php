<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use rpkamp\Behat\MailhogExtension\Context\Initializer\MailhogAwareInitializer;
use rpkamp\Mailhog\MailhogClient;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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

        $httpClient = new Definition(HttpClient::class);
        $httpClient->setFactory([HttpClientDiscovery::class, 'find']);
        $httpClient->setPrivate(true);

        $container->setDefinition('mailhog.http_client', $httpClient);

        $httpMessageFactory = new Definition(MessageFactory::class);
        $httpMessageFactory->setFactory([MessageFactoryDiscovery::class, 'find']);
        $httpMessageFactory->setPrivate(true);

        $container->setDefinition('mailhog.http_message_factory', $httpMessageFactory);

        $mailhogClient = new Definition(MailhogClient::class, [
            new Reference('mailhog.http_client'),
            new Reference('mailhog.http_message_factory'),
            '%mailhog.base_url%'
        ]);

        $container->setDefinition('mailhog.client', $mailhogClient);

        $contextInitializer = new Definition(MailhogAwareInitializer::class, [
            new Reference('mailhog.client'),
        ]);

        $contextInitializer->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);
        $contextInitializer->setPrivate(true);
        $container->setDefinition('mailhog.context_initializer', $contextInitializer);
    }

    public function process(ContainerBuilder $container)
    {
    }

    public function initialize(ExtensionManager $extensionManager)
    {
    }
}
