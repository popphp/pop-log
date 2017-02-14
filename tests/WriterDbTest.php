<?php

namespace Pop\Log\Test;

use Pop\Log\Writer;
use Pop\Db\Db;

class WriterDbTest extends \PHPUnit_Framework_TestCase
{

    public function testConstructor()
    {
        $db     = Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $writer = new Writer\Db($db, 'logs');
        $this->assertInstanceOf('Pop\Log\Writer\Db', $writer);
    }

    public function testLog()
    {
        $db     = Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $writer = new Writer\Db($db, 'logs');

        $writer->writeLog(5, 'This is a database test.', [
            'timestamp' => date('Y-m-d H:i:s'),
            'name'      => 'NOTICE'
        ]);

        $db->query('SELECT * FROM logs');
        $rows = [];
        while (($row = $db->fetch())) {
            $rows[] = $row;
        }
        $this->assertEquals('This is a database test.', $rows[0]['message']);
    }

}
