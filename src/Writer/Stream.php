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
namespace Pop\Log\Writer;

use Pop\Log\Formatter;

/**
 * Stream log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@noladev.com>
 * @copyright  Copyright (c) 2009-2027 NOLA Interactive, LLC.
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class Stream extends AbstractWriter
{

    /**
     * The underlying stream resource
     * @var mixed
     */
    protected mixed $stream = null;

    /**
     * Whether this instance opened (and therefore owns/closes) the stream
     * @var bool
     */
    protected bool $ownsStream = false;

    /**
     * Formatter used to shape each log entry
     * @var Formatter\FormatterInterface
     */
    protected Formatter\FormatterInterface $formatter;

    /**
     * Constructor
     *
     * Instantiate the stream writer object. Accepts either a stream URL string (opened here with
     * fopen($stream, 'a') and closed automatically when this instance is destroyed) or an already-open
     * stream resource (e.g. the STDOUT/STDERR constants, or a caller-supplied fopen() handle) — in that
     * case, the caller keeps ownership and this writer never closes it.
     *
     * @param  mixed                         $stream
     * @param  ?Formatter\FormatterInterface $formatter
     * @throws Exception
     */
    public function __construct(mixed $stream, ?Formatter\FormatterInterface $formatter = null)
    {
        if (is_string($stream)) {
            $handle = @fopen($stream, 'a');
            if ($handle === false) {
                throw new Exception('Unable to open stream: ' . $stream);
            }
            $this->stream     = $handle;
            $this->ownsStream = true;
        } elseif (is_resource($stream) && get_resource_type($stream) === 'stream') {
            $this->stream = $stream;
        } else {
            throw new Exception('Stream must be a resource or a valid stream URL string.');
        }

        $this->formatter = $formatter ?? new Formatter\NdJson();
    }

    /**
     * Create a writer targeting php://stdout
     *
     * @param  ?Formatter\FormatterInterface $formatter
     * @return static
     */
    public static function stdout(?Formatter\FormatterInterface $formatter = null): static
    {
        return new static('php://stdout', $formatter);
    }

    /**
     * Create a writer targeting php://stderr
     *
     * @param  ?Formatter\FormatterInterface $formatter
     * @return static
     */
    public static function stderr(?Formatter\FormatterInterface $formatter = null): static
    {
        return new static('php://stderr', $formatter);
    }

    /**
     * Get the underlying stream resource
     *
     * @return mixed
     */
    public function getStream(): mixed
    {
        return $this->stream;
    }

    /**
     * Get the formatter
     *
     * @return Formatter\FormatterInterface
     */
    public function getFormatter(): Formatter\FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Write to the log
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @throws Exception
     * @return Stream
     */
    public function writeLog(string $level, string $message, array $context = []): Stream
    {
        if ($this->isWithinLogLimit($level)) {
            if (!is_resource($this->stream)) {
                throw new Exception('Stream is no longer open.');
            }
            $entry = $this->formatter->format($level, $message, $context) . PHP_EOL;
            if (@fwrite($this->stream, $entry) === false) {
                throw new Exception('Unable to write to stream.');
            }
        }

        return $this;
    }

    /**
     * Destructor
     *
     * Closes the underlying stream only if this instance opened it itself (i.e. was constructed from a
     * string). A caller-supplied resource (including STDOUT/STDERR) is never closed here.
     */
    public function __destruct()
    {
        if ($this->ownsStream && is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

}
