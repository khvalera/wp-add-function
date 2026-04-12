<?php
/*
* Plugin Name: wp-add-function
* Description: Additional common function WP
* Version: 0.0.2
* Author: Khomenko Valery
* Author URI: https://github.com/khvalera/wp-add-function
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
// Чтобы не получать ошибку нужно предварительно запустить wp-includes/pluggable.php
if( ! function_exists( 'wp_get_current_user' )) {
    include(ABSPATH . "wp-includes/pluggable.php");
}

//===========================================
// Подключение общих функций
//===========================================
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/wp-hide.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/custom-login.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/wp-disable-auto-update.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/disable-translations.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/common-functions.php' );

require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-actions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-core.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-forms.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-classes.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-elements.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/pages/pages-export.php' );

require_once( WPMU_PLUGIN_DIR . '/wp-add-function/db-functions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/class-list-table-state-manager.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/class-base-list-table.php' );

//===========================================
// WAF Tiles — універсальний механізм плиток
//===========================================
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/tiles-functions.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/class-waf-tiles-renderer.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/class-waf-tiles-engine.php' );
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/class-waf-tiles-ajax-controller.php' );

//===========================================
// TCPDF для експорту в PDF
//===========================================
require_once( WPMU_PLUGIN_DIR . '/wp-add-function/lib/tcpdf/tcpdf.php' );
