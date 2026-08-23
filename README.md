pop-log
=======

[![Build Status](https://github.com/popphp/pop-log/workflows/phpunit/badge.svg)](https://github.com/popphp/pop-log/actions)
[![Coverage Status](https://cc.popphp.org/coverage.php?comp=pop-log)](https://cc.popphp.org/pop-log/)

[![Join the chat at https://discord.gg/TZjgT74U7E](https://media.popphp.org/img/discord.svg)](https://discord.gg/TZjgT74U7E)

* [Overview](#overview)
* [Install](#install)
* [Quickstart](#quickstart)
* [Writers](#writers)
  - [File](#file)
  - [Mail](#mail)
  - [Database](#database)
  - [HTTP](#http)
  - [Syslog](#syslog)
  - [Stream](#stream)
* [Exceptions](#exceptions)
* [PSR-3 Compatibility](#psr-3-compatibility)
* [Context](#context)
* [Processors](#processors)
* [Limits](#limits)

Overview
--------
`pop-log` is a logging component that provides a way of logging events following the standard
BSD syslog protocol outlined in [RFC-3164](http://tools.ietf.org/html/rfc3164), and is fully compatible with
[PSR-3](https://www.php-fig.org/psr/psr-3/) (`Psr\Log\LoggerInterface`). Support is built-in for writing log
messages to a file, a database table, or deploying them via email, HTTP, a real RFC-3164 syslog packet over
UDP, or a stream such as stdout, stderr, or any other PHP stream resource. The eight available log message
severity values are:

* `emergency` (0)
* `alert` (1)
* `critical` (2)
* `error` (3)
* `warning` (4)
* `notice` (5)
* `info` (6)
* `debug` (7)

and are available via their respective methods:

* $log->emergency($message);
* $log->alert($message);
* $log->critical($message);
* $log->error($message);
* $log->warning($message);
* $log->notice($message);
* $log->info($message);
* $log->debug($message);

`pop-log` is a component of the [Pop PHP Framework](https://www.popphp.org/).

[Top](#pop-log)

Install
-------

Requires PHP >= 8.4.0. The only runtime dependency is [`psr/log`](https://packagist.org/packages/psr/log)
(`^3.0`) &mdash; support for the `Database`, `Mail`, and `HTTP` writers additionally requires the
corresponding Pop component (`popphp/pop-db`, `popphp/pop-mail`, `popphp/pop-http`), each of which is
otherwise entirely optional.

This README documents the upcoming 5.0 release (PSR-3 compatibility, the real RFC-3164 `Syslog` writer,
`Stream`, `Processors`, `Formatters`), which has not been tagged yet. Until it is, install the `dev-2026`
branch directly:

    composer require popphp/pop-log:dev-dev-2026

Or, require it in your composer.json file

    "require": {
        "popphp/pop-log" : "dev-dev-2026"
    }

Once 5.0.0 is tagged, this section will be updated to a normal version constraint (e.g. `^5.0`).

[Top](#pop-log)

Quickstart
----------

This is a basic example using the file writer:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.log'));

$log->info('Just a info message');
$log->alert('Look Out! Something serious happened!');
```

Then, your 'app.log' file will contain:

```text
2015-07-11 12:32:32    info    INFO    Just a info message
2015-07-11 12:32:33    alert   ALERT   Look Out! Something serious happened!
```

[Top](#pop-log)

Writers
-------

There are six available log writers, but others can be created if they implement the
`Pop\Log\Writer\WriterInterface`.

### File

The file log writer simply stores the log output to a log file on disk. The log file format
is derived from the log filename. Supported log file types include:

- Plain text (`.log` or `.txt`)
- CSV (`.csv`)
- TSV (`.tsv`)
- XML (`.xml`)
- JSON (`.json`)
- NDJSON / JSON Lines (`.jsonl` or `.ndjson`)

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.csv'));

$context = [
    'name'      => 'my-log-entry',
    'timestamp' => date('Y-m-d H:i:s')
];

$log->info('Just a info message', $context);
```

The above code creates a CSV file with the log entry:

```csv
2023-10-31 15:58:28,info,my-log-entry,"Just a info message",
```

#### Formatters

The log file format above is auto-selected from the file's extension and dispatched to a
`Pop\Log\Formatter\FormatterInterface` implementation:

| Extension            | Formatter                    |
|-----------------------|-------------------------------|
| `.csv`                 | `Formatter\Csv`               |
| `.tsv`                 | `Formatter\Tsv`                |
| `.jsonl`, `.ndjson`     | `Formatter\NdJson`             |
| `.xml`, `.json`         | legacy whole-file-rewrite handling (unchanged, no formatter) |
| anything else           | `Formatter\Line`               |

You can also pass a formatter explicitly as the second constructor argument to override this
auto-selection, for example to force NDJSON output regardless of the file's extension (this also
works to override `.xml`/`.json` paths, which otherwise use the legacy whole-file-rewrite handling):

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;
use Pop\Log\Formatter;

$log = new Logger(new File(__DIR__ . '/logs/app.log', new Formatter\NdJson()));
```

A custom formatter just needs to implement `Pop\Log\Formatter\FormatterInterface`.

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

$log->info('Just a info message');
$log->alert('Look Out! Something serious happened!');
```

Then the emails listed above will receive a series of emails like this:

```text
Subject: Custom Log Entry: INFO (info)
2023-11-11 12:32:32    info    INFO    Just a info message
```
```text
Subject: Custom Log Entry: ALERT (alert)
2023-11-11 12:32:33    alert   ALERT   Look Out! Something serious happened!
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

$log->info('Just a info message');
$log->alert('Look Out! Something serious happened!');
```

In this case, the logs are written to a database table that has the columns
`id`, `timestamp`, `level`, `name`, `message` and `context`. So, after the example above,
your database table would look like this:

| Id | Timestamp           | Level    | Name  | Message                               | Context |
|----|---------------------|----------|-------|----------------------------------------|---------|
| 1  | 2015-07-11 12:32:32 | info     | INFO  | Just a info message                   |         |
| 2  | 2015-07-11 12:32:33 | alert    | ALERT | Look Out! Something serious happened! |         |


[Top](#pop-log)

### HTTP

Using the HTTP writer requires the `pop-http` component. It creates a request and sends
it to the HTTP logging resource. (Refer to the `pop-http` documentation for more information
on how to use the HTTP client.)

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

$log = new Logger(new Writer\Http($client));
$log->info('Just a info message');
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

### Syslog

The syslog log writer sends a real [RFC-3164](http://tools.ietf.org/html/rfc3164)-formatted packet
(`<PRI>HEADER TAG: MSG`) over UDP to a syslog daemon or network log collector (rsyslog, syslog-ng, Papertrail,
etc). It does not require any other Pop component.

```php
use Pop\Log\Facility;
use Pop\Log\Logger;
use Pop\Log\Writer\Syslog;

$log = new Logger(new Syslog(Facility::LOCAL0, 'logs.mydomain.com', 514, 'my-app'));

$log->info('Just a info message');
$log->alert('Look Out! Something serious happened!');
```

`Facility` is a backed enum covering all 24 RFC-3164 facilities (`KERNEL`, `USER`, `MAIL`, `DAEMON`, `AUTH`,
`SYSLOG`, `LPR`, `NEWS`, `UUCP`, `CRON`, `AUTHPRIV`, `FTP`, `NTP`, `LOGAUDIT`, `LOGALERT`, `CLOCK`,
`LOCAL0`-`LOCAL7`) — it defaults to `Facility::USER`. The constructor also accepts an optional `$tag` (defaults
to the running script's basename), `$hostname` (defaults to `gethostname()`), and `$includePid` (defaults to
`true`, appending `[pid]` after the tag). Packets are capped at 1024 bytes per RFC-3164 and truncated if
longer.

[Top](#pop-log)

### Stream

The stream log writer sends log entries to any writable PHP stream — `STDOUT`/`STDERR`, an arbitrary
`fopen()` resource, or a stream URL string (`php://stdout`, `php://memory`, a file path, etc). It's the
natural fit for 12-factor/containerized deployments, where the container runtime collects whatever the
process writes to stdout/stderr, and for simple CLI scripts that want log output visible in the terminal.
It does not require any other Pop component.

```php
use Pop\Log\Logger;
use Pop\Log\Writer\Stream;

$log = new Logger(Stream::stdout());

$log->info('Just a info message');
$log->alert('Look Out! Something serious happened!');
```

`Stream::stdout()` and `Stream::stderr()` are convenience factories for the common case. You can also
construct directly from a resource or a stream URL string, and pass a formatter as needed — like `File`,
output shape is driven by `Pop\Log\Formatter\FormatterInterface`, defaulting to `Formatter\NdJson` (one
JSON object per line, matching what most log aggregators expect from stdout):

```php
use Pop\Log\Writer\Stream;
use Pop\Log\Formatter;

// An already-open resource (including STDOUT/STDERR) is never closed by the writer — the caller keeps
// ownership of it.
$stream = new Stream(STDOUT, new Formatter\Line());

// A string is opened here (fopen($stream, 'a')) and closed automatically when the writer is destroyed.
$memory = new Stream('php://memory');
```

[Top](#pop-log)

Exceptions
----------

Several writers throw `Pop\Log\Writer\Exception` when they can't do their job:

- **`Syslog`** &mdash; the constructor throws if the UDP socket to the given host/port can't be opened
  (e.g. the hostname doesn't resolve).
- **`File`** &mdash; `writeLog()` throws for the `.xml`/`.json` legacy whole-file-rewrite paths if an
  exclusive lock on the log file can't be acquired.
- **`Stream`** &mdash; the constructor throws if given something that isn't a resource or a valid stream
  URL string, or a string that fails to open; `writeLog()` throws if the underlying stream is no longer
  open (e.g. a caller closed a resource they supplied) or if the write itself fails.

A `Logger` with multiple writers isolates these failures per writer: if one writer throws during a
`log()`/`info()`/etc. call, the remaining writers still run, and the first exception encountered is
re-thrown only after every writer has had a chance to write.

[Top](#pop-log)

PSR-3 Compatibility
--------------------

`Pop\Log\Logger` implements [`Psr\Log\LoggerInterface`](https://www.php-fig.org/psr/psr-3/) directly, so it can
be handed to any library or framework code that expects a PSR-3 logger. This means every logging method
(`emergency()`, `alert()`, ..., `debug()`, `log()`) returns `void` rather than `Logger` — chaining calls with
`->` is not supported.

Messages support `{placeholder}` interpolation from the context array, per the PSR-3 spec:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.log'));
$log->info('User {user} logged in from {ip}', ['user' => 'nick', 'ip' => '1.2.3.4']);
```

produces:

```text
2026-08-09 12:32:32    info    INFO    User nick logged in from 1.2.3.4
```

A context value consumed by a `{placeholder}` is not also duplicated in the writer's separate serialized
context output. `Logger::EMERGENCY` through `Logger::DEBUG` are PSR-3 level strings (e.g. `Logger::ERROR ===
'error'`), matching `Psr\Log\LogLevel::*` exactly; legacy severity ints (0-7) are still accepted anywhere a
level is passed in (`log()`, `setLogLimit()`), normalized internally. An invalid level (not a recognized
PSR-3 string or an int outside 0-7) throws `Psr\Log\InvalidArgumentException`.

To go the other direction &mdash; get a level's uppercase display name from either form &mdash; use
`$log->getLevel($level)` (instance) or the equivalent static `Logger::getLogLevel($level)`:

```php
$log->getLevel(Logger::ERROR); // 'ERROR'
Logger::getLogLevel(3);        // 'ERROR'
```

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

$log->info('Just a info message', $context);
```

#### The `Context` Utility

`Pop\Log\Context` is the shared utility that writers and formatters use to turn the array above into
writer-facing output, and it's directly usable by your own code too &mdash; most relevantly, if you're
writing a custom `Formatter` or `Writer`:

- `Context::serialize(array $context): string` &mdash; strips the reserved `timestamp`/`name`/`format`
  keys and serializes the rest as text (`key=value;` pairs), JSON, or PHP-serialized, depending on
  `$context['format']`. This is what powers the trailing context blob you see appended to `File`'s
  plain-text/CSV/TSV output.
- `Context::sanitize(string $value): string` &mdash; strips CR/LF from a string before it's written into
  a delimited/line-based log format, to prevent an embedded newline from forging a fake extra line (log
  injection).

Any `Formatter\FormatterInterface` implementation that embeds level/message/context values into a
line-delimited or otherwise unescaped text format is responsible for calling `Context::sanitize()` on
them itself &mdash; writers no longer pre-sanitize before calling a formatter, since not every format
needs it (JSON's own encoding, as used by `Formatter\NdJson`, already escapes control characters safely
within string values).

[Top](#pop-log)

Processors
----------

Processors are callables that enrich the `$context` array before a log entry is written. Each
processor has the signature `callable(array $context): array` &mdash; it receives the current
context and must return the (possibly modified) context array. Register one with `addProcessor()`:

```php
use Pop\Log\Logger;
use Pop\Log\Writer\File;

$log = new Logger(new File(__DIR__ . '/logs/app.log'));

$log->addProcessor(function (array $context): array {
    $context['request_id'] = bin2hex(random_bytes(8));
    return $context;
});
```

Processors run *before* `{placeholder}` interpolation, so a value a processor injects is
immediately usable as a placeholder in the log message:

```php
$log->addProcessor(function (array $context): array {
    $context['request_id'] = 'req-123';
    return $context;
});

$log->info('Handling request {request_id}'); // "Handling request req-123"
```

Register several at once with `addProcessors()`. They run in registration order, and each one
sees the context as enriched by the ones before it:

```php
$log->addProcessors([
    function (array $context): array {
        $context['step'] = 'one';
        return $context;
    },
    function (array $context): array {
        $context['step'] .= '-two'; // sees 'one' from the previous processor
        return $context;
    }
]);
```

If a processor throws, the exception is *not* caught &mdash; it aborts the `log()` call
immediately, before any writer runs. This is different from a writer that throws, where the
remaining writers still run and the exception is only re-thrown after the fan-out completes.
Keep processors free of side effects that shouldn't happen unless the log entry actually gets
written.

A processor is also a handy way to set a default context serialization `format` for every
writer, without threading it through every call site. Use `??=` so a `format` passed directly
to a log call can still override the default:

```php
$log->addProcessor(function (array $context): array {
    $context['format'] ??= 'json';
    return $context;
});
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

$prodLog->setLogLimit(Logger::ERROR); // Log only ERROR and more severe
$devLog->setLogLimit(Logger::INFO);   // Log only INFO and more severe

$log = new Logger([$prodLog, $devLog]);

$log->alert('Look Out! Something serious happened!'); // Will write to both writers
$log->info('Just a info message');                    // Will write to only app_dev.log
```

The `app_prod.log` file will contain:

```text
2023-11-11 12:32:33    alert   ALERT   Look Out! Something serious happened!
```

And the `app_dev.log` file will contain:

```text
2023-11-11 12:32:33    alert   ALERT   Look Out! Something serious happened!
2023-11-11 12:32:34    info    INFO    Just a info message
```

To set the same limit across every writer registered on a `Logger` at once, instead of setting each
writer's limit individually, call `setLogLimit()` on the `Logger` itself:

```php
$log = new Logger([$prodLog, $devLog]);
$log->setLogLimit(Logger::ERROR); // every writer on this Logger now only logs ERROR and more severe
```

