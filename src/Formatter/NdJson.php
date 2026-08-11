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
 * NDJSON (JSON Lines) formatter — one self-contained JSON object per line
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class NdJson implements FormatterInterface
{

    /**
     * Format a single log entry into a self-contained JSON object
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return string
     */
    public function format(string $level, string $message, array $context): string
    {
        $timestamp = $context['timestamp'];
        $name      = $context['name'];
        unset($context['timestamp'], $context['name'], $context['format']);

        $encoded = json_encode([
            'timestamp' => $timestamp,
            'level'     => $level,
            'name'      => $name,
            'message'   => $message,
            'context'   => (object)$context,
        ], JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);

        return ($encoded !== false) ? $encoded : '{}';
    }

}
