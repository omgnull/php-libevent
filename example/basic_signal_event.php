#!/usr/bin/php

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
 *
 * For example: e gathering some statistics in background app and saving it to database.
 * Writing done in periods of time, and some data will be completly lost on emergancy stop.
 * register_shutdown_function will not help. So is the simpliest way is to catch
 * SIGTERM signal and perform gracefull shutdown.
 * If we a not using kill -9 for that xD
 */

require_once 'autoload.php';

use Libevent\Base\EventBase;
use Libevent\Event\EventInterface;
use Libevent\Event\Event;

class BasicSignalEventHandler
{
    public function basic_sigterm_event(EventInterface $event)
    {
        echo sprintf("This is a simple sigterm event handler. Ivoked by %s \n", $event->getName());
        echo "----- arguments -----\n";
        var_dump($event->getArguments());
        echo "----- end arguments -----\n";
        exit();
    }

    public function basic_sigusr1_event(EventInterface $event)
    {
        echo sprintf("This is a simple sigusr1 event handler. Ivoked by %s \n", $event->getName());
    }

    public function basic_sigint_event(EventInterface $event)
    {
        echo sprintf("This is a simple sigint event handler. Ivoked by %s \n", $event->getName());
        exit();
    }
}

// Initialize event handler
$eventHandler = new BasicSignalEventHandler();

// Initialize event base
$base = new EventBase();

// Create map of events
$map = array(
    SIGUSR1 => 'basic_sigusr1_event',   // user defined commands
    SIGINT  => 'basic_sigint_event',    // very helpful on pressing ctrl+c
    SIGTERM => 'basic_sigterm_event',   // simple kill
);

/**
 * Prepare and enable events
 * @var Event $event
 */
foreach ($map as $signal => $name) {
    $event = new Event($base, $name);
    $event
        ->prepare($signal, (EV_SIGNAL | EV_PERSIST), array($eventHandler, $name), array($signal))
        ->enable();
}

// Base infinite loop
while (true) {
    // Set loop to non block
    $base->loop(EVLOOP_NONBLOCK);

    // Sleep 3 seconds
    usleep(3000000);

    /**
     * @link http://en.wikipedia.org/wiki/Kill_(command)
     */

    // Next we must send the signals, just uncomment lines with posix functions if you have pisix extension.
    // Also we can send signal from console manualy using kill command, but first need to find out php process pid
    // $ ps axu |grep php
    // and kill or
    // $ kill -s USR1 $(ps axu |grep '[b]asic_signal_event.php' |awk '{print $2}')
    // $ kill $(ps axu |grep '[b]asic_signal_event.php' |awk '{print $2}')
    // or just hit ctrl+c

    // Send SIGUSR1 signal
    // posix_kill(posix_getpid(), SIGUSR1);

    // Send SIGTERM signal
    // posix_kill(posix_getpid(), SIGTERM);
}