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
use Libevent\Event\EventInterface;
use Libevent\Event\Event;

class EventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Libevent\Event\Event::initialize
     * @covers Libevent\Event\Event::generateName
     * @return EventInterface
     */
    public function testEventInit()
    {
        $base = new EventBase();

        // event without direct name
        $event = new Event($base);
        $this->assertTrue(is_string($event->getName()));
        unset($event);

        $event = new Event($base, 'test');

        // Invalid callback test
        try {
            $event->prepare(SIGUSR1, EV_SIGNAL, array($this, 'invalidCallback'), array('test_event'));
            $this->markTestIncomplete('Invalid callback not catched');
        } catch (\InvalidArgumentException $e) {

        }

        $this->assertInstanceOf(
            'Libevent\Event\EventInterface',
            $event->prepare(SIGUSR1, EV_SIGNAL | EV_PERSIST, array($this, 'eventHandlerBreakingLoop'), array('test_event'))
        );
        $this->assertTrue($event->enable());

        return $event;
    }

    /**
     * @param EventInterface $event
     * @covers Libevent\Event\Event::remove
     * @depends testEventInit
     */
    public function testEventDisableAndArgumentLinked(EventInterface $event)
    {
        $base = $event->getBase();
        $event = new Event($base, 'test_event_enable');
        $this->assertFalse($event->enable());

        // Test non persist
        $event->prepare(SIGUSR2, EV_SIGNAL, array($this, 'eventHandlerInnerCallback'), array(
            'test_argument' => new \stdClass()
        ));

        $this->assertCount(1, $base->getDisabledEvenets());
        $this->assertTrue($base->isEventDisabled('test_event_enable'));
        $this->assertTrue($event->enable());
        $this->assertFalse($base->isEventDisabled('test_event_enable'));
        $this->assertCount(0, $base->getDisabledEvenets());

        posix_kill(posix_getpid(), SIGUSR2);
        $base->loop(EVLOOP_NONBLOCK);

        $this->assertArrayHasKey('test_argument', $arguments = $event->getArguments());
        $this->assertTrue(is_object($arguments['test_argument']));
        $this->assertAttributeEquals(1, 'testParam', $arguments['test_argument']);

        $this->assertTrue($base->isEventDisabled('test_event_enable'));
        $event->enable();
        $event->free();
        $this->assertCount(0, $base->getDisabledEvenets());
        $this->assertFalse($base->exists('test_event_enable'));
        unset($base);
    }

    /**
     * @param EventInterface $event
     * @depends testEventInit
     *
     * @return EventInterface
     */
    public function testEventVariables(EventInterface $event)
    {
        $this->assertTrue($event->getName() === 'test');
        $this->assertTrue($event->check());
        $this->assertTrue($event->getBase() instanceof EventBaseInterface);
        $this->assertTrue($event->getBase()->exists($event->getName()));

        return $event;
    }

    /**
     * @param EventInterface $event
     * @depends testEventVariables
     *
     * @return EventInterface
     */
    public function testEventExceptionSetGet(EventInterface $event)
    {
        $exception = new EventException();
        $this->assertInstanceOf('Libevent\Exception\EventException', $exception->setEvent($event));
        $this->assertInstanceOf('Libevent\Event\EventInterface', $exception->getEvent());

        return $event;
    }

    /**
     * @param EventInterface $event
     * @covers Libevent\Event\Event::invoke
     * @depends testEventExceptionSetGet
     *
     * @return EventInterface
     */
    public function testEventManualInvoke(EventInterface $event)
    {
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
        posix_kill(posix_getpid(), SIGUSR1);
        $base = $event->getBase();
        $base->loop();

        $event = new Event($base, 'event_for_loop_exit');
        $event
            ->prepare(SIGHUP, EV_SIGNAL, array($this, 'eventHandlerExitingLoop'), array('test_event'))
            ->enable();

        $event->disable();
        $base->loop();

        return $event;
    }

    /**
     * @param EventInterface $event
     * @depends testEventBaseInvoke
     *
     * @return array
     */
    public function testEventClone(EventInterface $event)
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
         * @var EventInterface $event
         * @var EventInterface $clone
         */
        list($event, $clone) = $events;
        $event->getBase()->free();
        $this->assertFalse($event->check());
        $this->assertTrue($clone->check());
    }

    /**
     * @param EventInterface $event
     * @group helper
     */
    public function eventHandlerBreakingLoop(EventInterface $event)
    {
        $base = $event->getBase();
        $base->loopBreak();
        
        $base->loopExit();
    }

    /**
     * @param EventInterface $event
     * @group helper
     */
    public function eventHandlerExitingLoop(EventInterface $event)
    {
        // Loop exit in 0.1sec
        $event->getBase()->loopExit(100000);
    }

    /**
     * @param EventInterface $event
     * @group helper
     */
    public function eventHandlerInnerCallback(EventInterface $event)
    {
        $arguments = $event->getArguments();

        if (!isset($arguments['test_argument']) || !$arguments['test_argument'] instanceof \stdClass) {
            return;
        }
        $arguments['test_argument']->testParam = 1;
    }
}