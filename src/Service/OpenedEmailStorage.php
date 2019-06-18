<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Service;

use rpkamp\Mailhog\Message\Message;
use RuntimeException;

final class OpenedEmailStorage
{
    /**
     * @var ?Message
     */
    private $openedEmail;

    public function setOpenedEmail(Message $message): void
    {
        $this->openedEmail = $message;
    }

    public function hasOpenedEmail(): bool
    {
        return null !== $this->openedEmail;
    }

    public function getOpenedEmail(): Message
    {
        if (null === $this->openedEmail) {
            throw new RuntimeException('No e-mail message opened!');
        }

        return $this->openedEmail;
    }
}
