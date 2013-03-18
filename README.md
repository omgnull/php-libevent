php-libevent
============

[![Build Status](https://travis-ci.org/omgnull/php-libevent.png)](https://travis-ci.org/omgnull/php-libevent)

This is php OOP wrapper for pecl libevent library http://pecl.php.net/package/libevent, used in php cli applications.
Documentation page: http://pecl.php.net/package/libevent

Not tested on libevent >= v2.*

For php >= 5.4 you can use an Event native classes, also requires libevent library >= 2+:
 * http://docs.php.net/manual/ru/book.event.php
 * http://pecl.php.net/package/event


Requirements:
-------------
 * php version >= 5.3.*
 * libevent library (http://libevent.org/)
 * libevent extension
 * posix extension (optional)


TODO:
-----
* Buffer events (not working correctly)
* Examples
* More tests


p.s. do not use it for web apps :D