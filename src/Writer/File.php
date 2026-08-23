<?php
declare(strict_types=1);
/**
 * Pop PHP Framework (https://www.popphp.org/)
 *
 * @link       https://github.com/popphp/popphp-framework
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Pop\Log\Writer;

use Pop\Log\Formatter;

/**
 * File log writer class
 *
 * @category   Pop
 * @package    Pop\Log
 * @author     Nick Sagona, III <nick@popphp.org>
 * @copyright  Copyright (c) 2009-2026 Nick Sagona, III
 * @license    https://www.popphp.org/license     New BSD License
 * @version    5.0.0
 */
class File extends AbstractWriter
{

    /**
     * Log file
     * @var ?string
     */
    protected ?string $file = null;

    /**
     * Log file type
     * @var ?string
     */
    protected ?string $type = null;

    /**
     * Formatter, null only when using the legacy xml/json whole-file-rewrite handling
     * @var ?Formatter\FormatterInterface
     */
    protected ?Formatter\FormatterInterface $formatter = null;

    /**
     * Constructor
     *
     * Instantiate the file writer object
     *
     * @param  string                        $file
     * @param  ?Formatter\FormatterInterface $formatter
     */
    public function __construct(string $file, ?Formatter\FormatterInterface $formatter = null)
    {
        if (!file_exists($file)) {
            touch($file);
        }

        $parts = pathinfo($file);

        $this->file = $file;
        $this->type = $parts['extension'] ?? null;

        $this->formatter = $formatter ?? match (strtolower($this->type ?? '')) {
            'csv'             => new Formatter\Csv(),
            'tsv'             => new Formatter\Tsv(),
            'jsonl', 'ndjson' => new Formatter\NdJson(),
            'xml', 'json'     => null,
            default           => new Formatter\Line(),
        };
    }

    /**
     * Get file
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get type
     * @return ?string
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Get formatter
     * @return ?Formatter\FormatterInterface
     */
    public function getFormatter(): ?Formatter\FormatterInterface
    {
        return $this->formatter;
    }

    /**
     * Write to the log
     *
     * @param  string $level
     * @param  string $message
     * @param  array  $context
     * @return File
     */
    public function writeLog(string $level, string $message, array $context = []): File
    {
        if ($this->isWithinLogLimit($level)) {
            if ($this->formatter !== null) {
                $entry = $this->formatter->format($level, $message, $context) . PHP_EOL;
                file_put_contents($this->file, $entry, FILE_APPEND);
            } else {
                switch (strtolower($this->type)) {
                    case 'xml':
                        $messageContext = $this->getContext($context);

                        $entry  = ($messageContext != '') ?
                            '    <entry timestamp="' . $context['timestamp'] . '" priority="' .
                            $level . '" name="' . $context['name'] . '" context="' . $messageContext .
                            '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL :
                            '    <entry timestamp="' . $context['timestamp'] . '" priority="' .
                            $level . '" name="' . $context['name'] . '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL;

                        $this->withExclusiveLock(function ($output) use ($entry) {
                            if (strpos($output, '<?xml version') === false) {
                                $output = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL .
                                    '<log>' . PHP_EOL . '</log>' . PHP_EOL;
                            }
                            return str_replace('</log>' . PHP_EOL, $entry . '</log>' . PHP_EOL, $output);
                        });
                        break;

                    case 'json':
                        $messageContext = $this->getContext($context);

                        $newEntry = [
                            'timestamp' => $context['timestamp'],
                            'priority'  => $level,
                            'name'      => $context['name'],
                            'message'   => $message,
                            'context'   => $messageContext
                        ];

                        $this->withExclusiveLock(function ($output) use ($newEntry) {
                            $json = (strpos($output, '{') !== false) ?
                                json_decode($output, true) : [];
                            $json[] = $newEntry;
                            return json_encode($json, JSON_PRETTY_PRINT);
                        });
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Run a read-modify-write cycle against the log file under an exclusive lock, to prevent two
     * concurrent writers from racing on file_get_contents()/file_put_contents() and silently losing
     * one writer's entry. $transform receives the file's current content and must return the new
     * content to write back.
     *
     * @param  callable $transform
     * @throws Exception
     * @return void
     */
    protected function withExclusiveLock(callable $transform): void
    {
        $handle = @fopen($this->file, 'c+');

        if (($handle === false) || (!flock($handle, LOCK_EX))) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new Exception('Unable to acquire an exclusive lock on ' . $this->file);
        }

        $new = $transform(stream_get_contents($handle));

        rewind($handle);
        ftruncate($handle, 0);
        fwrite($handle, $new);

        flock($handle, LOCK_UN);
        fclose($handle);
    }

}
