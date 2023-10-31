pop-log
=======

[![Build Status](https://github.com/popphp/pop-log/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-log/actions)
[![Coverage Status](http://cc.popphp.org/coverage.php?comp=pop-log)](http://cc.popphp.org/pop-log/)

[![Join the chat at https://popphp.slack.com](https://media.popphp.org/img/slack.svg)](https://popphp.slack.com)
[![Join the chat at https://discord.gg/D9JBxPa5](https://media.popphp.org/img/discord.svg)](https://discord.gg/D9JBxPa5)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Writers](#writers)
  - [File](#file)
  - [Mail](#mail)
  - [Database](#database)
  - [HTTP](#http)
* [Context](#context)
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

Writers
-------

### File

The file log writer simply stores the log output to a log file on disk. The log file format
is derived from the log filename. Supported log file types include:

- Plain text (`.log` or `.txt`)
- CSV (`.csv`)
- TSV (`.tsv`)
- XML (`.xml`)
- JSON (`.json`)

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.csv'));

$context = [
    'name'      => 'my-log-entry',
    'timestamp' => date('Y-m-d H:i:s')
];

$log->info('Just a info message.', $context);
```

The above code creates a CSV file with the log entry:

```csv
2023-10-31 15:58:28,6,my-log-entry,"Just a info message.",
```

[Top](#pop-log)

### Mail

The mail log writer sends the log entries via email using the `popphp/pop-mail` component.
The constructor requires a `Pop\Mail\Mailer` object and at least one email as the second
argument. An optional third argument allows you to pass in additional email headers, like
a subject and CC addresses.

```php
use Pop\Log\Logger;
use Pop\Log\Writer\Mail;
use Pop\Mail\Mailer;
use Pop\Mail\Transport\Sendmail;

$emails  = ['sysadmin@mydomain.com', 'logs@mydomain.com'];
$options = [
    'subject' => 'Custom Log Entry:',
    'cc'      => 'another@mydomain.com'
];

$mailer = new Mailer(new Sendmail());
$log    = new Logger(new Mail($mailer, $emails, $options));

$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

Then the emails listed above will receive a series of emails like this:

```text
Subject: Custom Log Entry: INFO (6)
2023-11-11 12:32:32    6    INFO    Just a info message.
```
```text
Subject: Custom Log Entry: ALERT (1)
2023-11-11 12:32:33    1    ALERT   Look Out! Something serious happened!
```

[Top](#pop-log)

### Database

Writing a log to a table in a database requires the `popphp/pop-db` component.
The database writer constructor takes an instance of `Pop\Db\Adapter\AbstractAdapter`
and also an optional `$table` argument (the default table name is `pop_log`).

```php
use Pop\Db\Db;
use Pop\Log\Logger;
use Pop\Log\Writer\Database;

$db  = Db::connect('sqlite', __DIR__ . '/logs/.htapplog.sqlite');
$log = new Logger(new Database($db, 'system_logs'));

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


[Top](#pop-log)

### HTTP

Using the HTTP writer requires the `pop-http` component. It creates a request and sends
it to the HTTP logging resource. Refer to the `pop-http` documentation for more information
on how to use it.

```php
use Pop\Log\Logger;
use Pop\Log\Writer;
use Pop\Http\Client;
use Pop\Http\Auth;

$client = new Client(
    'https://logs.mydomain.com/',
    ['method' => 'POST'],
    Auth::createKey('LOG_API_KEY')
);

$log = new Logger(new Writer\Http($client);
$log->info('Just a info message.');
$log->alert('Look Out! Something serious happened!');
```

The log writer will send HTTP requests with the log data to the HTTP service with the following
HTTP data fields:

- `timestamp`
- `level`
- `name`
- `message`
- `context`

[Top](#pop-log)

Context
-------

For additional contextual information, the `$context` array can be passed to the methods
called to trigger the log entry. It can contain:

```php
$context = [
    'name'      => 'my-log-entry',
    'timestamp' => date('Y-m-d H:i:s'),
    'format'    => 'json'
];
```

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

Limits
-------

Log level limits can be set for the log writer objects to enforce the severity of
which log messages actually get logged:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$prodLog = new File(__DIR__ . '/logs/app_prod.log');
$devLog  = new File(__DIR__ . '/logs/app_dev.log');

$prodLog->setLogLimit(3); // Log only ERROR (3) and above
$devLog->setLogLimit(6);  // Log only INFO (6) and above

$log = new Logger([$prodLog, $devLog]);

$log->alert('Look Out! Something serious happened!'); // Will write to both writers
$log->info('Just a info message.');                   // Will write to only app_dev.log
```

The `app_prod.log` file will contain:

```text
2023-11-11 12:32:33    1    ALERT   Look Out! Something serious happened!
```

And the `app_dev.log` file will contain:

```text
2023-11-11 12:32:33    1    ALERT   Look Out! Something serious happened!
2023-11-11 12:32:34    6    INFO    Just a info message.
```

