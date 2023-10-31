pop-log
=======

[![Build Status](https://github.com/popphp/pop-log/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-log/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-log)](http://cc.popphp.org/pop-log/)

[![Join the chat at https://popphp.slack.com](https://media.popphp.org/img/slack.svg)](https://popphp.slack.com)
[![Join the chat at https://discord.gg/D9JBxPa5](https://media.popphp.org/img/discord.svg)](https://discord.gg/D9JBxPa5)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Context](#context)
* [Writers](#writers)
  - [File](#file)
  - [Mail](#mail)
  - [Database](#database)
  - [HTTP](#http)
* [Formats](#formats)
* [Limits](#limits)
Overview
--------
`pop-log` is a logging component that provides a way of logging events following the standard
BSD syslog protocol outlined in [RFC-3164](http://tools.ietf.org/html/rfc3164). Support is built-in for writing log messages
to a file or database table or deploying them via email or HTTP. The eight available log message
severity values are:

* EMERG  (0)
* ALERT  (1)
* CRIT   (2)
* ERR    (3)
* WARN   (4)
* NOTICE (5)
* INFO   (6)
* DEBUG  (7)

and are available via their respective methods:

* $log->emergency($message);
* $log->alert($message);
* $log->critical($message);
* $log->error($message);
* $log->warning($message);
* $log->notice($message);
* $log->info($message);
* $log->debug($message);

`pop-log` is a component of the [Pop PHP Framework](http://www.popphp.org/).

[Top](#pop-log)

Install
-------

Install `pop-log` using Composer.

    composer require popphp/pop-log

Or, require it in your composer.json file

    "require": {
        "popphp/pop-log" : "^4.0.0"
    }

[Top](#pop-log)

Quickstart
----------

This is a basic example using the file writer:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.log'));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

Then, your 'app.log' file will contain:

```text
2015-07-11 12:32:32    6    INFO    Just a info message.
2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!
```

[Top](#pop-log)

Context
-------

For more granular control of writing a log entry, the `$context` array can be
passed to the methods call to trigger the log entry. At a minimum, it can contain
a `name` and `timestamp` value:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.log'));

$context = [
    'name'      => 'my-log-entry',
    'timestamp' => date('Y-m-d H:i:s')
];

$log->info('Just a info message.', $context);
```

[Top](#pop-log)

Writers
-------

### File

[Top](#pop-log)

### Mail

Here's an example using mail, which requires `popphp/pop-mail`:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\Mail;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$mailer = new Mailer(new Sendmail());
$log    = new Logger(new Mail($mailer, [
    'sysadmin@mydomain.com', 'logs@mydomain.com'
]));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

Then the emails listed above will receive a series of emails like this:

```text
Subject: Log Entry: INFO (6)
2015-07-11 12:32:32    6    INFO    Just a info message.
```
```text
Subject: Log Entry: ALERT (1)
2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!
```

#### Mail context options 


[Top](#pop-log)

### Database

[Top](#pop-log)

### HTTP

[Top](#pop-log)

### Using an HTTP Service

Here's an example using an HTTP service:

```php
use Pop\Log\Logger;
use Pop\Log\Writer;
use Pop\Http\Client;

$stream = new Client\Stream('http://logs.mydomain.com/');
$log    = new Logger(new Writer\Http($stream);

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

The log writer will send HTTP requests with the log data to the HTTP service.

### Using a Database Table

Writing a log to a table in a database requires you to install `popphp/pop-db`:

```php
use Pop\Db\Db;
use Pop\Log\Logger;
use Pop\Log\Writer;

$db  = Db::connect('sqlite', __DIR__ . '/logs/.htapplog.sqlite');
$log = new Logger(new Writer\Db($db, 'system_logs'));

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

### Setting Log Limits

Log level limits can be set for the log writer objects to enforce the severity of
which log messages actually get logged:

```php
use Pop\Log\Logger;
use Pop\Log\Writer;

$prodLog = new Writer\File(__DIR__ . '/logs/app_prod.log');
$devLog  = new Writer\File(__DIR__ . '/logs/app_dev.log');

$prodLog->setLogLimit(3); // Log only ERROR (3) and above
$devLog->setLogLimit(6);  // Log only INFO (6) and above

$log = new Logger([$prodLog, $devLog]);

$log->alert('Look Out! Something serious happened!'); // Will write to both writers
$log->info('Just a info message.');                   // Will write to only app_dev.log

```

The `app_prod.log` file will contain:

    2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!

And the `app_dev.log` file will contain:

    2015-07-11 12:32:33    1    ALERT   Look Out! Something serious happened!
    2015-07-11 12:32:34    6    INFO    Just a info message.


