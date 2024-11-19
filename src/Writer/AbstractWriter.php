<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
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
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2025 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    4.0.0
 */
abstract class AbstractWriter implements WriterInterface
{

    /**
     * Log limit
     * @var ?int
     */
    protected ?int $limit = null;

    /**
     * Set log limit
     *
     * @param  int $level
     * @return AbstractWriter
     */
    public function setLogLimit(int $level): AbstractWriter
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
     * @return int|null
     */
    public function getLogLimit(): int|null
    {
        return $this->limit;
    }

    /**
     * Has log limit
     *
     * @return bool
     */
    public function hasLogLimit(): bool
    {
        return ($this->limit !== null);
    }

    /**
     * Check if a log level is within the set log level limit
     *
     * @param  int $level
     * @return bool
     */
    public function isWithinLogLimit(int $level): bool
    {
        if (($level < 0) || ($level > 7)) {
            throw new \InvalidArgumentException('Error: The level ' . $level . ' is an invalid level.');
        }

        return (($this->limit === null) || ($level <= $this->limit));
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return AbstractWriter
     */
    abstract public function writeLog(mixed $level, string $message, array $context = []): AbstractWriter;

    /**
     * Get context for log
     *
     * @param  array $context
     * @return string
     */
    public function getContext(array $context = []): string
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
            // If the data values needs to be preserved, use JSON encoding or PHP serialization
            case 'json':
                $messageContext = json_encode($context);
                break;
            case 'php':
                $messageContext = serialize($context);
                break;
            // Else, complex values like arrays and objects will get reduced to a basic string representation, i.e. [Array]
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
