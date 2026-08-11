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
namespace Pop\Log;

/**
 * Log context utility class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
final class Context
{

    /**
     * Serialize a context array (minus timestamp/name/format) as text, JSON, or PHP-serialized,
     * depending on context['format']
     *
     * @param  array $context
     * @return string
     */
    public static function serialize(array $context): string
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

    /**
     * Strip CR/LF from a value before it's written into a delimited/line-based log format, to prevent an
     * embedded newline from forging a fake extra line (or, for writers that build header-style text like
     * Mail's Subject line, a fake extra header).
     *
     * @param  string $value
     * @return string
     */
    public static function sanitize(string $value): string
    {
        return str_replace(["\r", "\n"], ' ', $value);
    }

}
