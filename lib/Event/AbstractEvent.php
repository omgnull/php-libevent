<?php

namespace Libevent\Event;


abstract class AbstractEvent
    implements EventInterface
{
    /**
     * Event id
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
     * Event timeout
     *
     * @var integer
     */
    protected $timeout;

    /**
     * @var EventBaseInterface
     */
    protected $base;

    /**
     * @var bool
     */
    protected $enabled;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
