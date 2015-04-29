[![Build Status][]](https://travis-ci.org/box-project/php-console)
[![Coverage Status][]](https://coveralls.io/r/box-project/php-console?branch=master)
[![Latest Stable Version][]](https://packagist.org/packages/box-project/console)
[![Latest Unstable Version][]](https://packagist.org/packages/box-project/console)
[![Total Downloads][]](https://packagist.org/packages/box-project/console)

Console
=======

Uses a dependency injection container to create and integrate a command line
application.

The Box Console component allows developers to create a Symfony Console
application while using Symfony DependencyInjection to manage dependencies.
This provides a greater degree of flexibility without (in most cases) modifying
existing source code, and better opportunities to create unit tests.


Example
-------

Creating a new console application is very simple.

**console:**

```php
#!/usr/bin/env php
<?php

use Box\Component\Console\Application;

// load composer autoloader
require __DIR__ . '/vendor/autoload.php';

// launch the application
$app = new Application();
$app->run();
```

Like the Symfony Console, you will receive the expected output:

```
Console Tool

Usage:
 command [options] [arguments]

Options:
 --help (-h)           Display this help message
 --quiet (-q)          Do not output any message
 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 --version (-V)        Display this application version
 --ansi                Force ANSI output
 --no-ansi             Disable ANSI output
 --no-interaction (-n) Do not ask any interactive question

Available commands:
 help              Displays help for a command
 list              Lists commands
container
 container:debug   Displays current services for an application
debug
 debug:container   Displays current services for an application
```

Features
--------

### debug:container

In the example you may have noticed the addition of a non-standard command. The
`debug:container` (`container:debug`) command is provided by Symfony's
FrameworkBundle bundle, which allows you to view the contents of the dependency
injection container. This is very useful for when you are experiencing issues
when adding new services.

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

### Load Definitions

The component provide support for loading definitions using closures, and
configuration files (INI, PHP, XML, and YAML). You can find documentation
on how to create these files in the official [DependencyInjection documentation][].
You can safely ignore the part about creating loaders since that is already
handled by this component.

#### Closures

```php
use Symfony\Component\DependencyInjection\ContainerBuilder;

$app->load(
    function (ContainerBuilder $container) {
        // add definitions
    }
);
```

#### Files

```php
$app->load('/path/to/services.ini');
$app->load('/path/to/services.php');
$app->load('/path/to/services.xml');
$app->load('/path/to/services.yml');
```

### Event Dispatcher

The Symfony EventDispatcher component is used to observe events triggered in
the Console component. You can find a list of observable events in Symfony's
[official documentation][].

You can register your event listeners and subscribers using tagged services.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container
    xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd"
>

  <parameters>
    <parameter key="example.listener.class">Example\Listener</parameter>
    <parameter key="example.subscriber.class">Example\Subscriber</parameter>
  </parameters>

  <services>
    <service class="%example.listener.class%" id="example.listener">
      <tag name="box.console.event.listener" event="console.command" method="doSomething"/>
    </service>
    <service class="%example.subscriber.class%" id="example.subscriber">
      <tag name="box.console.event.subscriber"/>
    </service>
  </services>

</container>
```

### Register via Tags

You can register commands and helpers by using tagged services.

```xml
<?xml version="1.0" encoding="UTF-8" ?>
<container
    xmlns="http://symfony.com/schema/dic/services"
    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
    xsi:schemaLocation="http://symfony.com/schema/dic/services http://symfony.com/schema/dic/services/services-1.0.xsd"
>

  <parameters>
    <parameter key="example.command.class">Example\Command</parameter>
    <parameter key="example.helper.class">Example\Helper</parameter>
  </parameters>

  <services>
    <service class="%example.command.class%" id="example.command">
      <tag name="box.console.command"/>
    </service>
    <service class="%example.helper.class%" id="example.helper">
      <tag name="box.console.helper"/>
    </service>
  </services>

</container>
```

[Build Status]: https://travis-ci.org/box-project/php-console.png?branch=master
[Coverage Status]: https://coveralls.io/repos/box-project/php-console/badge.png?branch=master
[Latest Stable Version]: https://poser.pugx.org/box-project/console/v/stable.png
[Latest Unstable Version]: https://poser.pugx.org/box-project/console/v/unstable.png
[Total Downloads]: https://poser.pugx.org/box-project/console/downloads.png

[official documentation]: http://symfony.com/doc/current/components/console/events.html
[DependencyInjection documentation]: http://symfony.com/doc/current/components/dependency_injection/introduction.html#setting-up-the-container-with-configuration-files
