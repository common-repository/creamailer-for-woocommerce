<?php
/**
 * Plugin Name: Creamailer for WooCommerce
 * Description: Simple Creamailer integration for WooCommerce.
 * Version:     1.0.2
 * Author:      Gurumedia Oy
 * Author URI:  https://gurumedia.fi
 * License:     GPLv2
 * Text Domain: creamailer-for-woocommerce
 * Domain Path: /i18n/languages
 *
 * @package CreamailerForWoocommerce
 */
/*
 *  Creamailer for Woocommerce WordPress plugin
 *  Copyright (C) 2021 Gurumedia (email: tuki@gurumedia.fi)
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if ( ! defined( 'CREAMAILER_FOR_WOOCOMMERCE_VERSION' ) ) {
	define( 'CREAMAILER_FOR_WOOCOMMERCE_VERSION', '1.0.2' );
}
if ( ! defined( 'CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_URL' ) ) {
	define( 'CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_URL', plugins_url( '', __FILE__ ) );
}
if ( ! defined( 'CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_DIR' ) ) {
	define( 'CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_DIR', dirname( __FILE__ ) );
}
if ( ! defined( 'CREAMAILER_FOR_WOOCOMMERCE_LOG_DIR' ) ) {
	define( 'CREAMAILER_FOR_WOOCOMMERCE_LOG_DIR', CREAMAILER_FOR_WOOCOMMERCE_PLUGIN_DIR . '/log' );
}
if ( ! class_exists( 'CreamailerForWordpressPlugin' ) ) {
	require_once dirname( __FILE__ ) . '/src/wp-creamailer-for-wordpress-plugin.php';
	new CreamailerForWordpressPlugin();
}
