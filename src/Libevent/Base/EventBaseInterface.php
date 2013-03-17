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

namespace Libevent\Base;

use Libevent\Exception\EventException;
use Libevent\Event\EventInterface;

/*
 * Interface EventBaseInterface
 */
interface EventBaseInterface
{
    /**
     * @return resource
     */
    public function getResource();

    /**
     * Check for event set in collection
     *
     * @param $name
     *
     * @return bool
     */
    public function exists($name);

    /**
     * Starts event loop for the specified event base.
     *
     * @see event_base_loop
     * @link http://php.net/manual/function.event-base-loop.php
     *
     * @param int $flags Optional parameter, which can take any combination of EVLOOP_ONCE and EVLOOP_NONBLOCK.
     *
     * @throws EventException if error
     *
     * @return int Returns 0 on success, -1 on error and 1 if no events were registered
     */
    public function loop($flags = 0);

    /**
     * Abort the active event loop immediately. The behaviour is similar to break statement.
     *
     * @see event_base_loopbreak
     *
     * @throws EventException
     *
     * @return EventBaseInterface
     */
    public function loopBreak();

    /**
     * Exit loop after a time.
     * The next event loop iteration after the given timer expires will
     * complete normally, then exit without blocking for events again.
     *
     * @link http://www.php.net/manual/function.event-base-loopexit.php
     * @see event_base_loopexit
     *
     * @param int $timeout Optional timeout parameter (in microseconds).
     * @throws EventException
     *
     * @return EventBaseInterface
     */
    public function loopExit($timeout = -1);

    /**
     * Sets the maximum priority level of the event base.
     *
     * @link http://www.php.net/manual/function.event-base-priority-init.php
     * @see event_base_priority_init
     *
     * @param int $priority
     * @throws EventException
     *
     * @return EventBaseInterface
     */
    public function setPriority($priority);

    /**
     * Free the base resource
     *
     * @link http://www.php.net/manual/function.event-base-free.php
     * @see event_base_free
     */
    public function free();

    /**
     * Add event to collection
     *
     * @param EventInterface $event
     *
     * @return bool
     */
    public function registerEvent(EventInterface $event);

    /**
     * Remove event from collection
     *
     * @param string|EventInterface $event
     *
     * @return bool
     */
    public function removeEvent($event);

    /**
     * Removes event from disabled stack and enable it
     *
     * @param string|EventInterface $event
     * @param int $events Only for buffered event required
     *
     * @return bool
     */
    public function enableEvent($event, $events = null);

    /**
     * Add event to disabled stack and disable it
     * It may mey be removed manualy
     *
     * @param string|EventInterface $event
     * @param int $events Required only for buffer event
     *
     * @return bool
     */
    public function disableEvent($event, $events = null);

    /**
     * Check if event is disabled
     *
     * @param string $name
     *
     * @return bool
     */
    public function isEventDisabled($name);

    /**
     * Return the array with disabled events
     *
     * @return array
     */
    public function getDisabledEvenets();
}