<?php

/**
 * PHP-OOP wrapper for libevent functionality
 *
 * @author Igor Makarov <doomsmile@gmail.com>
 *
 * @link http://www.wangafu.net/~nickm/libevent-book/
 * @link http://php.net/manual/en/ref.libevent.php
 * @link http://pecl.php.net/package/libevent
 *
 * @uses libevent
 */

/**
 * Example signal event
 */

require_once 'include.php';

use Libevent\Base\EventBase;
use Libevent\Event\EventInterface;
use Libevent\Event\Event;

class SimpleEventHandler
{
    public function simple_sigterm_event(EventInterface $event)
    {
        echo sprintf("This is a simple sigterm event handler. Ivoked by %s \n", $event->getName());
        echo "<----- arguments ----->";
        var_dump($event->getArguments());
        echo "<----- end arguments ----->";
        exit();
    }

    public function simple_sigusr1_event(EventInterface $event)
    {
        echo sprintf("This is a simple sigterm event handler. Ivoked by %s \n", $event->getName());
    }
}

// Initialize event handler
$eventHandler = new SimpleEventHandler();

// Initialize event base
$base = new EventBase();

// Create map of events
$map = array(
    SIGUSR1 => 'simple_sigusr1_event',
    SIGTERM => 'simple_sigterm_event',
);

/**
 * Prepare events and enable
 * @var Event $event
 */
foreach ($map as $signal => $name) {
    $event = new Event($base, $name);
    $event
        ->prepare($signal, (EV_SIGNAL | EV_PERSIST), array($eventHandler, $name), array($signal))
        ->enable()
    ;
}

// Base infinite loop
while (true) {
    // Set loop to non block
    $base->loop(EVLOOP_NONBLOCK);

    // Sleep 3 seconds
    usleep(3000000);

    // Send SIGUSR1 signal
    // posix_kill(posix_getpid(), SIGUSR1);

    // Send SIGTERM signal
    // posix_kill(posix_getpid(), SIGTERM);
}