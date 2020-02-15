<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\Listener;

use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface as ContractsEventDispatcherInterface;

/**
 * BC layer between the old Symfony dispatcher and the
 * new Symfony dispatcher
 */
final class EventDispatcher
{
    /**
     * @var EventDispatcherInterface|ContractsEventDispatcherInterface
     */
    private $dispatcher;

    public function __construct($dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function addSubscriber(object $subscriber): void
    {
        $this->dispatcher->addSubscriber($subscriber);
    }

    public function dispatch($event, string $name): void
    {
        // New version
        if ($this->dispatcher instanceof ContractsEventDispatcherInterface) {
            $this->dispatcher->dispatch($event, $name);

            return;
        }

        // Old version
        $this->dispatcher->dispatch($name, $event);
    }
}
