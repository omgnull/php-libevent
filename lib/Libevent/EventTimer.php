<?php

namespace Libevent;

/**
 * Libevent php-oop wrapper for simple event timer
 * @link http://www.wangafu.net/~nickm/libevent-book/
 *
 * @uses libevent
 */
class EventTimer
    extends AbstractEvent
{
    /**
     * Event id
     *
     * @var string
     */
    protected $name;

    /**
     * Event callback
     *
     * @var callable
     */
    protected $callback;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var EventBaseInterface
     */
    protected $base;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @var bool
     */
    protected $persist = false;

    /**
	 * Creates a new event resource.
	 *
	 * @see event_new
	 * @param string $name
	 * @param EventBaseInterface $base
     *
     * @throws EventException
	 */
	public function __construct($name, EventBaseInterface $base)
	{
		if (!is_scalar($name)) {
            $name = uniqid(rand(100000, 1000000));
        }

        if ($base->exists($name)) {
            throw new EventException(sprintf('Can\'t create new buffered event. Event with same name "%s" already exists.', $name));
        }

        $this->name = $name;
        $this->base = $base;
        $this->initialize();
	}

    public function __destruct()
    {
        $this->free();
    }

	/**
	 * Adds an event to the set of monitored events.
     *
     * @param int $timeout Optional timeout (in microseconds).
	 * @see event_add
	 * @link http://www.php.net/manual/en/function.event-add.php
     *
	 * @throws EventException if can't add event
	 *
	 * @return Event
	 */
	public function enable($timeout)
	{
        if ($this->enabled || !$this->check()) {
            //return false;
        }

        if (false === event_timer_add($this->resource, $timeout)) {
			throw new EventException('Can\'t add event (event_add)');
		}

        $this->enabled = true;
        $this->timeout = $timeout;

		return $this;
	}

    /**
     * Remove event from set of monitored events
     *
     * @param bool $reInitialize
     *
     * @return bool
     */
    public function disable($reInitialize = false)
    {
        if (!$this->enabled || !$this->check($reInitialize)) {
            return false;
        }

        $this->remove();
        $this->enabled = false;

        return true;
    }

	/**
	 * Destroys the event and frees all the resources associated.
	 *
	 * @see event_free
	 *
	 * @return Event
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

		return $this;
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
	 * @param array $arg
	 * @param bool $persist
     *
     * @throws EventException
	 *
	 * @return Event
	 */
	public function prepare($callback, array $arg = array(), $persist = false)
	{
		if ($this->enabled) {
            $this->disable();
        }

        event_timer_set($this->resource, array($this, 'onTimer'));
        if (false === event_base_set($this->resource, $this->base->getResource())) {
            throw new EventException(sprintf('Could not set event "%s" base (event_base_set)', $this->name));
        }

        array_unshift($arg, array($this));
        $this->arguments = $arg;
        $this->callback = $callback;
        $this->persist = (bool)$persist;
        $this->base->registerEvent($this);

		return $this;
	}

    public function onTimer()
    {
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
        if (false === event_timer_del($this->resource)) {
            throw new EventException("Can't delete event (event_del)", 1);
        }

        return true;
    }

    /**
     * Checks for event resource.
     *
     * @param bool $reInitialize
     *
     * @throws EventException if resource is already freed
     *
     * @return bool
     */
    protected function check($reInitialize = false)
    {
        $status = is_resource($this->resource);
        if (false === $status && true === $reInitialize) {
            $this->initialize();
        }

        return $status;
    }

    /**
     * Manualy invoke the event callback
     *
     * @return bool
     */
    public function invoke()
    {
        if (!is_callable($this->callback)) {
            return false;
        }
        call_user_func($this->callback, $this->arguments);

        return true;
    }
}