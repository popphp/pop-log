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

use Psr\Log\LogLevel;
use Psr\Log\InvalidArgumentException;

/**
 * Log level class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
final class Level
{

    /**
     * Severity map: PSR-3 level string => RFC severity int
     * @var array
     */
    private const SEVERITY = [
        LogLevel::EMERGENCY => 0,
        LogLevel::ALERT     => 1,
        LogLevel::CRITICAL  => 2,
        LogLevel::ERROR     => 3,
        LogLevel::WARNING   => 4,
        LogLevel::NOTICE    => 5,
        LogLevel::INFO      => 6,
        LogLevel::DEBUG     => 7,
    ];

    /**
     * Convert a PSR-3 level string or a legacy severity int into a severity int (0-7)
     *
     * @param  mixed $level
     * @throws InvalidArgumentException
     * @return int
     */
    public static function toSeverity(mixed $level): int
    {
        if (is_int($level) && in_array($level, self::SEVERITY, true)) {
            return $level;
        }

        if (is_string($level) && ctype_digit($level) && in_array((int)$level, self::SEVERITY, true)) {
            return (int)$level;
        }

        if (is_string($level)) {
            $name = strtolower($level);

            if (array_key_exists($name, self::SEVERITY)) {
                return self::SEVERITY[$name];
            }
        }

        throw new InvalidArgumentException(
            'Error: The level ' . get_debug_type($level) . (is_scalar($level) ? ' (' . var_export($level, true) . ')' : '') . ' is an invalid level.'
        );
    }

    /**
     * Convert a PSR-3 level string into its uppercase display name
     *
     * @param  string $level
     * @throws InvalidArgumentException
     * @return string
     */
    public static function toName(string $level): string
    {
        $name = strtolower($level);

        if (!array_key_exists($name, self::SEVERITY)) {
            throw new InvalidArgumentException('Error: The level ' . $name . ' is an invalid level.');
        }

        return strtoupper($name);
    }

    /**
     * Convert a severity int (0-7) into its canonical PSR-3 level string
     *
     * @param  int $severity
     * @throws InvalidArgumentException
     * @return string
     */
    public static function fromSeverity(int $severity): string
    {
        $level = array_search($severity, self::SEVERITY, true);

        if ($level === false) {
            throw new InvalidArgumentException('Error: The severity ' . $severity . ' is an invalid severity.');
        }

        return $level;
    }

}
