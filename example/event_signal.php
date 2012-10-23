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

require_once __DIR__ . '/../lib/include.php';

use Libevent\Event\LibeventEventInterface;
use Libevent\Base\EventBase;
use Libevent\Event\Event;

class SimpleEventHandler
{
    public function handleSigterm(LibeventEventInterface $event)
    {
        echo sprintf("This is a simple sigterm event handler. Ivoked by %s \n", $event->getName());
        echo "<----- arguments ----->";
        var_dump($event->getArguments());
        echo "<----- end arguments ----->";
        exit();
    }

    public function handleSigusr1(LibeventEventInterface $event)
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
    SIGUSR1 => new Event($this->base, 'simple_sigusr1_event'),
    SIGTERM => new Event($this->base, 'simple_sigterm_event'),
);

/**
 * Prepare events and enable
 * @var Event $event
 */
foreach ($map as $signal => $event) {
    if (SIGURG === $signal) {
        $event->prepare($signal, (EV_SIGNAL | EV_PERSIST), array($eventHandler, 'handleSigusr1'))->enable();
        continue;
    }

    $event->prepare($signal, (EV_SIGNAL | EV_PERSIST), array($eventHandler, 'handleSigterm'), array('Terminated'))->enable();
}

// Base infinite loop
while (true) {
    // We set loop to non block
    $base->loop(EVLOOP_NONBLOCK);

    // Sleep 3 seconds
    usleep(3000000);

    // Send SIGUSR1 signal
    posix_kill(posix_getpid(), SIGUSR1);

    // Send SIGTERM signal from other console window: killall event_signal.php
    posix_kill(posix_getpid(), SIGTERM);
}