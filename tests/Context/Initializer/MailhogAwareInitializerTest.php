<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Tests\Context\Initializer;

use Behat\Behat\Context\Context;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\Context\Initializer\MailhogAwareInitializer;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class MailhogAwareInitializerTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_inject_mailhog_client_in_a_mailhog_aware_context(): void
    {
        $context = new class implements Context, MailhogAwareContext {
            /** @var MailhogClient $mailhogClient */
            public $mailhogClient;
            public function setMailhog(MailhogClient $client): void
            {
                $this->mailhogClient = $client;
            }
        };

        /** @var MockInterface|MailhogClient $mailhogClient */
        $mailhogClient = Mockery::mock(MailhogClient::class);

        $initializer = new MailhogAwareInitializer($mailhogClient);
        $initializer->initializeContext($context);

        $this->assertSame($mailhogClient, $context->mailhogClient);
    }

    /**
     * @test
     */
    public function it_should_ignore_non_mailhog_aware_contexts(): void
    {
        $context = new class implements Context {
            /** @var MailhogClient $mailhogClient */
            public $mailhogClient;
            public function setMailhog(MailhogClient $client): void
            {
                $this->mailhogClient = $client;
            }
        };

        /** @var MockInterface|MailhogClient $mailhogClient */
        $mailhogClient = Mockery::mock(MailhogClient::class);

        $initializer = new MailhogAwareInitializer($mailhogClient);
        $initializer->initializeContext($context);

        $this->assertNull($context->mailhogClient);
    }
}
