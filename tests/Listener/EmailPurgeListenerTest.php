<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\Listener;

use Behat\Behat\EventDispatcher\Event\BeforeScenarioTested;
use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Testwork\Environment\StaticEnvironment;
use Behat\Testwork\Suite\GenericSuite;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use rpkamp\Behat\MailhogExtension\Listener\EmailPurgeListener;
use rpkamp\Mailhog\MailhogClient;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class EmailPurgeListenerTest extends MockeryTestCase
{
    /**
     * @var MockInterface|MailhogClient
     */
    private $client;

    /**
     * @var EmailPurgeListener
     */
    private $listener;

    /**
     * @var EventDispatcher
     */
    private $dispatcher;

    public function setUp()
    {
        $this->client = Mockery::spy(MailhogClient::class);
        $this->listener = new EmailPurgeListener($this->client, 'email');

        $this->dispatcher = new EventDispatcher();
        $this->dispatcher->addSubscriber($this->listener);
    }

    /**
     * @test
     */
    public function it_should_purge_all_messages_before_each_scenario_in_feature_with_email_tag()
    {
        $scenario = new ScenarioNode('test', [], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', ['email'], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ScenarioTested::BEFORE, $event);

        $this->client->shouldHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_purge_all_messages_before_each_scenario_in_with_email_tag()
    {
        $scenario = new ScenarioNode('test', ['email'], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', [], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ScenarioTested::BEFORE, $event);

        $this->client->shouldHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_purge_all_messages_before_each_example_in_feature_with_email_tag()
    {
        $scenario = new ScenarioNode('test', [], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', ['email'], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ExampleTested::BEFORE, $event);

        $this->client->shouldHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_purge_all_messages_before_each_example_with_email_tag()
    {
        $scenario = new ScenarioNode('test', ['email'], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', [], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ExampleTested::BEFORE, $event);

        $this->client->shouldHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_purge_messages_only_once_on_multiple_email_tags()
    {
        $scenario = new ScenarioNode('test', ['email', 'email'], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', ['email', 'email'], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ExampleTested::BEFORE, $event);

        $this->client->shouldHaveReceived('purgeMessages')->once();
    }

    /**
     * @test
     */
    public function it_should_not_purge_all_messages_before_each_scenario_without_email_tag()
    {
        $scenario = new ScenarioNode('test', [], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', [], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ScenarioTested::BEFORE, $event);

        $this->client->shouldNotHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_not_purge_all_messages_before_each_example_without_email_tag()
    {
        $scenario = new ScenarioNode('test', [], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', [], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $this->dispatcher->dispatch(ExampleTested::BEFORE, $event);

        $this->client->shouldNotHaveReceived('purgeMessages');
    }

    /**
     * @test
     */
    public function it_should_use_custom_tag_to_purge_emails()
    {
        /** @var MockInterface|MailhogClient $client */
        $client = Mockery::spy(MailhogClient::class);
        $listener = new EmailPurgeListener($client, 'foobarbazban');

        $dispatcher = new EventDispatcher();
        $dispatcher->addSubscriber($listener);

        $scenario = new ScenarioNode('test', [], [], 'test', 1);
        $event = new BeforeScenarioTested(
            new StaticEnvironment(new GenericSuite('generic', [])),
            new FeatureNode('test', 'test', ['foobarbazban'], null, [$scenario], 'test', 'en_GB', null, 1),
            $scenario
        );

        $dispatcher->dispatch(ScenarioTested::BEFORE, $event);

        $client->shouldHaveReceived('purgeMessages');
    }
}
