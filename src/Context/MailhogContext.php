<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context;

use Exception;
use rpkamp\Mailhog\MailhogClient;
use rpkamp\Mailhog\Message\Contact;

final class MailhogContext implements MailhogAwareContext
{
    /**
     * @var MailhogClient
     */
    private $mailhogClient;

    public function setMailhog(MailhogClient $client): void
    {
        $this->mailhogClient = $client;
    }

    /**
     * @Given /^my inbox is empty$/
     */
    public function myInboxIsEmpty(): void
    {
        $this->mailhogClient->purgeMessages();
    }

    /**
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)"$/
     * @Then /^I should see an email with body "(?P<body>[^"]*)"$/
     * @Then /^I should see an email from "(?P<from>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" and body "(?P<body>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" and body "(?P<body>[^"]*)" from "(?P<from>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" from "(?P<from>[^"]*)"$/
     *
     * @Then /^I should see an email to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email with body "(?P<body>[^"]*)" to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email from "(?P<from>[^"]*)" to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" and body "(?P<body>[^"]*)" to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" and body "(?P<body>[^"]*)" from "(?P<from>[^"]*)" to "(?P<recipient>[^"]*)"$/
     * @Then /^I should see an email with subject "(?P<subject>[^"]*)" from "(?P<from>[^"]*)" to "(?P<recipient>[^"]*)"$/
     */
    public function iShouldSeeAnEmailWithSubjectAndBodyFromToRecipient(
        string $subject = null,
        string $body = null,
        string $from = null,
        string $recipient = null
    ): void {
        $message = $this->mailhogClient->getLastMessage();

        if (!empty($subject) && $subject !== $message->subject) {
            throw new Exception(sprintf('Expected subject "%s", found "%s"', $subject, $message->subject));
        }

        if (!empty($body) && $body !== $message->body) {
            throw new Exception('Unexpected body value');
        }

        if (!empty($from) && $from !== $message->sender->emailAddress && $from !== $message->sender->name) {
            throw new Exception(sprintf('Could not find expected message from "%s"', $from));
        }

        if (!empty($recipient) && false === $message->recipients->contains(Contact::fromString($recipient))) {
            throw new Exception(sprintf('Could not find expected message to "%s"', $recipient));
        }
    }

    /**
     * @Given /^I should see "([^"]*)" in email$/
     */
    public function iShouldSeeInEmail(string $text): void
    {
        $message = $this->mailhogClient->getLastMessage();

        if (false === strpos($message->body, $text)) {
            throw new Exception(sprintf('Could not find "%s" in email', $text));
        }
    }

    /**
     * @Then /^there should be (\d+) email(?:s)? in my inbox$/
     */
    public function thereShouldBeEmailInMyInbox(int $numEmails): void
    {
        $numMailhogMessages = $this->mailhogClient->getNumberOfMessages();

        if ($numMailhogMessages !== $numEmails) {
            throw new Exception(
                sprintf(
                    'Expected %d messages in inbox, but there were %d',
                    $numEmails,
                    $numMailhogMessages
                )
            );
        }
    }

    /**
     * @Then /^I should see an email with attachment "([^"]*)"$/
     */
    public function iShouldSeeAnEmailWithAttachment(string $filename): void
    {
        $message = $this->mailhogClient->getLastMessage();

        $found = false;
        foreach ($message->attachments as $attachment) {
            if ($filename === $attachment->filename) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new Exception(sprintf('Messages does not contain a message with attachment "%s"', $filename));
        }
    }
}
