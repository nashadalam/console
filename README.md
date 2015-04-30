[![Build Status][]](https://travis-ci.org/box-project/console)
[![Latest Stable Version][]](https://packagist.org/packages/box-project/console)
[![Latest Unstable Version][]](https://packagist.org/packages/box-project/console)
[![Total Downloads][]](https://packagist.org/packages/box-project/console)

Console
=======

    $ composer require box-project/console

The Box Console component helps you create a command line application using
the inversion of control pattern, powered by existing [Symfony][] components.
All of the wiring is taken care of for you, and all that remains is creating
your console commands.

```php
#!/usr/bin/env php
<?php

use Box\Component\Console\Application;

// load composer autoloader
require __DIR__ . '/vendor/autoload.php';

// launch the application
(new Application())
    ->load('services.xml')
    ->run();
```

```
Console Tool

Usage:
 command [options] [arguments]

Options:
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: [...]
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question

Available commands:
 example           My example command
 help              Displays help for a command
 list              Lists commands
container
 container:debug   Displays current services for an application
debug
 debug:container   Displays current services for an application
```

Preparation
-----------

Before we begin, you will want to familiarize yourself with the software this
component uses. It heavily relies on a few Symfony components to do most of its
work. For convenience, links to the documentation for each of these components
are available below.

- [Symfony Console][] - You will be most interested in documentation on how to
  create your own commands and helpers, especially the sections about accepting
  input and rendering output.
- [Symfony DependencyInjection][] - You will be most interested in documentation
  about how to create definitions using either closures or configuration files.
  All other work is handled by the Box Console component, so you can skip those
  parts if they are not related to your work.
- [Symfony EventDispatcher][] - You will be most interested in documentation
  about how to create your own listener and/or subscriber. An explanation will
  later be provided on how to actual use those in the application you are
  creating.

Getting Started
---------------

To begin, you will need to create a new script.

```php
#!/usr/bin/env php
<?php

// load the library here

use Box\Component\Console\Application;
```

### Application Information

By default, all applications are created with the name and version of `UNKNOWN`.
When you run the application without any arguments, or if the `--version` option
is used, the application information will be shown.

```
Console Tool
```

To specify your own name and version, you will need to pass them as arguments
when you create your instance for the Application class.

```php
$app = new Application('Example', '0.0.0');
```

```
Example version 0.0.0
```

### Loading Services

When run on its own without any configuration, the application will simply
display a help screen and a list of commands that are currently available.
To add your own services (commands, helpers, listeners, and subscribers),
you will need to load them.

#### Closures

You can add your own service definitions by using a closure.

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;

$app->load(
    function (ContainerBuilder $container) {
        // define here
    }
);
```

#### Files

You can load your service definitions from files.

```php
$app->load('/path/to/services.xml');
```

While you are expected to provide an absolute path to the configuration files,
you may also provide a relative file path. Note, however, that relative files
will always be expected to be available from the current working directory
path.

The following types of files are supported:

- INI
- PHP (similar to using a closure)
- XML
- YAML

### Defining Services

You've learned how you can load your services, which is great and all, but how
do you actually create them? While registering services are, for the most part,
all very similar, there are some subtle variations that are very important.

> Remember, while the examples use XML, you may use any supported format that
> is listed listed above in **Files**. You may want to look at the [wiki][] to
> find examples in other formats.

#### Commands

To register a command, you will need to use the `box.console.command` tag.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container>
  <parameters>
    <parameter key="example.command.class">Example\Command</parameter>
  </parameters>
  <services>
    <service class="%example.command.class%" id="example.command">
      <tag name="box.console.command"/>
    </service>
  </services>
</container>
```

#### Helpers

To register a command, you will need to use the `box.console.helper` tag.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container>
  <parameters>
    <parameter key="example.helper.class">Example\Helper</parameter>
  </parameters>
  <services>
    <service class="%example.helper.class%" id="example.helper">
      <tag name="box.console.helper"/>
    </service>
  </services>
</container>
```

#### Events

You can find a complete listing of console events in the official Symfony
Console documentation about [`ConsoleEvents`][].

##### Listener

To register an event listener, you will need to use the
`box.console.event.listener` tag with the `event` and `method` attributes
defined.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container>
  <parameters>
    <parameter key="example.listener.class">Example\Listener</parameter>
  </parameters>
  <services>
    <service class="%example.listener.class%" id="example.listener">
      <tag name="box.console.event.listener" event="console.command" method="myMethod"/>
    </service>
  </services>
</container>
```

##### Subscriber

To register an event subscriber, you will need to use the
`box.console.event.subscriber` tag.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container>
  <parameters>
    <parameter key="example.subscriber.class">Example\Subscriber</parameter>
  </parameters>
  <services>
    <service class="%example.subscriber.class%" id="example.subscriber">
      <tag name="box.console.event.subscriber"/>
    </service>
  </services>
</container>
```

### Debugging Services

In the output of the opening example (near the top of the documentation), you
may have noticed the `debug:container` (`container:debug`) command. The 
`debug:container` command is provided by Symfony's FrameworkBundle bundle,
which allows you to view the contents of the dependency injection container.
This is very useful for when you are experiencing issues when adding new or
modifying existing services.

```
$ ./console debug:container
[container] Public services
 Service ID                          Class name
 box.console                         Symfony\Component\Console\Application
 box.console.command.container_debug Box\Component\Console\Command\ContainerDebugCommand
 box.console.helper.container        Box\Component\Console\Helper\ContainerHelper
 box.console.helper.debug_formatter  Symfony\Component\Console\Helper\DebugFormatterHelper
 box.console.helper.dialog           Symfony\Component\Console\Helper\DialogHelper
 box.console.helper.formatter        Symfony\Component\Console\Helper\FormatterHelper
 box.console.helper.process          Symfony\Component\Console\Helper\ProcessHelper
 box.console.helper.progress         Symfony\Component\Console\Helper\ProgressHelper
 box.console.helper.question         Symfony\Component\Console\Helper\QuestionHelper
 box.console.helper.table            Symfony\Component\Console\Helper\TableHelper
 box.console.helper_set              Symfony\Component\Console\Helper\HelperSet
 service_container                   Symfony\Component\DependencyInjection\ContainerBuilder
To search for a service, re-run this command with a search term. debug:container log
```

License
-------

This software is released under the [MIT License](LICENSE).

[Build Status]: https://travis-ci.org/box-project/console.png?branch=master
[Latest Stable Version]: https://poser.pugx.org/box-project/console/v/stable.png
[Latest Unstable Version]: https://poser.pugx.org/box-project/console/v/unstable.png
[Total Downloads]: https://poser.pugx.org/box-project/console/downloads.png

[Symfony]: http://symfony.org/
[Symfony Console]: http://symfony.com/doc/current/components/console/index.html
[Symfony DependencyInjection]: http://symfony.com/doc/current/components/dependency_injection/index.html
[Symfony EventDispatcher]: http://symfony.com/doc/current/components/event_dispatcher/index.html
[`ConsoleEvents`]: http://symfony.com/doc/current/components/console/events.html
[wiki]: https://github.com/box-project/console/wiki
