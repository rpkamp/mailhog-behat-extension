<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context;

use Behat\Behat\Context\Context;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;

interface OpenedEmailStorageAwareContext extends Context
{
    public function setOpenedEmailStorage(OpenedEmailStorage $storage): void;
}
