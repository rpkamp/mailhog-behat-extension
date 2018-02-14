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

    /**
     * The tag name for scenarios/features that trigger
     * a mailhog purge. Defaults to 'email'.
     *
     * @var string
     */
    private $purgeTag;

    public function __construct(MailhogClient $client, string $purgeTag)
    {
        $this->client = $client;
        $this->purgeTag = $purgeTag;
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
            if ($this->purgeTag === $tag) {
                $this->client->purgeMessages();
                return;
            }
        }
    }
}
