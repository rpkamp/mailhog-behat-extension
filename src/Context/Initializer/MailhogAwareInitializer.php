<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class MailhogAwareInitializer implements ContextInitializer
{
    private MailhogClient $client;

    public function __construct(MailhogClient $client)
    {
        $this->client = $client;
    }

    public function initializeContext(Context $context): void
    {
        if (!$context instanceof MailhogAwareContext) {
            return;
        }

        $context->setMailhog($this->client);
    }
}
