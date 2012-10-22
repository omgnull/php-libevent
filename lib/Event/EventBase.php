<?php

namespace Libevent\Event;

/**
 * LibEventBase resourse wrapper
 *
 * @link http://www.wangafu.net/~nickm/libevent-book/
 * @uses libevent
 *
 */
class EventBase
    implements EventBaseInterface
{
	/**
	 * Default priority
	 */
	const DEFAULT_PRIORITY = 30;

	/**
	 * Event base resource
	 *
	 * @var resource
	 */
	private $resource;

	/**
	 * Timers
	 *
	 * @var array[]
	 */
    private $events = array();

	/**
	 * Construct new event base
	 *
	 * @see event_base_new
     * @link http://www.php.net/manual/function.event-base-new.php
     * @param int $priority
	 * @throws EventException
	 */
	public function __construct($priority = self::DEFAULT_PRIORITY)
	{
		if (false === $this->resource = event_base_new()) {
			throw new EventException('Could not create event base resourse.');
		}

        $this->setPriority((int)$priority);
	}

	/**
	 * Destroys the specified event base and all the events associated.
     * Empty collection
     *
     * @link http://www.php.net/manual/function.event-base-free.php
     * @see event_base_free
     *
     * @link http://www.php.net/manual/function.event-free.php
     * @see event_free
     *
     * @return void
	 */
	public function __destruct()
	{
        /**
         * @var EventInterface $event
         */
        foreach ($this->events as $event) {
            $event->free();
        }

        $this->free();
	}

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Check for event set in collection
     *
     * @param $eventId
     *
     * @return bool
     */
    public function exists($eventId)
    {
        return isset($this->events[$eventId]);
    }

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
	public function loop($flags = 0)
	{
        if (-1 === ($status = event_base_loop($this->resource, $flags))) {
			throw new EventException('Can\'t start base loop (event_base_loop)');
		}

		return $status;
	}

	/**
	 * Abort the active event loop immediately. The behaviour is similar to break statement.
	 *
	 * @see event_base_loopbreak
	 *
	 * @throws EventException
	 *
	 * @return EventBaseInterface
	 */
	public function loopBreak()
	{
		if (false === event_base_loopbreak($this->resource)) {
			throw new EventException('Can\'t break loop (event_base_loopbreak)');
		}

		return $this;
	}

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
	public function loopExit($timeout = -1)
	{
		if (false === event_base_loopexit($this->resource, $timeout)) {
			throw new EventException('Could not set loop exit timeout.');
		}

		return $this;
	}

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
	public function setPriority($priority)
	{
		if (false === event_base_priority_init($this->resource, $priority)) {
			throw new EventException(sprintf(
                'Can\'t set the maximum priority level of the event base to %s (event_base_priority_init)', $priority
            ));
		}

		return $this;
	}

    /**
     * Free the base resource
     *
     * @link http://www.php.net/manual/function.event-base-free.php
     * @see event_base_free
     */
    public function free()
    {
        if (is_resource($this->resource)) {
            event_base_free($this->resource);
        }
    }

    /**
     * Add event to collection
     *
     * @param EventInterface $event
     *
     * @return bool
     */
    public function registerEvent(EventInterface $event)
    {
        $name = $event->getName();
        if ($this->exists($name)) {
            return false;
        }

        $this->events[$name] = $event;

        return true;
    }

    /**
     * Remove event from collection
     *
     * @param string|EventInterface $event
     *
     * @return bool
     */
    public function removeEvent($event)
    {
        $name = $event instanceof EventInterface ? $event->getName() : $event;

        if ($this->exists($name)) {
            return false;
        }

        unset($this->events[$name]);

        return true;
    }
}