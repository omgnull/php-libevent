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
 * Creates event
 */
class Event
    extends AbstractEvent
{
    /**
     * Event timeout in microseconds
     *
     * @var integer
     */
    protected $timeout = -1;

    /**
     * @var resource|integer
     */
    protected $fd;

	/**
	 * Adds an event to the set of monitored events.
     *
	 * @see event_add
	 * @link http://www.php.net/manual/en/function.event-add.php
     *
	 * @throws EventException if can't add event
	 *
	 * @return Event
	 */
	public function enable()
	{
        if ($this->enabled || !$this->check()) {
            return false;
        }

        if (false === event_add($this->resource, $this->timeout)) {
			throw new EventException(sprintf('Can\'t add event "%s" (event_add)', $this->name));
		}
        $this->enabled = true;

		return $this;
	}

    /**
     * Set event timeout in microseconds
     *
     * @param $timeout
     *
     * @return EventInterface
     */
    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;

        return $this;
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
	 * @see event_set
     * @link http://www.php.net/manual/en/function.event-set.php
     *
     * @see event_base_set
     * @link http://www.php.net/manual/en/function.event-base-set.php
     *
	 * @param resource|mixed $fd Valid PHP stream resource. The stream must be castable to file descriptor,
     * so you most likely won't be able to use any of filtered streams.
	 * @param int $events A set of flags indicating the desired event, can be EV_TIMEOUT, EV_READ, EV_WRITE and EV_SIGNAL.
     * The additional flag EV_PERSIST makes the event to persist until {@link event_del}() is
     * called, otherwise the callback is invoked only once.
	 * @param callable $callback Callback function to be called when the matching event occurs.
	 * @param array $arguments
     *
     * @throws EventException
	 *
	 * @return Event
	 */
	public function prepare($fd, $events, $callback, array $arguments = array())
	{
		if ($this->enabled) {
            $this->disable();
        }

        if (!event_set($this->resource, $fd, $events, $callback, $this)) {
			throw new EventException(sprintf('Can\'t prepare event (event_set)', $this->name));
		}

        if (false === event_base_set($this->resource, $this->base->getResource())) {
            throw new EventException(sprintf('Could not set event "%s" base (event_base_set)', $this->name));
        }

        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->base->registerEvent($this);

		return $this;
	}

    /**
     * Creates event resource
     *
     * @throws EventException
     */
    protected function initialize()
    {
        if (false === $this->resource = event_new()) {
            throw new EventException('Can\'t create new event resourse (event_new)', 1);
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
        if (false === event_del($this->resource)) {
            throw new EventException("Can't delete event (event_del)", 1);
        }

        return true;
    }
}