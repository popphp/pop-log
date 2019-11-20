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

/**
 * Log writer abstract class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2020 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.2.0
 */
abstract class AbstractWriter implements WriterInterface
{

    /**
     * Log limit
     * @var int
     */
    protected $limit = null;

    /**
     * Set log limit
     *
     * @param  int $level
     * @return AbstractWriter
     */
    public function setLogLimit($level)
    {
        $level = (int)$level;

        if (($level < 0) || ($level > 7)) {
            throw new \InvalidArgumentException('Error: The level ' . $level . ' is an invalid level.');
        }

        $this->limit = $level;
        return $this;
    }

    /**
     * Get log limit
     *
     * @return int
     */
    public function getLogLimit()
    {
        return $this->limit;
    }

    /**
     * Has log limit
     *
     * @return boolean
     */
    public function hasLogLimit()
    {
        return (null !== $this->limit);
    }

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  int $level
     * @return boolean
     */
    public function isWithinLogLimit($level)
    {
        $level = (int)$level;

        if (($level < 0) || ($level > 7)) {
            throw new \InvalidArgumentException('Error: The level ' . $level . ' is an invalid level.');
        }

        return ((null === $this->limit) || ((null !== $this->limit) && ($level <= $this->limit)));
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return AbstractWriter
     */
    abstract public function writeLog($level, $message, array $context = []);

    /**
     * Get context for log
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = [])
    {
        $messageContext = '';

        if (isset($context['timestamp'])) {
            unset($context['timestamp']);
        }
        if (isset($context['name'])) {
            unset($context['name']);
        }
        if (isset($context['format'])) {
            $format = $context['format'];
            unset($context['format']);
        } else {
            $format = 'text';
        }

        switch ($format) {
            case 'json':
                $messageContext = json_encode($context);
                break;
            default:
                foreach ($context as $key => $value) {
                    if (is_array($value)) {
                        $value = '[Array]';
                    }
                    if (is_object($value)) {
                        $value = '[Object]';
                    }
                    $messageContext .= (string)$key . '=' . (string)$value . ';';
                }
        }

        return $messageContext;
    }

}
