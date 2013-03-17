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
     */
    public function testBaseInit()
    {
        $base = new EventBase();
        $this->assertTrue(is_resource($base->getResource()), 'Invalid event base resource.');
        $base->free();

        return $base;
    }

    /**
     * @depends testBaseInit
     * @expectedException \Libevent\Exception\EventException
     */
    public function testBaseExceptionOnPrioritySet(EventBase $base)
    {
        $base->setPriority(EventBase::DEFAULT_PRIORITY);
    }

    /**
     * @depends testBaseExceptionOnPrioritySet
     * @expectedException \Libevent\Exception\EventException
     */
    public function testBaseExceptionOnNonIntegerPrioritySet()
    {
        new EventBase('string');
    }
}