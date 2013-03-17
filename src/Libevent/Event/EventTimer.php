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

namespace Libevent\Event;

use Libevent\Exception\EventException;

/**
 * Creates timed event
 */
class EventTimer extends Event
{
    /**
     * Event persistent
     *
     * @var bool
     */
    protected $persist = false;

    /**
     * Adds an event timer to the set of monitored events.
     *
     * @see event_add
     * @link http://www.php.net/manual/en/function.event-add.php
     *
     * @param null $events Not used here
     *
     * @throws EventException
     *
     * @return bool
     */
    public function enable($events = null)
    {
        if ($this->enabled) {
            return false;
        }

        if (!$this->check()) {
            throw $this->exception('Event already freed and could not be used.');
        }

        if (false === event_timer_add($this->resource, $this->timeout)) {
            throw $this->exception('Could not add timer event (event_timer_add).');
        }
        $this->enabled = true;

        return true;
    }

    /**
     * Prepares the event to be used
     *
     * @see event_timer_set
     * @link undocumented function
     *
     * @see event_base_set
     * @link http://www.php.net/manual/en/function.event-base-set.php
     *
     * @param callable $callback Callback function to be called when the matching event occurs.
     * @param array $arguments
     * @param bool $persist
     *
     * @throws EventException|\InvalidArgumentException
     *
     * @return self
     */
    public function prepare($callback, array $arguments = array(), $persist = false)
    {
        if ($this->enabled) {
            $this->disable();
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback must be callable.');
        }

        $this->arguments = $arguments;
        $this->callback = $callback;
        $this->persist = (bool)$persist;
        $this->base->registerEvent($this);
        event_timer_set($this->resource, array($this, 'onTimer'));

        if (false === event_base_set($this->resource, $this->base->getResource())) {
            throw $this->exception('Could not set event base (event_base_set).');
        }

        return $this;
    }

    /**
     * Fire on timer event and invoke stored callback
     *
     * @throws \Libevent\Exception\EventException
     */
    public function onTimer()
    {
        if (!$this->enabled) {
            throw $this->exception('Could not fire timer event. Event is disabled.');
        }
        $this->enabled = false;
        $this->invoke();

        if (true === $this->persist) {
            $this->enable($this->timeout);
        }
    }

    /**
     * Creates event resource
     *
     * @throws EventException
     *
     * @return void
     */
    protected function initialize()
    {
        if (false === $this->resource = event_timer_new()) {
            throw $this->exception('Could not create new event timer resource (event_new).');
        }
    }

    /**
     * Remove an event from the set of monitored events.
     *
     * @see event_del
     *
     * @throws EventException if can't delete event
     *
     * @return bool
     */
    protected function remove()
    {
        if (false === event_timer_del($this->resource)) {
            throw $this->exception('Could not delete event (event_timer_del).');
        }

        return true;
    }

    /**
     * Overridable function to determine event type
     *
     * @return int
     */
    protected function getExceptionCode()
    {
        return EventException::EVENT_TIMER_EXCEPTION;
    }
}