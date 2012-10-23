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
 * Abstract event
 */
abstract class AbstractBufferEvent
    implements EventBufferInterface
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

    /**
     * Creates event resource
     *
     * @throws EventException
     */
    protected abstract function initialize();

    /**
     * Remove an event from the set of monitored events.
     *
     * @see event_del
     *
     * @throws EventException if can't delete event
     *
     * @return bool
     */
    protected abstract function remove();
}