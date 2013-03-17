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
 * Creates buffered event
 *
 * Constants EVBUFFER_READ, EVBUFFER_WRITE, EVBUFFER_EOF, EVBUFFER_ERROR, EVBUFFER_TIMEOUT
 */
class EventBuffer extends Event
{
    /**
     * Defaults
     */
    const DEFAULT_PRIORITY          = 10;
    const DEFAULT_TIMEOUT_READ      = 30;
    const DEFAULT_TIMEOUT_WRITE     = 30;

    /**
     * Watermarks
     */
    const DEFAULT_LOWMARK           = 1;
    const DEFAULT_HIGHMARK          = 0xffffff;

    /**
     * Creates a new buffered event resource.
     *
     * @see event_buffer_new
     * @link http://www.php.net/manual/en/function.event-buffer-new.php
     *
     * @param resource $stream Valid PHP stream resource. Must be castable to file descriptor
     * @param callable|null $readcb Callback to invoke where there is data to read, or NULL if no callback is desired
     * @param callable|null $writecb Callback to invoke where the descriptor is ready for writing, or NULL if no callback is desired
     * @param callable $errorcb Callback to invoke where there is an error on the descriptor, cannot be NULL
     * @param array $arguments An arguments that will be passed to each of the callbacks
     *
     * @throws EventException|\InvalidArgumentException
     *
     * @return EventBuffer
     */
    public function prepare($stream, $readcb = null, $writecb = null, $errorcb = null, array $arguments = array())
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Could not prepare buffered event. Invalid fd.');
        }

        if (false === $this->resource = event_buffer_new($stream, $readcb, $writecb, $errorcb, $this)) {
            throw $this->exception('Could not create new buffered event resourse (event_buffer_new).');
        }

        if (!event_buffer_base_set($this->resource, $this->base->getResource())) {
            throw $this->exception('Could not set buffered event base (event_buffer_base_set).');
        }
        $this->arguments = $arguments;
        $this->base->registerEvent($this);

        return $this;
    }

    /**
     * Disables buffered event
     *
     * @see event_buffer_disable
     *
     * @throws EventException|\InvalidArgumentException
     *
     * @param int $events Any combination of EV_READ and EV_WRITE.
     *
     * @return EventBuffer
     */
    public function disable($events = null)
    {
        if (null === $events) {
            throw new \InvalidArgumentException(
                    'Events to disable must be specified for.
                    Any combination of EV_READ and EV_WRITE (event_buffer_disable).'
            );
        }

        if (!event_buffer_disable($this->resource, $events)) {
            throw $this->exception('Could not disable buffered event (event_buffer_disable).');
        }
        
        return $this;
    }

    /**
     * Enables buffered event
     *
     * @see event_buffer_enable
     *
     * @throws EventException|\InvalidArgumentException
     *
     * @param int $events Any combination of EV_READ and EV_WRITE.
     *
     * @return EventBuffer
     */
    public function enable($events = null)
    {
        if (null === $events) {
            throw new \InvalidArgumentException(
                'Events to enable must be specified for "%s".
                 Any combination of EV_READ and EV_WRITE (event_buffer_disable).'
            );
        }

        if (!event_buffer_enable($this->resource, $events)) {
            throw $this->exception('Could not enable buffered event (event_buffer_enable).');
        }

        return $this;
    }

    /**
     * Destroys the buffered event and frees all the resources associated.
     *
     * @link http://www.php.net/manual/function.event-buffer-free.php
     * @see event_buffer_free
     *
     * @param bool $baseCall Argument for prevent infinite loop
     *
     * @throws EventException
     *
     * @return EventBuffer
     */
    public function free($baseCall = false)
    {
        if ($this->check()) {
            event_buffer_free($this->resource);
            $this->resource = null;

            if (false === $baseCall) {
                $this->base->removeEvent($this->name);
            }
            $this->base = null;
            $this->arguments = array();
        }

        return $this;
    }


    /**
     * Reads data from the input buffer of the buffered event.
     *
     * @see event_buffer_read
     *
     * @param int $dataSize Data size in bytes.
     *
     * @return string|bool Data from buffer or FALSE
     */
    public function read($dataSize)
    {
        return event_buffer_read($this->resource, $dataSize);
    }

    /**
     * Writes data to the specified buffered event.
     *
     * @see event_buffer_write
     *
     * @throws EventException
     *
     * @param string $data The data to be written.
     * @param integer $dataSize Optional size parameter. Writes all the data by default
     *
     * @return EventBuffer
     */
    public function write($data, $dataSize = -1)
    {
        if (!event_buffer_write($this->resource, $data, $dataSize)) {
            throw $this->exception('Could not write data to the buffered event (event_buffer_write).');
        }
        
        return $this;
    }


    /**
     * Changes the stream on which the buffered event operates.
     *
     * @see event_buffer_fd_set
     *
     * @throws \InvalidArgumentException
     *
     * @param resource $stream Valid PHP stream, must be castable to file descriptor.
     *
     * @return EventBuffer
     */
    public function setStream($stream)
    {
        if (!is_resource($stream)) {
            throw new \InvalidArgumentException('Invalid fd set for buffer event.');
        }
        event_buffer_fd_set($this->resource, $stream);

        return $this;
    }

    /**
     * Sets or changes existing callbacks for the buffered event.
     *
     * @see event_buffer_set_callback
     *
     * @throws EventException
     *
     * @param callable|null $readcb 
     * Callback to invoke where there is data to read, or NULL if no callback is desired.
     * function(resource $buf, array $args(CLibEventBuffer $e, mixed $arg)){}
     * 
     * @param callable|null $writecb 
     * Callback to invoke where the descriptor is ready for writing, or NULL if no callback is desired.
     * function(resource $buf, array $args(CLibEventBuffer $e, mixed $arg)){}
     * 
     * @param callable $errorcb 
     * Callback to invoke where there is an error on the descriptor, cannot be NULL.
     * function(resource $buf, int $what, array $args(CLibEventBuffer $e, mixed $arg)){}
     * 
     * @param mixed $arg [optional] 
     * An argument that will be passed to each of the callbacks.
     * 
     *
     * @return EventBuffer
     */
    public function setCallback($readcb, $writecb, $errorcb, $arg = null)
    {
        if (!event_buffer_set_callback($this->resource, $readcb, $writecb, $errorcb, array($this, $arg))) {
            throw $this->exception('Could not set buffered event callbacks (event_buffer_set_callback).');
        }

        return $this;
    }

    /**
     * Sets the read and write timeouts for the specified buffered event.
     *
     * @see event_buffer_timeout_set
     *
     * @throws EventException
     *
     * @param int $read_timeout  Read timeout (in seconds).
     * @param int $write_timeout Write timeout (in seconds).
     *
     * @return EventBuffer
     */
    public function setTimout($read_timeout = self::DEFAULT_TIMEOUT_READ, $write_timeout = self::DEFAULT_TIMEOUT_WRITE)
    {
        event_buffer_timeout_set($this->resource, $read_timeout, $write_timeout);

        return $this;
    }

    /**
     * Set the marks for read and write events.
     *
     * Libevent does not invoke read callback unless there is at least lowmark
     * bytes in the input buffer; if the read buffer is beyond the highmark,
     * reading is stopped. On output, the write callback is invoked whenever
     * the buffered data falls below the lowmark
     *
     * @see event_buffer_timeout_set
     *
     * @throws EventException
     *
     * @param int $events   Any combination of EV_READ and EV_WRITE.
     * @param int $lowmark  Low watermark.
     * @param int $highmark High watermark.
     *
     * @return EventBuffer
     */
    public function setWatermark($events, $lowmark = self::DEFAULT_LOWMARK, $highmark = self::DEFAULT_HIGHMARK)
    {
        event_buffer_watermark_set($this->resource, $events, $lowmark, $highmark);

        return $this;
    }

    /**
     * Assign a priority to a buffered event.
     *
     * @see event_buffer_priority_set
     *
     * @param int $value
     * Priority level. Cannot be less than zero and cannot exceed
     * maximum priority level of the event base (see {@link event_base_priority_init}()).
     * 
     *
     * @throws EventException
     *
     * @return EventBuffer
     */
    public function setPriority($value = self::DEFAULT_PRIORITY)
    {
        if (!event_buffer_priority_set($this->resource, $value)) {
            throw $this->exception('Could not set buffered event priority (event_buffer_priority_set).');
        }

        return $this;
    }

    /**
     * Overridable function to determine event type
     *
     * @return int
     */
    protected function getExceptionCode()
    {
        return EventException::EVENT_BUFFER_EXCEPTION;
    }
}