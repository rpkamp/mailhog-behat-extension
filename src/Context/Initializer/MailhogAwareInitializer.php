<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class MailhogAwareInitializer implements ContextInitializer
{
    /**
     * @var MailhogClient
     */
    private $client;

    public function __construct(MailhogClient $client)
    {
        $this->client = $client;
    }

    /**
     * Initializes provided context.
     *
     * @param Context $context
     */
    public function initializeContext(Context $context)
    {
        if (!$context instanceof MailhogAwareContext) {
            return;
        }

        $context->setMailhog($this->client);
    }
}
