<?php

namespace rpkamp\Behat\MailhogExtension\Tests\Service;

use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;
use rpkamp\Mailhog\Message\Contact;
use rpkamp\Mailhog\Message\ContactCollection;
use rpkamp\Mailhog\Message\Message;
use RuntimeException;

class OpenedEmailStorageTest extends TestCase
{
    /**
     * @test
     */
    public function it_should_indicate_when_no_opened_email_has_been_set(): void
    {
        $service = new OpenedEmailStorage();

        $this->assertFalse($service->hasOpenedEmail());
    }

    /**
     * @test
     */
    public function it_should_indicate_when_an_opened_email_has_been_set(): void
    {
        $service = new OpenedEmailStorage();
        $service->setOpenedEmail($this->getMessage());

        $this->assertTrue($service->hasOpenedEmail());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_when_asked_for_opened_email_but_none_was_set(): void
    {
        $service = new OpenedEmailStorage();

        $this->expectException(RuntimeException::class);
        $service->getOpenedEmail();
    }

    /**
     * @test
     */
    public function it_should_return_the_set_opened_email(): void
    {
        $service = new OpenedEmailStorage();
        $message = $this->getMessage();

        $service->setOpenedEmail($message);
        $this->assertEquals($message, $service->getOpenedEmail());
    }

    /**
     * @return Message
     */
    public function getMessage(): Message
    {
        return new Message(
            '1234',
            new Contact('me@myself.example'),
            new ContactCollection([new Contact('me@myself.example')]),
            new ContactCollection([]),
            new ContactCollection([]),
            'Test e-mail',
            'Hello there!',
            []
        );
    }
}
