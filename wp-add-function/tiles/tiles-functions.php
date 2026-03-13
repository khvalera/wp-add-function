<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function waf_tiles_register_screen( array $config ) {
    return WAF_Tiles_Engine::register_screen( $config );
}

function waf_tiles_get_screen( string $screen_id ) {
    return WAF_Tiles_Engine::get_screen( $screen_id );
}

function waf_tiles_render_screen( string $screen_id ) {
    return WAF_Tiles_Engine::render_screen( $screen_id );
}

function waf_tiles_render_partial( string $screen_id ) {
    return WAF_Tiles_Engine::render_partial( $screen_id );
}
