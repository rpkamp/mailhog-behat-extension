<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\TestCase;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class FeatureContext implements Context, MailhogAwareContext
{
    /**
     * @var MailhogClient
     */
    private $mailHog;

    public function setMailhog(MailhogClient $client)
    {
        $this->mailHog = $client;
    }

    /**
     * @Given /^I send an email with subject "([^"]*)" and body "([^"]*)"$/
     */
    public function iSendAnEmailWithSubjectAndBody(string $subject, string $body)
    {
        $message = (new Swift_Message())
            ->setFrom('me@myself.example')
            ->setTo('me@myself.example')
            ->setBody($body)
            ->setSubject($subject);

        $mailer = new Swift_Mailer(new Swift_SmtpTransport('localhost', 3025));

        $mailer->send($message);
    }

    /**
     * @Then /^I should receive an email with subject "([^"]*)" and body "([^"]*)"$/
     */
    public function iShouldReceiveAnEmailWithSubjectAndBody(string $subject, string $body)
    {
        $message = $this->mailHog->getLastMessage();

        TestCase::assertEquals($subject, $message->subject);
        TestCase::assertEquals($body, $message->body);
    }

    /**
     * @Then /^there should be (\d+) email in my inbox$/
     */
    public function thereShouldBeEmailInMyInbox(int $numEmails)
    {
        TestCase::assertEquals($numEmails, $this->mailHog->getNumberOfMessages());
    }
}
