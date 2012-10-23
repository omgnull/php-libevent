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
    extends LibeventEventInterface
{
    /**
     * Disables event
     *
     * @return void
     */
    public function disable();

    /**
     * Enables event
     *
     * @throws EventException
     *
     * @return void
     */
    public function enable();

    /**
     * Manually invoke the event callback
     *
     * @return bool
     */
    public function invoke();
}