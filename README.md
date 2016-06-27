pop-log
=======

[![Build Status](https://travis-ci.org/popphp/pop-log.svg?branch=master)](https://travis-ci.org/popphp/pop-log)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-log)](http://cc.popphp.org/pop-log/)

OVERVIEW
--------
`pop-log` is a logging component that provides a way of logging events following the standard
BSD syslog protocol outlined in [RFC-3164](http://tools.ietf.org/html/rfc3164). Support is built-in
for writing log messages to a file or database table or deploying them via email. The eight
available log message severity values are:

* EMERG  (0)
* ALERT  (1)
* CRIT   (2)
* ERR    (3)
* WARN   (4)
* NOTICE (5)
* INFO   (6)
* DEBUG  (7)

and are available via their respective methods:

* $log->emerg($message);
* $log->alert($message);
* $log->crit($message);
* $log->err($message);
* $log->warn($message);
* $log->notice($message);
* $log->info($message);
* $log->debug($message);

`pop-log` is a component of the [Pop PHP Framework](http://www.popphp.org/).

INSTALL
-------

Install `pop-log` using Composer.

    composer require popphp/pop-log

BASIC USAGE
-----------

### Using a log file

Setting up and using a log file is pretty simple. Plain text is the default,
but there is also support for CSV, TSV and XML formats:

```php
use Pop\Log\Logger;
use Pop\Log\Writer;

$log = new Logger(new Writer\File(__DIR__ . '/logs/app.log'));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

Then, your 'app.log' file will contain:

    2015-07-11 12:32:32    6    INFO    Just a info message.
    2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!

### Using email

Here's an example using email, which requires you to install `popphp/pop-mail`:

```php
use Pop\Log\Logger;
use Pop\Log\Writer;

$log = new Logger(new Writer\Mail([
    'sysadmin@mydomain.com', 'logs@mydomain.com'
]));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

Then the emails listed above will receive a series of emails like this:

    Subject: Log Entry: INFO (6)
    2015-07-11 12:32:32    6    INFO    Just a info message.

and

    Subject: Log Entry: ALERT (1)
    2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!

### Using a database table

Writing a log to a table in a database requires you to install `popphp/pop-db`:

```php
use Pop\Db\Db;
use Pop\Db\Sql;
use Pop\Log\Logger;
use Pop\Log\Writer;

$db  = Db::connent('sqlite', __DIR__ . '/logs/.htapplog.sqlite');
$sql = new Sql($db);

$log = new Logger(new Writer\Db($sql, 'system_logs'));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

In this case, the logs are written to a database table that has the columns
`id`, `timestamp`, `level`, `name` and `message`. So, after the example above,
your database table would look like this:

| Id | Timestamp           | Level    | Name  | Message                               |
|----|---------------------|----------|-------|---------------------------------------|
| 1  | 2015-07-11 12:32:32 | 6        | INFO  | Just a info message.                  |
| 2  | 2015-07-11 12:32:33 | 1        | ALERT | Look Out! Something serious happened! |

### Custom logging

You can also write a non-standard, custom log that is specific to your app:

```php
use Pop\Log\Logger;
use Pop\Log\Writer;

$log = new Logger(new Writer\File(__DIR__ . '/logs/app.log'));

$log->customLog('This is a custom log.');
```