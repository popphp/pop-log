<?php

namespace Pop\Log\Test;

use Pop\Log\Writer;
use Pop\Db\Db;
use PHPUnit\Framework\TestCase;

class WriterDatabaseTest extends TestCase
{

    public function testConstructor()
    {
        if (file_exists(__DIR__ . '/tmp/log.sqlite')) {
            unlink(__DIR__ . '/tmp/log.sqlite');
        }
        touch(__DIR__ . '/tmp/log.sqlite');
        chmod(__DIR__ . '/tmp/log.sqlite', 0777);
        $db     = Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $writer = new Writer\Database($db, 'logs');
        $this->assertInstanceOf('Pop\Log\Writer\Database', $writer);
        $this->assertContains('logs', $db->getTables());
        $this->assertInstanceOf('Pop\Db\Adapter\Sqlite', $writer->getDb());
        $this->assertEquals('logs', $writer->getTable());
    }

    public function testLog()
    {
        $db     = Db::connect('sqlite', ['database' => __DIR__ . '/tmp/log.sqlite']);
        $writer = new Writer\Database($db, 'logs');

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
        if (file_exists(__DIR__ . '/tmp/log.sqlite')) {
            unlink(__DIR__ . '/tmp/log.sqlite');
        }
    }

}
