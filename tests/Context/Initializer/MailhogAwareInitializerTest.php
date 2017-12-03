<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\Context\Initializer;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\Context\Initializer\MailhogAwareInitializer;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class MailhogAwareInitializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_inject_mailhog_client_in_a_mailhog_aware_context()
    {
        $context = new class implements Context, MailhogAwareContext {
            public $mailhogClient;
            public function setMailhog(MailhogClient $client)
            {
                $this->mailhogClient = $client;
            }
        };

        /** @var MockObject|MailhogClient $mailhogClient */
        $mailhogClient = $this->createMock(MailhogClient::class);

        $initializer = new MailhogAwareInitializer($mailhogClient);
        $initializer->initializeContext($context);

        $this->assertSame($mailhogClient, $context->mailhogClient);
    }

    /**
     * @test
     */
    public function it_should_ignore_non_mailhog_aware_contexts()
    {
        $context = new class implements Context {
            public $mailhogClient;
            public function setMailhog(MailhogClient $client)
            {
                $this->mailhogClient = $client;
            }
        };

        /** @var MockObject|MailhogClient $mailhogClient */
        $mailhogClient = $this->createMock(MailhogClient::class);

        $initializer = new MailhogAwareInitializer($mailhogClient);
        $initializer->initializeContext($context);

        $this->assertNull($context->mailhogClient);
    }
}
