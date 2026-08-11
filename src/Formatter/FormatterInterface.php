<?php
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Formatter;

/**
 * Log formatter interface
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
interface FormatterInterface
{

    /**
     * Format a single log entry into a string
     *
     * Implementations that embed level/message/context values into a line-delimited or otherwise
     * unescaped text format are responsible for sanitizing them via Pop\Log\Context::sanitize() —
     * Writer\File does not pre-sanitize before calling this method, since not every format needs it
     * (e.g. JSON's own encoding already escapes control characters safely within string values).
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    public function format(string $level, string $message, array $context): string;

}
