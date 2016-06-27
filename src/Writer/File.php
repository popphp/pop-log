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
namespace Pop\Log\Writer;

/**
 * File log writer class
 *
 * @category   Pop
 * @package    Pop_Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    2.1.0
 */
class File extends AbstractWriter
{

    /**
     * Array of allowed log file types
     * @var array
     */
    protected $allowed = [
        'csv' => 'text/csv',
        'log' => 'text/plain',
        'tsv' => 'text/tsv',
        'txt' => 'text/plain',
        'xml' => 'application/xml'
    ];

    /**
     * Full path of log file, i.e. '/path/to/logfile.ext'
     * @var string
     */
    protected $fullpath = null;

    /**
     * Full basename of log file, i.e. 'logfile.ext'
     * @var string
     */
    protected $basename = null;

    /**
     * Full filename of log file, i.e. 'logfile'
     * @var string
     */
    protected $filename = null;

    /**
     * Log file extension, i.e. 'ext'
     * @var string
     */
    protected $extension = null;

    /**
     * Log file size in bytes
     * @var int
     */
    protected $size = 0;

    /**
     * Log file mime type
     * @var string
     */
    protected $mime = 'text/plain';

    /**
     * Constructor
     *
     * Instantiate the file writer object
     *
     * @param  string $file
     * @param  array $types
     * @throws Exception
     * @return File
     */
    public function __construct($file, array $types = null)
    {
        if (null !== $types) {
            $this->allowed = $types;
        }

        if (!file_exists($file)) {
            touch($file);
        }

        $this->fullpath  = $file;
        $parts           = pathinfo($file);
        $this->size      = filesize($file);
        $this->basename  = $parts['basename'];
        $this->filename  = $parts['filename'];
        $this->extension = (isset($parts['extension']) && ($parts['extension'] != '')) ? $parts['extension'] : null;

        if ((count($this->allowed) > 0) && !isset($this->allowed[strtolower($this->extension)])) {
            throw new Exception('Error: That log file type is not allowed.');
        }

        if (null !== $this->extension) {
            $this->mime = $this->allowed[strtolower($this->extension)];
        }
    }

    /**
     * Write to the log
     *
     * @param  mixed  $level
     * @param  string $message
     * @param  array  $context
     * @return File
     */
    public function writeLog($level, $message, array $context = [])
    {
        switch ($this->mime) {
            case 'text/plain':
                $entry = $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" . $message . "\t" . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->fullpath, $entry, FILE_APPEND);
                break;

            case 'text/csv':
                $message = '"' . str_replace('"', '\"', $message) . '"' ;
                $entry   = $context['timestamp'] . "," . $level . "," . $context['name'] . "," . $message . "," . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->fullpath, $entry, FILE_APPEND);
                break;

            case 'text/tsv':
                $message = '"' . str_replace('"', '\"', $message) . '"' ;
                $entry   = $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" . $message . "\t" . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->fullpath, $entry, FILE_APPEND);
                break;

            case 'application/xml':
                $output = file_get_contents($this->fullpath);
                if (strpos($output, '<?xml version') === false) {
                    $output = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . '<log>' . PHP_EOL . '</log>' . PHP_EOL;
                }

                $messageContext = $this->getContext($context);

                $entry  = ($messageContext != '') ?
                    '    <entry timestamp="' . $context['timestamp'] . '" priority="' . $level . '" name="' . $context['name'] . '" context="' . $messageContext . '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL :
                    '    <entry timestamp="' . $context['timestamp'] . '" priority="' . $level . '" name="' . $context['name'] . '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL;

                $output = str_replace('</log>' . PHP_EOL, $entry . '</log>' . PHP_EOL, $output);
                file_put_contents($this->fullpath, $output);
                break;
        }

        return $this;
    }

    /**
     * Write to a custom log
     *
     * @param  string $content
     * @return File
     */
    public function writeCustomLog($content)
    {
        file_put_contents($this->fullpath, $content . PHP_EOL, FILE_APPEND);
        return $this;
    }

}
