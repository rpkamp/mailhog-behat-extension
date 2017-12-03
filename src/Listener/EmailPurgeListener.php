<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Listener;

use Behat\Behat\EventDispatcher\Event\ExampleTested;
use Behat\Behat\EventDispatcher\Event\ScenarioLikeTested;
use Behat\Behat\EventDispatcher\Event\ScenarioTested;
use rpkamp\Mailhog\MailhogClient;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class EmailPurgeListener implements EventSubscriberInterface
{
    /**
     * @var MailhogClient
     */
    private $client;

    public function __construct(MailhogClient $client)
    {
        $this->client = $client;
    }

    public static function getSubscribedEvents()
    {
        return [
            ScenarioTested::BEFORE => ['purgeEmails', 10],
            ExampleTested::BEFORE => ['purgeEmails', 10],
        ];
    }

    public function purgeEmails(ScenarioLikeTested $event)
    {
        $scenario = $event->getScenario();
        $feature  = $event->getFeature();

        foreach (array_merge($feature->getTags(), $scenario->getTags()) as $tag) {
            if ('email' === $tag) {
                $this->client->purgeMessages();
                return;
            }
        }
    }
}
