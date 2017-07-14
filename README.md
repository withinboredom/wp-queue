# wp-queue

An incredibly simple queue system for WordPress.

## Installation

Drop `wp-queue.php` anywhere you like (preferably mu-plugins or plugins folder).

Activate if necessary.

## Topics

The queue uses `topics` to organize things. You place data in a `topic` and subscribe to a `topic`.

## Putting things on the queue

Call `WP_Queue::enqueue( $topic, $data, $delay )` where `$topic` is the topic to place `$data` on the queue and `$delay`
is the number of seconds to delay putting it on the queue.

## Subscribing to topics

Call `WP_Queue::subscribe( $topic, $callable )` where `$topic` is the topic to subscribe to and `$callable` is a callable
to callback with. It must accept 1 argument and that argument will be the `$data` you pass in from `WP_Queue::enqueue`.

# Queue Guarantees

The queue operates on a best-effort, at least once basis. It's possible to receive duplicates, however, extremely unlikely.
It's also possible that a message will get lost, however, also extremely unlikely. It's built on wp-cron, so it must be
enabled to receive subscriptions.

# Caveats

- Requires wp-cron
- Subscriptions are called from php's shutdown hook. Use absolute paths in your callbacks if you need file access.
