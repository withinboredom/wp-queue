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

class WP_Queue {
	public static function enqueue( $topic, $data, $delay = 0 ) {
		wp_schedule_single_event( time() + $delay, 'pop_wp_queue_' . sha1( $topic . '_' . microtime() ), [
			[
				'data'  => $data,
				'topic' => $topic
			]
		] );
	}

	public static function subscribe( $topic, $callback ) {
		if ( ! isset( $subscriptions[ $topic ] ) ) {
			self::$subscriptions[ $topic ] = [];
		}

		self::$subscriptions[ $topic ][] = $callback;
	}

	private $queue = [];
	private static $subscriptions = [];

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

	public function __construct() {
		add_action( 'all', [ $this, 'ready_queue' ], 2 );
		register_shutdown_function( [ $this, 'dequeue' ] );
	}

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
