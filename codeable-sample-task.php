<?php
/*
 * Plugin Name: Codeable Sample Task
 * Version: 1.0
 * Plugin URI: https://github.com/thebeard
 * Description: A small sample task for the Codeable Team. Safely updating the title of a Wordpress post using a $_GET parameter
 * Author: Theunis Cilliers
 * Author URI: https://github.com/thebeard
 *
 * @package WordPress
 * @author Theunis Cilliers
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// Load plugin class files
require_once( 'includes/class-codeable-sample-task.php' );

// Define Permissions & Redirect behavior
if ( !defined( 'CAN_GUEST_UPDATE') ) define( 'CAN_GUEST_UPDATE', true );
if ( !defined( 'DISABLE_REDIRECT' ) ) define( 'DISABLE_REDIRECT', false );

/**
 * Returns the main instance of Codeable_Sample_Task to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return object Codeable_Sample_Task
 */
function Codeable_Sample_Task () {

	$instance = Codeable_Sample_Task::instance( __FILE__, '1.0.0' ); // #code trail... 2
	return $instance;
	
}

Codeable_Sample_Task(); // #code trail... 1
