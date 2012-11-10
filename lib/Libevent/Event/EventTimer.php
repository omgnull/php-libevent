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
use Libevent\Base\EventBaseInterface;

/**
 * Creates timed event
 */
class EventTimer
    extends Event
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
	 * @throws EventException
	 *
	 * @return bool
	 */
	public function enable()
	{
        if ($this->enabled || !$this->check()) {
            return false;
        }

        if (false === event_timer_add($this->resource, $this->timeout)) {
			throw new EventException(sprintf('Can\'t add timer event (event_timer_add)', $this->name));
		}

        $this->enabled = true;

        return true;
	}

	/**
	 * Destroys the event and frees all the resources associated.
	 *
	 * @see event_free
	 *
	 * @return void
	 */
	public function free()
	{
		if ($this->check()) {
            if ($this->enabled) {
                $this->remove();
            }

			event_free($this->resource);
			$this->resource = null;
		}
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
     * @throws EventException
	 *
	 * @return Event
	 */
	public function prepare($callback, array $arguments = array(), $persist = false)
	{
		if ($this->enabled) {
            $this->disable();
        }

        $this->arguments = $arguments;
        $this->callback = $callback;
        $this->persist = (bool)$persist;
        $this->base->registerEvent($this);
        event_timer_set($this->resource, array($this, 'onTimer'));

        if (false === event_base_set($this->resource, $this->base->getResource())) {
            throw new EventException(sprintf('Could not set event "%s" base (event_base_set)', $this->name));
        }

		return $this;
	}

    public function onTimer()
    {
        if (!$this->enabled) {
            throw new EventException(sprintf('Could not fire timer event "%s". Event is disabled', $this->name));
        }

        if ($this->invoke() && true === $this->persist) {
            $this->enable($this->timeout);
        }
    }

    /**
     * Creates event resource
     *
     * @throws EventException
     */
    protected function initialize()
    {
        if (false === $this->resource = event_timer_new()) {
            throw new EventException(sprintf('Can\'t create new event timer "%s" resource (event_new)', $this->name));
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
            throw new EventException(sprintf('Can\'t delete event "%s" (event_timer_del)', $this->name));
        }

        return true;
    }
}