<?php

/**
 * WP-Broker
 *
 * @package             wp-broker
 * @author              Michiel Uitdehaag
 * @copyright           2020 Michiel Uitdehaag for muis IT
 * @licenses            GPL-3.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:         wp-broker
 * Plugin URI:          https://github.com/muisit/wp-broker
 * Description:         Turn your wordpress installation in a private facebook
 * Version:             1.0.0
 * Requires at least:   5.4
 * Requires PHP:        7.2
 * Author:              Michiel Uitdehaag
 * Author URI:          https://www.muisit.nl
 * License:             GNU GPLv3
 * License URI:         https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:         wp-broker
 * Domain Path:         /languages
 *
 * This file is part of wp-broker.
 *
 * wp-broker is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * wp-broker is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with wp-broker.  If not, see <https://www.gnu.org/licenses/>.
 */


function wpbroker_activate() {
    require_once(__DIR__.'/php/activate.php');
    $activator = new \WPBroker\Activator();
    $activator->activate();
}

function wpbroker_deactivate() {
    require_once(__DIR__.'/php/activate.php');
    $activator = new \WPBroker\Activator();
    $activator->deactivate();
}

function wpbroker_uninstall() {
    require_once(__DIR__ . '/php/activate.php');
    $activator = new \WPBroker\Activator();
    $activator->uninstall();
}

function wpbroker_upgrade_function($upgrader_object, $options) {
    $current_plugin_path_name = plugin_basename(__FILE__);

    if ($options['action'] == 'update' && $options['type'] == 'plugin') {
        foreach ($options['plugins'] as $each_plugin) {
            if ($each_plugin == $current_plugin_path_name) {
                require_once(__DIR__ . '/php/activate.php');
                $activator = new \WPBroker\Activator();
                $activator->upgrade();
            }
        }
    }
}
function wpbroker_plugins_loaded() {
    require_once(__DIR__ . '/php/activate.php');
    $activator = new \WPBroker\Activator();
    $activator->update();
}

function wpbroker_ajax_handler($page) {
    require_once(__DIR__ . '/php/api.php');
    $dat = new \WPBroker\API();
    $dat->resolve();
}

function wpbroker_display_admin_page() {
    error_log("wpbroker: display_admin_page");
    require_once(__DIR__ . '/php/display.php');
    $dat = new \WPBroker\Display();
    $dat->adminPage();
}

function wpbroker_enqueue_scripts($page) {
    error_log("wpbroker: enqueue_scripts");
    require_once(__DIR__ . '/php/display.php');
    $dat = new \WPBroker\Display();
    $dat->scripts($page);
}

function wpbroker_admin_menu() {
	add_menu_page(
		__( 'Wall' ),
		__( 'Wall' ),
		'has_wall',
		'WPBroker',
        'wpbroker_display_admin_page',
        'dashicons-media-spreadsheet',
        100
	);
}
function wpbroker_shortcode($atts) {
    require_once(__DIR__ . '/php/display.php');
    $actor = new \WPBroker\Display();
    return $actor->shortCode("wall",$atts);
}

if (defined('ABSPATH')) {
    register_activation_hook( __FILE__, 'wpbroker_activate' );
    register_deactivation_hook( __FILE__, 'wpbroker_deactivate' );
    register_uninstall_hook(__FILE__, 'wpbroker_uninstall');
    add_action('upgrader_process_complete', 'wpbroker_upgrade_function', 10, 2);
    add_action('plugins_loaded', 'wpbroker_plugins_loaded');

    add_action( 'admin_enqueue_scripts', 'wpbroker_enqueue_scripts' );
    add_action( 'admin_menu', 'wpbroker_admin_menu' );
    add_action('wp_ajax_WPBroker', 'wpbroker_ajax_handler');
    add_action('wp_ajax_nopriv_WPBroker', 'wpbroker_ajax_handler');
    add_shortcode( 'wp-broker', 'wpbroker_shortcode' );

}
