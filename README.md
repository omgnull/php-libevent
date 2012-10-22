php-libevent
============

https://github.com/onqu/php-libevent

This is php OOP wrapper for pecl libevent library http://pecl.php.net/package/libevent for php console applications.

Documentation page: http://pecl.php.net/package/libevent

Requirements:
 * php version 5.3.*
 * posix extesiion
 * libevent extesiion
 * pcntl extesiion

exmple signal listener:

    require __DIR__ . '/lib/include.php'

    class EventSignalHandler
    {
      public function test(array $arg)
      {
        // $arg[0] is the event class instance
        echo sprintf('Event signal %s triggered', $arg[1]);
        exit();
      }
    }


    // Initialize base

    $handler = new EventSignalHandler();
    $base = new \Libevent\Event\EventBase();
  
    foreach (array(SIGINT, SIGTERM) as $signal) {
      // Initialize event
      $event = new \Libevent\Event\Event(null, $base);

      // Map the signals
      $event
          ->prepare($signal, (EV_SIGNAL | EV_PERSIST), array($handler, 'test'), array($signal))
          ->enable()
      ;
    }

    // Main loop
    while (true) {
      $base->loop(EVLOOP_NONBLOCK);
      usleep(2000000);
      posix_kill(posix_getpid(), SIGTERM);
    }