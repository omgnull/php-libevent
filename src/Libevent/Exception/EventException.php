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

namespace Libevent\Exception;

use Libevent\Base\EventBaseInterface;
use Libevent\Event\EventInterface;

/**
 * Class EventException
 *
 * Used to catch event specific specific exceptions
 */
class EventException extends \Exception
{
    /**
     * Constants to determine error object
     */
    const EVENT_EXCEPTION           = 100;
    const EVENT_TIMER_EXCEPTION     = 101;
    const EVENT_BUFFER_EXCEPTION    = 102;
    const BASE_EXCEPTION            = 200;

    /**
     * Event base if cause exception
     *
     * @var EventBaseInterface
     */
    protected $base;

    /**
     * Event if cause exception
     *
     * @var
     */
    protected $event;

    /**
     * Set base
     *
     * @param EventBaseInterface $base
     *
     * @return EventException
     */
    public function setBase(EventBaseInterface $base)
    {
        $this->base = $base;

        return $this;
    }

    /**
     * Gets base or null
     *
     * @return null|EventBaseInterface
     */
    public function getBase()
    {
        return $this->base;
    }

    /**
     * Set event
     *
     * @param EventInterface $event
     *
     * @return EventException
     */
    public function setEvent(EventInterface $event)
    {
        $this->event = $event;

        return $this;
    }

    /**
     * Gets event or null
     *
     * @return null|EventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }
}