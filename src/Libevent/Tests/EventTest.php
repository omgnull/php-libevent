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

namespace Libevent\Tests;


use Libevent\Exception\EventException;
use Libevent\Base\EventBaseInterface;
use Libevent\Base\EventBase;
use Libevent\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return Event
     */
    public function testEventInit()
    {
        $base = new EventBase();
        $event = new Event($base, 'test');
        $event
            ->prepare(SIGUSR1, EV_SIGNAL | EV_PERSIST, array($this, 'eventHandler'), array('test_event'))
            ->enable();

        return $event;
    }

    /**
     * @param Event $event
     * @depends testEventInit
     *
     * @return Event
     */
    public function testEventVariables(Event $event)
    {
        $this->assertTrue($event->getName() === 'test');
        $this->assertTrue($event->check());
        $this->assertTrue($event->getBase() instanceof EventBaseInterface);
        $this->assertTrue($event->getBase()->exists($event->getName()));

        return $event;
    }

    /**
     * @param Event $event
     * @depends testEventVariables
     *
     * @return Event
     */
    public function testEventManualInvoke(Event $event)
    {
        $this->expectOutputString("test_event");
        $event->invoke();

        return $event;
    }

    /**
     * @param Event $event
     * @depends testEventManualInvoke
     *
     * @return Event
     */
    public function testEventBaseInvoke(Event $event)
    {
        $this->expectOutputString("test_event");
        posix_kill(posix_getpid(), SIGUSR1);
        $event->getBase()->loop(EVLOOP_NONBLOCK);

        return $event;
    }

    /**
     * @param Event $event
     * @depends testEventManualInvoke
     *
     * @return Event
     */
    public function testEventDisable(Event $event)
    {
        $this->assertEquals(0, count($event->getBase()->getDisabledEvenets()));
        $event->disable();
        $this->assertEquals(1, count($event->getBase()->getDisabledEvenets()));
        $this->assertTrue($event->getBase()->isEventDisabled($event->getName()));

        return $event;
    }

    /**
     * @param Event $event
     * @depends testEventBaseInvoke
     *
     * @return array
     */
    public function testEventClone(Event $event)
    {
        $clone = clone $event;
        $this->assertTrue($clone->getName() !== $event->getName());
        $this->assertTrue($clone->getBase() === $event->getBase());
        $this->assertEquals(1, count($clone->getArguments()));
        $this->assertEquals(1, count($clone->getBase()->getDisabledEvenets()));
        $this->assertFalse($clone->getBase()->exists($clone->getName()));

        return array($event, $clone);
    }

    /**
     * @param array $events
     * @depends testEventClone
     */
    public function testFreeEvents(array $events)
    {
        /**
         * @var Event $event
         * @var Event $clone
         */
        list($event, $clone) = $events;
        $event->getBase()->free();
        $this->assertFalse($event->check());
        $this->assertTrue($clone->check());
    }

    /**
     * @param Event $event
     * @group helper
     */
    public function eventHandler($event)
    {
        $arguments = $event->getArguments();
        print $arguments[0];
    }
}