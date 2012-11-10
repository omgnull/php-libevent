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
     * Gets the event arguments
     * @return array
     */
    public function getArguments();

    /**
     * Disables event
     *
     * @param null|integer $events
     *
     * @return void
     */
    public function disable($events = null);

    /**
     * Enables event
     *
     * @param null|integer $events
     *
     * @throws EventException
     *
     * @return void
     */
    public function enable($events = null);

    /**
     * Manually invoke the event callback
     *
     * @return bool
     */
    public function invoke();

    /**
     * Free event resource
     *
     * @return void
     */
    public function free();
}