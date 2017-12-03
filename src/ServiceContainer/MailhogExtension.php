<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\ServiceContainer;

use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MailhogExtension implements Extension
{
    public function process(ContainerBuilder $container)
    {
        // TODO: Implement process() method.
    }

    public function getConfigKey()
    {
        return 'mailhog';
    }

    public function initialize(ExtensionManager $extensionManager)
    {
        // TODO: Implement initialize() method.
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
        // TODO: Implement load() method.
    }
}
