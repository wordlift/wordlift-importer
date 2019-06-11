<?php
/**
 * This file defines the interface for task runners.
 *
 * @since 1.0.0
 * @package Wordlift_Geo
 * @subpackage Wordlift_Geo/includes/task
 */

/**
 * Define the Wordlift_Task_Runner interface.
 *
 * @since 1.0.0
 */
interface Wordlift_Task_Runner {

	/**
	 * Start the task.
	 *
	 * @param int $limit The maximum number of items to process.
	 * @param int $offset The starting offset (zero-based).
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	function start( $limit = 0, $offset = 0 );

}
