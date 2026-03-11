<?php

/*
Plugin Name: Disable All Auto Updates
Description: Disables all automatic WordPress updates.
*/

//===========================================
// Вимкнути всі автоматичні оновлення
//===========================================

// Вимикаємо автооновлення ядра
add_filter('automatic_updater_disabled', '__return_true');

// Вимикаємо оновлення ядра WordPress (всі типи)
add_filter('auto_update_core', '__return_false');

// Вимикаємо оновлення плагінів
add_filter('auto_update_plugin', '__return_false');

// Вимикаємо оновлення тем
add_filter('auto_update_theme', '__return_false');

// Вимикаємо оновлення перекладів
add_filter('auto_update_translation', '__return_false');

// Автоматичні оновлення безпеки (security updates)
// Малі автоматичні оновлення (minor releases)
// Великі автоматичні оновлення (major releases)

add_filter('wp_auto_update_core', '__return_false');

//===========================================
// Вимкнути перевірку версії
//===========================================
add_action('init', function() {
    remove_action('init', 'wp_version_check');
    remove_action('wp_version_check', 'wp_version_check');
    remove_action('admin_init', '_maybe_update_core');
    add_filter('pre_option_update_core', '__return_null');
    wp_clear_scheduled_hook('wp_version_check');
});
