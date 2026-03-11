<?php
/**
 * Plugin Name: Disable WP Translations API (no WordPress.org calls)
 * Description: Prevents WordPress from calling api.wordpress.org for translation packages.
 */

add_filter('translations_api', function ($result, $type, $args) {
    // Must be an array (or WP_Error). Core code treats this as an array. :contentReference[oaicite:1]{index=1}
    return [
        'translations' => [],
    ];
}, 10, 3);
