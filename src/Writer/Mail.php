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

/**
 * Mail log writer class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.2.0
 */
class Mail extends AbstractWriter
{

    /**
     * Array of emails in which to send the log messages
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
     * @param  mixed $emails
     * @param  array $options
     * @throws Exception
     * @return Mail
     */
    public function __construct($emails, array $options = [])
    {
        $this->options = $options;

        if (!is_array($emails)) {
            $emails = [$emails];
        }

        foreach ($emails as $key => $value) {
            if (!is_numeric($key)) {
                $this->emails[] = [
                    'name'  => $key,
                    'email' => $value
                ];
            } else {
                $this->emails[] = [
                    'email' => $value
                ];
            }
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
        $subject = (isset($this->options['subject'])) ?
            $this->options['subject'] :
            'Log Entry:';

        $subject .= ' ' . $context['name'] . ' (' . $level . ')';

        $mail = new \Pop\Mail\Mail($subject, $this->emails);
        if (isset($this->options['headers'])) {
            $mail->setHeaders($this->options['headers']);
        }

        $entry = $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" . $message . "\t" . $this->getContext($context) . PHP_EOL;

        $mail->setText($entry)
             ->send();

        return $this;
    }

    /**
     * Write to a custom log
     *
     * @param  string $content
     * @return Mail
     */
    public function writeCustomLog($content)
    {
        $subject = (isset($this->options['subject'])) ?
            $this->options['subject'] :
            'Custom Log Entry';

        $mail = new \Pop\Mail\Mail($subject, $this->emails);
        if (isset($this->options['headers'])) {
            $mail->setHeaders($this->options['headers']);
        }

        $mail->setText($content . PHP_EOL)
             ->send();

        return $this;
    }

}
