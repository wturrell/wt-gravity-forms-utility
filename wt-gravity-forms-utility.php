<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * Dashboard. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://wturrell.co.uk
 * @since             0.0.1
 *
 * @wordpress-plugin
 * Plugin Name:       Gravity Forms Utility
 * Plugin URI:        https://github.com/wturrell/wt-gravity-forms-utility
 * Description:       Resend form notifications in bulk
 * Version:           0.1.0
 * Tested up to:      4.5.2
 * Author:            William Turrell
 * Author URI:        http://wturrell.co.uk/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wt-gravity-forms-utility
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
} elseif ( defined( 'WP_CLI' ) && WP_CLI ) {
	// Command line support for development
	require_once plugin_dir_path( __FILE__ ) . 'wp-cli.php';
}

