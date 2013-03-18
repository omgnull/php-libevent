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

/*
 * Interface EventInterface
 */
interface EventInterface
{
    /**
     * Gets the event name
     *
     * @return string
     */
    public function getName();

    /**
     * Get event base
     *
     * @return EventBaseInterface
     */
    public function getBase();

    /**
     * Gets event timeout
     *
     * @return mixed
     */
    public function getTimeout();

    /**
     * Gets the event arguments
     * @return array
     */
    public function getArguments();

    /**
     * Disables event
     *
     * @param null|integer $events
     * @param bool $baseCall
     *
     * @return void
     */
    public function disable($events = null, $baseCall = false);

    /**
     * Enables event
     *
     * @param null|integer $events
     *
     * @throws EventException
     *
     * @return bool
     */
    public function enable($events = null);

    /**
     * Manually invoke the event callback
     *
     * @return void
     */
    public function invoke();

    /**
     * Free event resource
     *
     * @param bool $baseCall
     *
     * @return void
     */
    public function free($baseCall = false);

    /**
     * Checks for active event resource.
     *
     * @return bool
     */
    public function check();
}