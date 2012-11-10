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
class Event implements EventInterface
{
    /**
     * Event unique name
     *
     * @var string
     */
    protected $name;

    /**
     * Event resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Event base instance
     *
     * @var EventBaseInterface
     */
    protected $base;

    /**
     * Event status
     *
     * @var bool
     */
    protected $enabled      = false;

    /**
     * Event callback arguments
     *
     * @var array
     */
    protected $arguments    = array();

    /**
     * Event callback function
     *
     * @var callable
     */
    protected $callback;

    /**
     * If set to true, uniqid will add additional
     * entropy (using the combined linear congruential generator) at the end
     * of the return value, which should make the results more unique.
     *
     * @var bool
     */
    protected $entropy      = false;

    /**
     * Event timeout in microseconds
     *
     * @var integer
     */
    protected $timeout = -1;

    /**
     * Creates a new event instance
     *
     * @param EventBaseInterface $base
     * @param string $name
     * @param bool $entropy
     *
     * @throws EventException
     */
    public function __construct(EventBaseInterface $base, $name = null, $entropy = false)
    {
        $this->entropy = $entropy;
        if (!is_scalar($name)) {
            $name = $this->generateName();
        }

        if ($base->exists($name)) {
            throw new EventException(sprintf('Can\'t create new event. Event with same name "%s" already exists.', $name));
        }

        $this->name = $name;
        $this->base = $base;
        $this->initialize();
    }

    /**
     * Destroy event resource
     */
    public function __destruct()
    {
        $this->free();
    }

    /**
     * Clone implementation
     */
    public function __clone()
    {
        $this->name = $this->generateName($this->entropy);
        $this->enabled = false;
        $this->initialize();
    }

    /**
     * Gets the event name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Gets the event arguments
     *
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Disables event
     *
     * @return void
     */
    public function disable()
    {
        if ($this->enabled) {
            $this->remove();
            $this->enabled = false;
        }
    }

    /**
     * Manually invoke the event callback
     *
     * @return bool
     */
    public function invoke()
    {
        if (!is_callable($this->callback)) {
            return false;
        }
        call_user_func($this->callback, $this);

        return true;
    }

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

    /**
     * Generate unique name if not set
     *
     * @return string
     */
    protected function generateName()
    {
        return uniqid(rand(100000, 1000000), $this->entropy);
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
}