<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
class Mail extends AbstractWriter
{

    /**
     * Mailer object
     * @var Mailer
     */
    protected $mailer = null;

    /**
     * Emails to which to send the log messages
     * @var array
     */
    protected $emails = [];

    /**
     * Array of mail-specific options, i.e. subject, headers, etc.
     * @var array
     */
    protected $options = [];

    /**
     * Constructor
     *
     * Instantiate the Mail writer object
     *
     * @param  Mailer $mailer
     * @param  mixed  $emails
     * @param  array  $options
     */
    public function __construct(Mailer $mailer, $emails, array $options = [])
    {
        $this->mailer  = $mailer;
        $this->options = $options;

        if (is_array($emails)) {
            foreach ($emails as $email) {
                $this->emails[] = ['email' => $email];
            }
        } else if (is_string($emails)) {
            $this->emails[] = ['email' => $emails];
        }
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return Mail
     */
    public function writeLog($level, $message, array $context = [])
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
