<?php
/**
 * Pop PHP Framework (http://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
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
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class Mail extends AbstractWriter
{

    /**
     * Mailer object
     * @var Mailer
     */
    protected $mailer = null;

    /**
     * List of emails in which to send the log messages
     * @var mixed
     */
    protected $emails = null;

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
     * @throws Exception
     */
    public function __construct(Mailer $mailer, $emails, array $options = [])
    {
        $this->mailer  = $mailer;
        $this->options = $options;
        $this->emails  = $emails;
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
        $subject = (isset($this->options['subject'])) ?
            $this->options['subject'] : 'Log Entry:';

        $subject .= ' ' . $context['name'] . ' (' . $level . ')';

        $queue   = new Queue($this->emails);
        $message = new Message($subject);

        if (isset($this->options['headers'])) {
            foreach ($this->options['headers'] as $header => $value) {
                switch (strtolower($header)) {
                    case 'cc':
                        $message->setCc($value);
                        break;
                    case 'bcc':
                        $message->setBcc($value);
                        break;
                    case 'from':
                        $message->setFrom($value);
                        break;
                    case 'reply-to':
                        $message->setReplyTo($value);
                        break;
                    case 'sender':
                        $message->setSender($value);
                        break;
                    case 'return-path':
                        $message->setReturnPath($value);
                        break;
                    default:
                        $message->addHeader($header, $value);
                }
            }
        }

        $message->setBody(
            $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" .
            $message . "\t" . $this->getContext($context) . PHP_EOL
        );

        $queue->addMessage($message);
        $this->mailer->sendFromQueue($queue);

        return $this;
    }

}
