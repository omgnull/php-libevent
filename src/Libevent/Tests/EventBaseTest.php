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

use Libevent\Base\EventBaseInterface;
use Libevent\Base\EventBase;
use Libevent\Exception\EventException;

class EventBaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Functions to be used
     *
     * @var array
     */
    protected $functions = array(
        'posix_getpid',
        'posix_kill'
    );

    public function testDependency()
    {
        // Test php version
        if (!version_compare(PHP_VERSION, '5.3', '>=')) {
            $this->markTestIncomplete('PHP version must be >= 5.3.');
        }

        // Test libevent extension
        if (!extension_loaded('libevent')) {
            $this->markTestIncomplete('Exetension "libevent.so" not loaded.');
        }

        // Used posix functions
        foreach ($this->functions as $function) {
            if (!function_exists($function)) {
                $this->markTestIncomplete(sprintf('Function %s must be enabled to run this test.', $function));
            }
        }
    }

    /**
     * @depends testDependency
     *
     * @return EventBase
     */
    public function testBaseInit()
    {
        $base = new EventBase();
        $this->assertTrue(is_resource($base->getResource()), 'Invalid event base resource.');
        $this->assertEquals(1, $base->loop());
        $base->free();

        return $base;
    }

    /**
     * @param EventBaseInterface $base
     * @depends testBaseInit
     *
     * @return EventBase
     */
    public function testBaseException(EventBaseInterface $base)
    {
        $exception = new EventException();
        $this->assertInstanceOf('Libevent\Exception\EventException', $exception->setBase($base));
        $this->assertInstanceOf('Libevent\Base\EventBaseInterface', $exception->getBase());

        return $base;
    }

    /**
     * @depends testBaseException
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testBaseExceptionOnPrioritySet(EventBaseInterface $base)
    {
        $base->setPriority(EventBase::DEFAULT_PRIORITY);
    }

    /**
     * @depends testBaseExceptionOnPrioritySet
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testBaseExceptionOnNonIntegerPrioritySet()
    {
        new EventBase('string');
    }

    /**
     * @depends testBaseExceptionOnNonIntegerPrioritySet
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testBaseLoopBreak()
    {
        $base = new EventBase();
        $this->assertInstanceOf('Libevent\Base\EventBaseInterface', $base->loopBreak());
        $base->free();
        $base->loopBreak();
    }

    /**
     * @depends testBaseLoopBreak
     * @expectedException \PHPUnit_Framework_Error
     */
    public function testBaseLoopExit()
    {
        $base = new EventBase();
        $this->assertInstanceOf('Libevent\Base\EventBaseInterface', $base->loopExit());
        $base->free();
        $base->loopExit();
    }
}