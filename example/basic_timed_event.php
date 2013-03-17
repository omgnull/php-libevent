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
 * Example timed event
 *
 * Useful for delayed operations, database/filesystem delayed write, etc.
 */

require_once 'autoload.php';

use Libevent\Base\EventBase;
use Libevent\Event\EventInterface;
use Libevent\Event\EventTimer;

class BasicTimedEventHandler
{
    /**
     * Event call limit
     *
     * @var int
     */
    protected $limit = 10;

    /**
     * Event call counter
     *
     * @var int
     */
    protected $counter = 0;

    public function __construct($limit = 10)
    {
        if (is_int($limit) && $limit > 0) {
            $this->limit = $limit;
        }
    }

    /**
     * Event not persist and will be called only once
     *
     * @param EventInterface $event
     */
    public function basic_timed_event_01(EventInterface $event)
    {
        echo sprintf("One time event call %s. Timeout: %s.\n", $event->getName(), $event->getTimeout());
    }

    /**
     * Basic persistent timed event
     *
     * @param EventInterface $event
     */
    public function basic_timed_event_02(EventInterface $event)
    {
        echo sprintf("Basic persistent call %s. Timeout: %s.\n", $event->getName(), $event->getTimeout());
    }

    /**
     * Event break the base loop on limit
     *
     * @param EventInterface $event
     */
    public function basic_timed_event_03(EventInterface $event)
    {
        $this->counter++;
        echo sprintf("Loop breaking persistent event call %s. Timeout: %s.\n", $event->getName(), $event->getTimeout());

        if ($this->counter === $this->limit) {
            $event->getBase()->loopBreak();
            echo sprintf("Call limit exceed for event %s. Loop break.\n", $event->getName());
        }
    }
}

// Initialize event handler
$eventHandler = new BasicTimedEventHandler(3);

// Initialize event base
$base = new EventBase();

// Map of events
$map = array(
    'basic_timed_event_01' => array(
        'timeout' => 1800000,
        'persist' => false
    ),
    'basic_timed_event_02' => array(
        'timeout' => 1300000,
        'persist' => true
    ),
    'basic_timed_event_03' => array(
        'timeout' => 3000000,
        'persist' => true
    ),
);

/**
 * Prepare and enable events
 * @var EventTimer $event
 */
foreach ($map as $name => $arguments) {
    $event = new EventTimer($base, $name);
    $event
        ->prepare(array($eventHandler, $name), array(), $arguments['persist'])
        ->setTimeout($arguments['timeout'])
        ->enable();
}

// Base loop while breaks
$base->loop();