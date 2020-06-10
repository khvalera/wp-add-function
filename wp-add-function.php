<?php
/*
* Plugin Name: wp-add-function
* Description: Additional common function WP
* Version: 0.0.1
* Author: Khomenko Valery
* Author URI: http://khv.pp.ua
* Domain Path: /lang/
* License: Trial
*/

/*
Copyright 2010-2019  Khomenko Valery  (email: khvalera@ukr.net)
*/

//===========================================
// Подключим локализацию дял MU плагина
add_action( 'init', function() {
    load_muplugin_textdomain( 'wp-add-function', dirname( plugin_basename( __FILE__ ) ) . '/wp-add-function/lang/' );
});

//===========================================
// Подключение общих функций
//===========================================
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/common-functions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages-functions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/db-functions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/class-table.php' );

