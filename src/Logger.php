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
namespace Pop\Log;

/**
 * Logger class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
class Logger
{

    /**
     * Constants for log levels
     * @var int
     */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * Message level short codes
     * @var array
     */
    protected $levels = [
        0 => 'EMERGENCY',
        1 => 'ALERT',
        2 => 'CRITICAL',
        3 => 'ERROR',
        4 => 'WARNING',
        5 => 'NOTICE',
        6 => 'INFO',
        7 => 'DEBUG'
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
    protected $timestampFormat = 'Y-m-d H:i:s';

    /**
     * Constructor
     *
     * Instantiate the logger object
     *
     * @param  Writer\WriterInterface|array $writer
     * @param  string                 $timestampFormat
     */
    public function __construct($writer = null, $timestampFormat = 'Y-m-d H:i:s')
    {
        if (null !== $timestampFormat) {
            $this->setTimestampFormat($timestampFormat);
        }

        if (null !== $writer) {
            if (is_array($writer)) {
                $this->addWriters($writer);
            } else {
                $this->addWriter($writer);
            }
        }
    }

    /**
     * Add log writers
     *
     * @param  array $writers
     * @return Logger
     */
    public function addWriters(array $writers)
    {
        foreach ($writers as $writer) {
            $this->addWriter($writer);
        }
        return $this;
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
     * Set log level limit for all log writers
     *
     * @param  int $level
     * @return Logger
     */
    public function setLogLimit($level)
    {
        foreach ($this->writers as $writer) {
            $writer->setLogLimit($level);
        }
        return $this;
    }

    /**
     * Set timestamp format
     *
     * @param  string $format
     * @return Logger
     */
    public function setTimestampFormat($format = 'Y-m-d H:i:s')
    {
        $this->timestampFormat = $format;
        return $this;
    }

    /**
     * Get timestamp format
     *
     * @return string
     */
    public function getTimestampFormat()
    {
        return $this->timestampFormat;
    }

    /**
     * Get level
     *
     * @param  int $level
     * @return string
     */
    public function getLevel($level)
    {
        return (isset($this->levels[(int)$level])) ? $this->levels[(int)$level] : '';
    }

    /**
     * Add an EMERGENCY log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function emergency($message, array $context = [])
    {
        return $this->log(self::EMERGENCY, $message, $context);
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
     * Add a CRITICAL log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function critical($message, array $context = [])
    {
        return $this->log(self::CRITICAL, $message, $context);
    }

    /**
     * Add an ERROR log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function error($message, array $context = [])
    {
        return $this->log(self::ERROR, $message, $context);
    }

    /**
     * Add a WARNING log entry
     *
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function warning($message, array $context = [])
    {
        return $this->log(self::WARNING, $message, $context);
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
     * Add a log entry
     *
     * @param  mixed $level
     * @param  mixed $message
     * @param  array $context
     * @return Logger
     */
    public function log($level, $message, array $context = [])
    {
        if (!isset($context['timestamp'])) {
            $context['timestamp'] = date($this->timestampFormat);
        }
        if (!isset($context['name'])) {
            $context['name'] = $this->levels[$level];
        }

        foreach ($this->writers as $writer) {
            $writer->writeLog($level, (string)$message, $context);
        }

        return $this;
    }

}
