#!/bin/sh

echo "\nstart to build libevent libs & headers"
sudo apt-get install libevent-1.4.2 libevent-core-1.4-2 libevent-dev

echo "\nstart installing libevent extension"
printf "\n" | pecl install channel://pecl.php.net/libevent-0.0.5
echo "extension=libevent.so" >> ~/.phpenv/versions/$(phpenv version-name)/etc/php.ini

echo "\nstart testing"
phpunit