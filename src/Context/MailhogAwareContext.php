<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context;

use Behat\Behat\Context\Context;
use rpkamp\Mailhog\MailhogClient;

interface MailhogAwareContext extends Context
{
    public function setMailhog(MailhogClient $client): void;
}
