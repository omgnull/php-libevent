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
 * Interface EventBufferInterface
 */
interface EventBufferInterface
    extends LibeventEventInterface
{
    /**
     * Disables event
     *
     * @param int $events
     *
     * @return void
     */
    public function disable($events);

    /**
     * Enables event
     *
     * @param int $events
     *
     * @throws EventException
     *
     * @return void
     */
    public function enable($events);
}