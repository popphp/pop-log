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
namespace Pop\Log;

/**
 * Logger class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.1.1
 */
class Logger
{

    /**
     * Constants for log levels
     * @var int
     */
    const EMERG  = 0;
    const ALERT  = 1;
    const CRIT   = 2;
    const ERR    = 3;
    const WARN   = 4;
    const NOTICE = 5;
    const INFO   = 6;
    const DEBUG  = 7;

    /**
     * Message level short codes
     * @var array
     */
    protected $levels = [
        0 => 'EMERG',
        1 => 'ALERT',
        2 => 'CRIT',
        3 => 'ERR',
        4 => 'WARN',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG',
    ];

    /**
     * Log writers
     * @var array
     */
    protected $writers = [];

    /**
     * Log timestamp format
     * @var string
     */
    protected $timestamp = 'Y-m-d H:i:s';

    /**
     * Constructor
     *
     * Instantiate the logger object
     *
     * @param  Writer\WriterInterface $writer
     * @return Logger
     */
    public function __construct(Writer\WriterInterface $writer = null)
    {
        if (null !== $writer) {
            $this->addWriter($writer);
        }
    }

    /**
     * Add a log writer
     *
     * @param  Writer\WriterInterface $writer
     * @return Logger
     */
    public function addWriter(Writer\WriterInterface $writer)
    {
        $this->writers[] = $writer;
        return $this;
    }

    /**
     * Get all log writers
     *
     * @return array
     */
    public function getWriters()
    {
        return $this->writers;
    }

    /**
     * Set timestamp format
     *
     * @param  string $format
     * @return Logger
     */
    public function setTimestamp($format = 'Y-m-d H:i:s')
    {
        $this->timestamp = $format;
        return $this;
    }

    /**
     * Get timestamp format
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * Add a log entry
     *
     * @param  mixed $level
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function log($level, $message, array $context = [])
    {
        $context['timestamp'] = date($this->timestamp);
        $context['name']      = $this->levels[$level];

        foreach ($this->writers as $writer) {
            $writer->writeLog($level, (string)$message, $context);
        }

        return $this;
    }

    /**
     * Add an EMERG log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function emerg($message, array $context = [])
    {
        return $this->log(self::EMERG, $message, $context);
    }

    /**
     * Add an ALERT log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function alert($message, array $context = [])
    {
        return $this->log(self::ALERT, $message, $context);
    }

    /**
     * Add a CRIT log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function crit($message, array $context = [])
    {
        return $this->log(self::CRIT, $message, $context);
    }

    /**
     * Add an ERR log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function err($message, array $context = [])
    {
        return $this->log(self::ERR, $message, $context);
    }

    /**
     * Add a WARN log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function warn($message, array $context = [])
    {
        return $this->log(self::WARN, $message, $context);
    }

    /**
     * Add a NOTICE log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function notice($message, array $context = [])
    {
        return $this->log(self::NOTICE, $message, $context);
    }

    /**
     * Add an INFO log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function info($message, array $context = [])
    {
        return $this->log(self::INFO, $message, $context);
    }

    /**
     * Add a DEBUG log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function debug($message, array $context = [])
    {
        return $this->log(self::DEBUG, $message, $context);
    }

    /**
     * Write a custom log entry
     *
     * @param  string $content
     * @return Logger
     */
    public function customLog($content)
    {
        foreach ($this->writers as $writer) {
            $writer->writeCustomLog($content);
        }

        return $this;
    }

}
