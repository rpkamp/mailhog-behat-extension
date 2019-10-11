<?php
declare(strict_types=1);

namespace rpkamp\Behat\MailhogExtension\Context;

use Exception;
use rpkamp\Behat\MailhogExtension\Service\OpenedEmailStorage;
use rpkamp\Mailhog\MailhogClient;
use rpkamp\Mailhog\Message\Contact;
use rpkamp\Mailhog\Specification\AndSpecification;
use rpkamp\Mailhog\Specification\AttachmentSpecification;
use rpkamp\Mailhog\Specification\BodySpecification;
use rpkamp\Mailhog\Specification\RecipientSpecification;
use rpkamp\Mailhog\Specification\SenderSpecification;
use rpkamp\Mailhog\Specification\SubjectSpecification;
use RuntimeException;

final class MailhogContext implements MailhogAwareContext, OpenedEmailStorageAwareContext
{
    /**
     * @var MailhogClient
     */
    private $mailhogClient;

    /**
     * @var OpenedEmailStorage
     */
    private $openedEmailStorage;

    public function setMailhog(MailhogClient $client): void
    {
        $this->mailhogClient = $client;
    }

    public function setOpenedEmailStorage(OpenedEmailStorage $storage)
    {
        $this->openedEmailStorage = $storage;
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
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function iShouldSeeAnEmailWithSubjectAndBodyFromToRecipient(
        string $subject = null,
        string $body = null,
        string $from = null,
        string $recipient = null
    ): void {
        $specifications = [];

        if (!empty($subject)) {
            $specifications[] = new SubjectSpecification($subject);
        }

        if (!empty($body)) {
            $specifications[] = new BodySpecification($body);
        }

        if (!empty($from)) {
            $specifications[] = new SenderSpecification(Contact::fromString($from));
        }

        if (!empty($recipient)) {
            $specifications[] = new RecipientSpecification(Contact::fromString($recipient));
        }

        $messages = $this->mailhogClient->findMessagesSatisfying(AndSpecification::all(...$specifications));

        if (count($messages) > 0) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'No message found%s%s%s%s',
                !empty($from) ? sprintf(' from "%s"', $from) : '',
                !empty($recipient) ? sprintf(' to "%s"', $recipient) : '',
                !empty($subject) ? sprintf(' with subject "%s"', $subject) : '',
                !empty($body) ? sprintf(' with body "%s"', $body) : ''
            )
        );
    }

    /**
     * @When /^I open the latest email from "(?P<from>[^"]*)"$/
     * @When /^I open the latest email to "(?P<recipient>[^"]*)"$/
     * @When /^I open the latest email with subject "(?P<subject>[^"]*)"$/
     *
     * @When /^I open the latest email from "(?P<from>[^"]*)" with subject "(?P<subject>[^"]*)"$/
     * @When /^I open the latest email to "(?P<recipient>[^"]*)" with subject "(?P<subject>[^"]*)"$/
     *
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function iOpenTheEmail(string $from = null, string $recipient = null, string $subject = null): void
    {
        $specifications = [];

        if (!empty($from)) {
            $specifications[] = new SenderSpecification(Contact::fromString($from));
        }

        if (!empty($recipient)) {
            $specifications[] = new RecipientSpecification(Contact::fromString($recipient));
        }

        if (!empty($subject)) {
            $specifications[] = new SubjectSpecification($subject);
        }

        $messages = $this->mailhogClient->findMessagesSatisfying(AndSpecification::all(...$specifications));

        if (count($messages) === 0) {
            throw new RuntimeException(
                sprintf(
                    'No message found%s%s%s',
                    !empty($from) ? sprintf(' from "%s"', $from) : '',
                    !empty($recipient) ? sprintf(' to "%s"', $recipient) : '',
                    !empty($subject) ? sprintf(' with subject "%s"', $subject) : ''
                )
            );
        }

        $this->openedEmailStorage->setOpenedEmail($messages[0]);
    }

    /**
     * @Then /^I should see "(?P<text>[^"]*)" in the opened email$/
     */
    public function iShouldSeeInTheOpenedEmail(string $text): void
    {
        if (!$this->openedEmailStorage->hasOpenedEmail()) {
            throw new RuntimeException('Unable to look for text in opened email - no email was opened yet');
        }

        $specification = new BodySpecification($text);

        if (!$specification->isSatisfiedBy($this->openedEmailStorage->getOpenedEmail())) {
            throw new Exception(sprintf('Could not find "%s" in email', $text));
        }
    }

    /**
     * @Then /^I should see an attachment with filename "(?P<filename>[^"]*)" in the opened email$/
     */
    public function iShouldAttachmentInOpenedEmail(string $filename): void
    {
        if (!$this->openedEmailStorage->hasOpenedEmail()) {
            throw new RuntimeException('Unable to look for text in opened email - no email was opened yet');
        }

        $specification = new AttachmentSpecification($filename);

        if (!$specification->isSatisfiedBy($this->openedEmailStorage->getOpenedEmail())) {
            throw new RuntimeException(
                sprintf('Opened email does not contain an attachment with filename "%s"', $filename)
            );
        }
    }

    /**
     * @Given /^I should see "([^"]*)" in email$/
     */
    public function iShouldSeeInEmail(string $text): void
    {
        $specification = new BodySpecification($text);

        $messages = $this->mailhogClient->findMessagesSatisfying($specification);

        if (count($messages) === 0) {
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
        $specification = new AttachmentSpecification($filename);

        $messages = $this->mailhogClient->findMessagesSatisfying($specification);

        if (count($messages) === 0) {
            throw new Exception(sprintf('Messages does not contain a message with attachment "%s"', $filename));
        }
    }
}
