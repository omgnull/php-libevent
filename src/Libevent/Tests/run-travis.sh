#!/bin/sh

echo "\nstart to buil libevent libs & headers"
sudo apt-get install libevent-1.4.2 libevent-dev
sudo pear upgrade

echo "\nstart installing libevent extension"
pecl install -f channel://pecl.php.net/libevent-0.0.5 --with-libevent
echo "extension=libevent.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo "\nstart testing"
phpunit --coverage-text