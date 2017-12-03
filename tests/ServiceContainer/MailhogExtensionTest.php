<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\ServiceContainer;

use Http\Client\HttpClient;
use Http\Message\MessageFactory;
use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\ServiceContainer\MailhogExtension;
use rpkamp\Mailhog\MailhogClient;
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

    private function assertContainerHasServiceOfClass(string $className, string $serviceId)
    {
        $definition = $this->container->getDefinition($serviceId);
        $this->assertInstanceOf($className, $this->container->resolveServices($definition));
    }

    private function loadExtension(ContainerBuilder $container)
    {
        $extension = new MailhogExtension();
        $extension->load($container, ['base_url' => self::BASE_URL]);
    }
}
