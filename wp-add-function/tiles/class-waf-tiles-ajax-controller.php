<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WAF_Tiles_Ajax_Controller {

    public static function boot() : void {
        add_action( 'wp_ajax_waf_tiles_render', [ __CLASS__, 'render' ] );
    }

    public static function render() : void {
        $screen_id = isset( $_POST['screen_id'] ) ? sanitize_key( wp_unslash( $_POST['screen_id'] ) ) : '';
        $nonce     = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

        if ( $screen_id === '' ) {
            wp_send_json_error( [ 'message' => __( 'Screen ID is required.', 'wp-add-function' ) ], 400 );
        }

        $screen = WAF_Tiles_Engine::get_screen( $screen_id );

        if ( empty( $screen ) ) {
            wp_send_json_error( [ 'message' => __( 'Tiles screen is not registered.', 'wp-add-function' ) ], 404 );
        }

        if ( ! wp_verify_nonce( $nonce, 'waf_tiles_render_' . $screen_id ) ) {
            wp_send_json_error( [ 'message' => __( 'Security check failed.', 'wp-add-function' ) ], 403 );
        }

        if ( ! current_user_can( $screen['capability'] ) ) {
            wp_send_json_error( [ 'message' => __( 'Access denied.', 'wp-add-function' ) ], 403 );
        }

        wp_send_json_success( [
            'html'        => WAF_Tiles_Engine::render_partial( $screen_id ),
            'rendered_at' => current_date_time( 'H:i:s' ),
        ] );
    }
}

WAF_Tiles_Ajax_Controller::boot();
