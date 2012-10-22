<?php

namespace Libevent\Event;

/*
 * Interface EventInterace
 */
interface EventInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * Destroys the event and frees all the resources associated.
     *
     * @see event_free
     *
     * @return EventInterface
     */
    public function free();
}