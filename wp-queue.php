<?php

/*
* Plugin Name: WordPress Queue
* Plugin URI: https://github.com/withinboredom/wp-queue
* Description: A simple, but powerful queue
* Author: withinboredom
* Version: 1.0-alpha
* Author URI: https://github.com/withinboredom/wp-queue
* License: GPL3+
*/

/**
 * Class WP_Queue
 *
 * A simply powerful WordPress Queue
 */
class WP_Queue {

	/**
	 * @var array The queue
	 */
	private $queue = [];

	/**
	 * @var array Subscriptions
	 */
	private static $subscriptions = [];

	/**
	 * WP_Queue constructor.
	 *
	 * Creates a queue object for dequeueing off the queue. Use the static functions to enqueue things.
	 */
	public function __construct() {
		add_action( 'all', [ $this, 'ready_queue' ], 2 );
		register_shutdown_function( [ $this, 'dequeue' ] );
	}

	/**
	 * Put data on the queue
	 *
	 * @param string $topic The topic to post the data to
	 * @param mixed $data The data to put on the topic
	 * @param int $delay The number of seconds to delay the data
	 */
	public static function enqueue( string $topic, $data, int $delay = 0 ) {
		wp_schedule_single_event( time() + $delay, 'pop_wp_queue_' . sha1( $topic . '_' . microtime() ), [
			[
				'data'  => $data,
				'topic' => $topic,
			]
		] );
	}

	/**
	 * Subscribe to a topic
	 *
	 * @param string $topic The topic to subscribe to
	 * @param callable $callback The callback to receive data at
	 */
	public static function subscribe( string $topic, callable $callback ) {
		if ( ! isset( $subscriptions[ $topic ] ) ) {
			self::$subscriptions[ $topic ] = [];
		}

		self::$subscriptions[ $topic ][] = $callback;
	}

	/**
	 * Takes items off the queue. Called during php shutdown.
	 */
	public function dequeue() {
		foreach ( $this->queue as $topic => &$queue ) {
			foreach ( $queue as &$data ) {
				if ( isset( self::$subscriptions[ $topic ] ) ) {
					foreach ( self::$subscriptions[ $topic ] as &$callback ) {
						if ( is_callable( $callback ) ) {
							$callback( $data['data'] );
						}
					}
				}
			}
		}
	}

	/**
	 * Handle's hooks, looking for enqueue hooks and dropping them on the queue.
	 *
	 * @param string $tag The hook name
	 * @param array $data The args for the hook
	 */
	public function ready_queue( $tag, $data ) {
		if ( strpos( $tag, 'pop_wp_queue' ) === 0 ) {
			$data = &$data[0];
			if ( ! is_array( $this->queue[ $data['topic'] ] ) ) {
				$this->queue[ $data['topic'] ] = [];
			}

			$this->queue[ $data['topic'] ][] = &$data;
		}
	}
}

if ( defined( 'DOING_CRON' ) ) {
	new WP_Queue();
}
