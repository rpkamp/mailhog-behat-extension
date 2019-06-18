<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use rpkamp\Behat\MailhogExtension\Context\OpenedEmailStorageAwareContext;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;

final class OpenedEmailStorageContextInitializer implements ContextInitializer
{
    /**
     * @var OpenedEmailStorage
     */
    private $openedEmailStorage;

    public function __construct(OpenedEmailStorage $openedEmailStorage)
    {
        $this->openedEmailStorage = $openedEmailStorage;
    }

    public function initializeContext(Context $context): void
    {
        if (!$context instanceof OpenedEmailStorageAwareContext) {
            return;
        }

        $context->setOpenedEmailStorage($this->openedEmailStorage);
    }
}
