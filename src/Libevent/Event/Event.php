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
     * Prepare event flag
     *
     * @var bool
     */
    protected $prepared     = false;

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
    protected $timeout      = -1;

    /**
     * Event persistent
     *
     * @var bool
     */
    protected $persist      = false;

    /**
     * Creates a new event instance
     *
     * @param EventBaseInterface $base
     * @param string $name
     * @param bool $entropy
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(EventBaseInterface $base, $name = null, $entropy = false)
    {
        $this->entropy = $entropy;

        if (!is_scalar($name)) {
            $name = $this->generateName();
        }

        if ($base->exists($name)) {
            throw new \InvalidArgumentException('Could not create new event. Event with same name already exists.');
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
        $this->name = $this->generateName();
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
     * Gets event base
     *
     * @return EventBaseInterface
     */
    public function getBase()
    {
        return $this->base;
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
     * @param null $events For compatibility only, used in buffered event
     * @param bool $baseCall To prevent loop
     *
     * @return void
     */
    public function disable($events = null, $baseCall = false)
    {
        if ($this->enabled) {
            $this->remove($baseCall);
            $this->enabled = false;
        }
    }

    /**
     * Manually invoke the event callback
     *
     * @throws EventException on invalid callback
     *
     * @return void
     */
    public function invoke()
    {
        if (!$this->enabled) {
            throw $this->exception('Event is disabled.');
        }
        call_user_func($this->callback, $this);

        if (!$this->persist) {
            $this->disable();
        }
    }

    /**
     * Adds an event to the set of monitored events.
     *
     * @see event_add
     * @link http://www.php.net/manual/en/function.event-add.php
     *
     * @param null $events For compatibility only, used in buffered event
     *
     * @throws EventException if can't add event
     *
     * @return bool
     */
    public function enable($events = null)
    {
        if ($this->enabled || !$this->prepared) {
            return false;
        }

        if (!$this->check()) {
            throw $this->exception('Event already freed and could not be used.');
        }

        if (false === event_add($this->resource, $this->timeout)) {
            throw $this->exception('Could not add event (event_add).');
        }
        $this->base->enableEvent($this->name);
        $this->enabled = true;

        return true;
    }

    /**
     * Set event timeout in microseconds
     *
     * @param $timeout
     *
     * @throws \InvalidArgumentException
     *
     * @return EventInterface
     */
    public function setTimeout($timeout)
    {
        if (!is_int($timeout)) {
            throw new \InvalidArgumentException('Timeout must be an interger in microseconds');
        }

        $this->timeout = $timeout;

        return $this;
    }

    /**
     * Gets event timeout
     *
     * @return int
     */
    public function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Destroys the event and frees all the resources associated.
     * @see event_free
     *
     * @param bool $baseCall Argument for prevent infinite loop
     *
     * @return void
     */
    public function free($baseCall = false)
    {
        if ($this->check()) {
            if ($this->enabled) {
                $this->remove($baseCall);
            }
            event_free($this->resource);
            // Resource must be nulled before removeEvent to prevent infinite loop
            $this->resource = null;

            if (false === $baseCall) {
                $this->base->removeEvent($this->name);
            }
            $this->base = null;
            $this->arguments = array();
            $this->callback = null;
            $this->prepared = false;
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
     * @throws EventException|\InvalidArgumentException
     *
     * @return Event
     */
    public function prepare($fd, $events, $callback, array $arguments = array())
    {
        if ($this->enabled) {
            $this->disable();
        }

        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Callback must be callable.');
        }

        if (!event_set($this->resource, $fd, $events, array($this, 'invoke'), $this)) {
            throw $this->exception('Could not prepare event (event_set).');
        }

        if ($events & EV_PERSIST) {
            $this->persist = true;
        }

        if (false === event_base_set($this->resource, $this->base->getResource())) {
            throw $this->exception('Could not set event base (event_base_set).');
        }
        $this->callback = $callback;
        $this->arguments = $arguments;
        $this->prepared = true;
        $this->base->registerEvent($this);

        return $this;
    }

    /**
     * Checks for active event resource.
     *
     * @return bool
     */
    public function check()
    {
        return is_resource($this->resource);
    }

    /**
     * Creates event resource
     *
     * @throws EventException
     */
    protected function initialize()
    {
        if (false === $this->resource = event_new()) {
            throw $this->exception('Could not create new event resourse (event_new).');
        }
    }

    /**
     * Remove an event from the set of monitored events.
     *
     * @see event_del
     *
     * @param bool $baseCall To prevent infinite loop
     *
     * @throws EventException if can't delete event
     *
     * @return void
     */
    protected function remove($baseCall)
    {
        if (false === event_del($this->resource)) {
            throw $this->exception('Could not delete event (event_del).');
        }
        if (false === $baseCall) {
            $this->base->disableEvent($this->name);
        }
    }

    /**
     * Generate unique name if not set
     *
     * @return string
     */
    protected function generateName()
    {
        return uniqid(mt_rand(10000, 1000000), $this->entropy);
    }

    /**
     * Overridable function to determine event type
     *
     * @return int
     */
    protected function getExceptionCode()
    {
        return EventException::EVENT_EXCEPTION;
    }

    /**
     * Prepare exception for specified event (can be overrided)
     * For php >= 5.4 will be nice
     * return (new EventException($message, EventException::EVENT_EXCEPTION))->setEvent($this);
     *
     * @param string $message
     *
     * @return EventException
     */
    protected function exception($message)
    {
        $exception = new EventException($message, $this->getExceptionCode());

        return $exception->setEvent($this);
    }
}