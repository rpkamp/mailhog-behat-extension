<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use rpkamp\Behat\MailhogExtension\Context\MailhogAwareContext;
use rpkamp\Mailhog\MailhogClient;

final class FeatureContext implements Context, MailhogAwareContext
{
    /**
     * @var MailhogClient
     */
    private $mailHog;

    public function setMailhog(MailhogClient $client): void
    {
        $this->mailHog = $client;
    }

    /**
     * @Given /^I send an email with subject "([^"]*)" and body "([^"]*)"$/
     */
    public function iSendAnEmailWithSubjectAndBody(string $subject, string $body): void
    {
        $message = (new Swift_Message())
            ->setFrom('me@myself.example', 'Myself')
            ->setTo('me@myself.example')
            ->setBody($body)
            ->setSubject($subject);

        $mailer = new Swift_Mailer(new Swift_SmtpTransport('localhost', 3025));

        $mailer->send($message);
    }

    /**
     * @Given /^I send an email with attachment "([^"]*)"$/
     */
    public function iSendAnEmailWithAttachment(string $filename): void
    {
        $message = (new Swift_Message())
            ->setFrom('me@myself.example', 'Myself')
            ->setTo('me@myself.example')
            ->setBody('Please see attached')
            ->setSubject('Email with attachment')
            ->attach(new Swift_Attachment(
                'Hello world!',
                $filename,
                'text/plain'
            ));

        $mailer = new Swift_Mailer(new Swift_SmtpTransport('localhost', 3025));

        $mailer->send($message);
    }
}
