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
 * @package    Pop\Log
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2016 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.popphp.org/license     New BSD License
 * @version    3.0.0
 */
class File extends AbstractWriter
{

    /**
     * Log file
     * @var string
     */
    protected $file = null;

    /**
     * Constructor
     *
     * Instantiate the file writer object
     *
     * @param  string $file
     */
    public function __construct($file)
    {
        if (!file_exists($file)) {
            touch($file);
        }

        $this->file  = $file;
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
        $ext = substr($this->file, -4);
        switch ($ext) {
            case '.csv':
                $message = '"' . str_replace('"', '\"', $message) . '"' ;
                $entry   = $context['timestamp'] . "," . $level . "," . $context['name'] . "," . $message . "," . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->file, $entry, FILE_APPEND);
                break;

            case '.tsv':
                $message = '"' . str_replace('"', '\"', $message) . '"' ;
                $entry   = $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" . $message . "\t" . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->file, $entry, FILE_APPEND);
                break;

            case '.xml':
                $output = file_get_contents($this->file);
                if (strpos($output, '<?xml version') === false) {
                    $output = '<?xml version="1.0" encoding="utf-8"?>' . PHP_EOL . '<log>' . PHP_EOL . '</log>' . PHP_EOL;
                }

                $messageContext = $this->getContext($context);

                $entry  = ($messageContext != '') ?
                    '    <entry timestamp="' . $context['timestamp'] . '" priority="' . $level . '" name="' . $context['name'] . '" context="' . $messageContext . '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL :
                    '    <entry timestamp="' . $context['timestamp'] . '" priority="' . $level . '" name="' . $context['name'] . '"><![CDATA[' . $message . ']]></entry>' . PHP_EOL;

                $output = str_replace('</log>' . PHP_EOL, $entry . '</log>' . PHP_EOL, $output);
                file_put_contents($this->file, $output);
                break;

            default:
                $entry = $context['timestamp'] . "\t" . $level . "\t" . $context['name'] . "\t" . $message . "\t" . $this->getContext($context) . PHP_EOL;
                file_put_contents($this->file, $entry, FILE_APPEND);
        }

        return $this;
    }

}
