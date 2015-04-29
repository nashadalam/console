<?php

namespace Box\Component\Console\Tests\Test;

use Symfony\Component\Console\Event\ConsoleTerminateEvent;

/**
 * A test event listener.
 *
 * @author Kevin Herrera <kevin@herrera.io>
 */
class EventListener
{
    /**
     * Echoes a simple message.
     *
     * @param ConsoleTerminateEvent $event The event arguments.
     */
    public function sayHello(ConsoleTerminateEvent $event)
    {
        $event->getOutput()->writeln('Hello, world!');
    }
}
