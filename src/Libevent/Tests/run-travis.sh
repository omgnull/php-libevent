#!/bin/sh

sudo apt-get install libevent-1.4-2 libevent-dev
pecl install channel://pecl.php.net/libevent-0.0.5
echo "extension=libevent.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini