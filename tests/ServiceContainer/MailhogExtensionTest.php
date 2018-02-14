<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\EventDispatcher\ServiceContainer\EventDispatcherExtension;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\Context\Initializer\MailhogAwareInitializer;
use rpkamp\Behat\MailhogExtension\Listener\EmailPurgeListener;
use rpkamp\Behat\MailhogExtension\ServiceContainer\MailhogExtension;
use rpkamp\Mailhog\MailhogClient;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class MailhogExtensionTest extends TestCase
{
    public const BASE_URL = 'http://localhost:10025/';

    /**
     * @var ContainerBuilder
     */
    private $container;

    public function setUp()
    {
        $this->container = new ContainerBuilder();
    }

    /**
     * @test
     */
    public function it_should_set_the_base_url_as_a_container_parameter()
    {
        $this->loadExtension($this->container);

        $this->assertEquals(self::BASE_URL, $this->container->getParameter('mailhog.base_url'));
    }

    /**
     * @test
     */
    public function it_should_set_a_http_client_instance_in_the_container()
    {
        $this->loadExtension($this->container);

        $this->assertContainerHasServiceOfClass(HttpClient::class, 'mailhog.http_client');
    }

    /**
     * @test
     */
    public function it_should_set_a_http_message_factory_in_the_container()
    {
        $this->loadExtension($this->container);

        $this->assertContainerHasServiceOfClass(MessageFactory::class, 'mailhog.http_message_factory');
    }

    /**
     * @test
     */
    public function it_should_set_a_mailhog_client_instance_in_the_container()
    {
        $this->loadExtension($this->container);

        $this->assertContainerHasServiceOfClass(MailhogClient::class, 'mailhog.client');
    }

    /**
     * @test
     */
    public function it_should_set_initializer_with_correct_tag()
    {
        $this->loadExtension($this->container);

        $this->assertContainerHasServiceOfClass(MailhogAwareInitializer::class, 'mailhog.context_initializer');

        $definition = $this->container->getDefinition('mailhog.context_initializer');
        $this->assertEquals([['priority' => 0]], $definition->getTag(ContextExtension::INITIALIZER_TAG));
    }

    /**
     * @test
     */
    public function it_should_set_and_register_purge_listener()
    {
        $this->loadExtension($this->container);

        $this->assertContainerHasServiceOfClass(EmailPurgeListener::class, 'mailhog.purge_listener');

        $definition = $this->container->getDefinition('mailhog.purge_listener');
        $this->assertEquals([['priority' => 0]], $definition->getTag(EventDispatcherExtension::SUBSCRIBER_TAG));
    }

    /**
     * @test
     */
    public function it_should_throw_exception_when_no_base_url_supplied()
    {
        $node = new ArrayNodeDefinition(null);
        (new MailhogExtension())->configure($node);

        $this->expectException(InvalidConfigurationException::class);
        (new Processor())->process($node->getNode(), [[]]);
    }

    /**
     * @test
     */
    public function it_should_set_default_email_purge_tag_if_none_supplied()
    {
        $node = new ArrayNodeDefinition(null);
        (new MailhogExtension())->configure($node);

        $configuration = (new Processor())->process($node->getNode(), [['base_url' => self::BASE_URL]]);
        $this->assertEquals('email', $configuration['purge_tag']);
    }

    private function assertContainerHasServiceOfClass(string $className, string $serviceId)
    {
        $definition = $this->container->getDefinition($serviceId);
        $this->assertInstanceOf($className, $this->container->resolveServices($definition));
    }

    private function loadExtension(ContainerBuilder $container)
    {
        $extension = new MailhogExtension();
        $extension->load($container, ['base_url' => self::BASE_URL, 'purge_tag' => 'email']);
    }
}
