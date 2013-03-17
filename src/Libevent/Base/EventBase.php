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

/**
 * EventBase resourse wrapper
 */
class EventBase implements EventBaseInterface
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
     * Registered events
     *
     * @var array
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
            throw $this->exception('Could not create event base resourse (event_base_new).');
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
        $this->free();
    }

    /**
     * Gets base resource
     *
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Check for event set in collection
     *
     * @param string $name
     *
     * @return bool
     */
    public function exists($name)
    {
        return isset($this->events[$name]);
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
            throw $this->exception('Could not start base loop (event_base_loop)');
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
            throw $this->exception('Could not break loop (event_base_loopbreak)');
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
            throw $this->exception('Could not set loop exit timeout.');
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
            throw $this->exception('Could not set the maximum priority level of the event base (event_base_priority_init)');
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
        /**
         * @var EventInterface $event
         */
        foreach ($this->events as $event) {
            $this->freeEvent($event->getName(), true);
        }

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
        $name = $event instanceof EventInterface ?
            $event->getName() : $event;

        if (!$this->exists($name)) {
            return false;
        }
        $this->freeEvent($name);

        return true;
    }

    /**
     * Iternal remove
     *
     * @param string $name
     * @param bool $baseCall Argument for prevent infinite loop
     *
     * @return void
     */
    protected function freeEvent($name, $baseCall = false)
    {
        /**
         * @var $event EventInterface
         * Force free the event, unset did not call __destruct if there are other links on event
        */
        $event = $this->events[$name];

        if ($event->check()) {
            $event->free($baseCall);
        }
        unset($this->events[$name]);
    }

    /**
     * Prepare exception for specified base
     * For php >= 5.4 will be nice
     * return (new EventException($message, EventException::BASE_EXCEPTION))->setBase($this);
     *
     * @param string $message
     *
     * @return EventException
     */
    protected function exception($message)
    {
        $exception = new EventException($message, EventException::BASE_EXCEPTION);

        return $exception->setBase($this);
    }
}