<?php
/**
 * Require all the files needed for Wordlift_Task.
 *
 * @package Wordlift_Task
 * @since 1.0.0
 */

require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/intf-wordlift-task.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/intf-wordlift-task-progress.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/intf-wordlift-task-runner.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/class-wordlift-task-ajax-adapter.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/class-wordlift-task-ajax-progress.php';
require_once plugin_dir_path( dirname( __FILE__ ) ) . 'task/class-wordlift-task-single-instance-task-runner.php';
