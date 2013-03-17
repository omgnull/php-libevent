#!/bin/sh

echo "start to buil libevent libs & headers\n"
sudo apt-get install libevent libevent-dev

echo "start installing libevent extension\n"
pecl install -f channel://pecl.php.net/libevent-0.0.5 --with-libevent
echo "extension=libevent.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo "start testing\n"
phpunit --coverage-text