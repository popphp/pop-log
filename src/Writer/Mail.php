<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Writer;

use Pop\Mail\Mailer;
use Pop\Mail\Message;
use Pop\Mail\Queue;

/**
 * Mail log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2026 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.4
 */
class Mail extends AbstractWriter
{

    /**
     * Mailer object
     * @var ?Mailer
     */
    protected ?Mailer $mailer = null;

    /**
     * Emails to which to send the log messages
     * @var array
     */
    protected array $emails = [];

    /**
     * Array of mail-specific options, i.e. subject, headers, etc.
     * @var array
     */
    protected array $options = [];

    /**
     * Constructor
     *
     * Instantiate the Mail writer object
     *
     * @param  Mailer $mailer
     * @param  mixed  $emails
     * @param  array  $options
     */
    public function __construct(Mailer $mailer, mixed $emails, array $options = [])
    {
        $this->mailer = $mailer;
        $this->setEmails($emails);
        $this->setOptions($options);
    }

    /**
     * Get mailer
     * @return Mailer
     */
    public function getMailer(): Mailer
    {
        return $this->mailer;
    }

    /**
     * Get emails
     * @return array
     */
    public function getEmails(): array
    {
        return $this->emails;
    }

    /**
     * Get options
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set emails
     *
     * @param  mixed $emails
     * @return Mail
     */
    public function setEmails(mixed $emails): Mail
    {
        $this->emails = [];
        $this->addEmails($emails);
        return $this;
    }

    /**
     * Add emails
     *
     * @param  mixed $emails
     * @return Mail
     */
    public function addEmails(mixed $emails): Mail
    {
        if (is_array($emails)) {
            foreach ($emails as $email) {
                $this->emails[] = ['email' => $email];
            }
        } else if (is_string($emails)) {
            $this->emails[] = ['email' => $emails];
        }
        return $this;
    }

    /**
     * Set options
     *
     * @param  array $options
     * @return Mail
     */
    public function setOptions(array $options): Mail
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Add option
     *
     * @param  mixed $option
     * @param  mixed $value
     * @return Mail
     */
    public function addOption(mixed $option, mixed $value): Mail
    {
        $this->options[$option] = $value;
        return $this;
    }

    /**
     * Add options
     *
     * @param  array $options
     * @return Mail
     */
    public function addOptions(array $options): Mail
    {
        foreach ($options as $option => $value) {
            $this->addOption($option, $value);
        }
        return $this;
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return Mail
     */
    public function writeLog(mixed $level, string $message, array $context = []): Mail
    {
        if ($this->isWithinLogLimit($level)) {
            $subject = (isset($this->options['subject'])) ?
                $this->options['subject'] : 'Log Entry:';

            $subject .= ' ' . $context['name'] . ' (' . $level . ')';

            $queue       = new Queue($this->emails);
            $mailMessage = new Message($subject);

            if (isset($this->options['headers'])) {
                foreach ($this->options['headers'] as $header => $value) {
                    switch (strtolower($header)) {
                        case 'cc':
                            $mailMessage->setCc($value);
                            break;
                        case 'bcc':
                            $mailMessage->setBcc($value);
                            break;
                        case 'from':
                            $mailMessage->setFrom($value);
                            break;
                        case 'reply-to':
                            $mailMessage->setReplyTo($value);
                            break;
                        case 'sender':
                            $mailMessage->setSender($value);
                            break;
                        case 'return-path':
                            $mailMessage->setReturnPath($value);
                            break;
                        default:
                            $mailMessage->addHeader($header, $value);
                    }
                }
            }

            $mailMessage->setBody(
                $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" .
                $message . "\t" . $this->getContext($context) . PHP_EOL
            );

            $queue->addMessage($mailMessage);
            $this->mailer->sendFromQueue($queue);
        }

        return $this;
    }

}
