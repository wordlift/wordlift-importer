<?php
/**
 * This file is part of the task subfolder. It provices the interface for tasks.
 *
 * @since 1.0.0
 * @package Wordlift_Geo
 * @subpackage Wordlift_Geo/includes/task
 */

/**
 * Define the Wordlift_Task interface.
 *
 * @since 1.0.0
 */
interface Wordlift_Task {

	/**
	 * Define the task ID.
	 *
	 * @return string The task id.
	 * @since 1.0.0
	 */
	function get_id();

	/**
	 * List the items to process.
	 *
	 * @param int $limit The maximum number of items to process, default 0, i.e. no limit.
	 * @param int $offset The starting offset, default 0.
	 *
	 * @return array An array of items.
	 * @since 1.0.0
	 */
	function list_items( $limit = 0, $offset = 0 );

	/**
	 * Count the total number of items to process.
	 *
	 * @return int Total number of items to process.
	 * @since 1.0.0
	 */
	function count_items();

	/**
	 * Process the provided item.
	 *
	 * @param mixed $item Process the provided item.
	 *
	 * @since 1.0.0
	 *
	 */
	function process_item( $item );

}
