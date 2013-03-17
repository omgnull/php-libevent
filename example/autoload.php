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

/**
 * Autoload for event package
 */
define('DS', DIRECTORY_SEPARATOR);
spl_autoload_register(function($classname) {
    if (0 === strpos($classname, 'Libevent')) {
        $classname = str_replace('\\', DS, $classname);
        require_once __DIR__ . DS . '..' . DS .'src' .DS . $classname . '.php';
    }
});