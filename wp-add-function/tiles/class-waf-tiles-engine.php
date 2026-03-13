<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WAF_Tiles_Engine {

    private static array $screens = [];
    private static bool $booted = false;

    public static function boot() : void {
        if ( self::$booted ) {
            return;
        }

        self::$booted = true;

        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_assets' ] );
    }

    public static function register_screen( array $config ) : array {
        self::boot();

        $screen_id = sanitize_key( $config['screen_id'] ?? '' );

        if ( $screen_id === '' ) {
            return [];
        }

        $config['screen_id']        = $screen_id;
        $config['page_slug']        = $config['page_slug'] ?? $screen_id;
        $config['capability']       = $config['capability'] ?? 'manage_options';
        $config['provider_callback']= $config['provider_callback'] ?? '';
        $config['schema_callback']  = $config['schema_callback'] ?? '';
        $config['ajax_enabled']     = ! empty( $config['ajax_enabled'] );
        $config['auto_refresh']     = ! empty( $config['auto_refresh'] );
        $config['refresh_interval'] = isset( $config['refresh_interval'] ) ? max( 5, (int) $config['refresh_interval'] ) : 30;
        $config['refresh_on_focus'] = array_key_exists( 'refresh_on_focus', $config ) ? (bool) $config['refresh_on_focus'] : true;

        self::$screens[ $screen_id ] = $config;

        return $config;
    }

    public static function get_screen( string $screen_id ) : array {
        $screen_id = sanitize_key( $screen_id );
        return self::$screens[ $screen_id ] ?? [];
    }

    public static function enqueue_assets() : void {
        $screen = self::get_current_screen_config();

        if ( empty( $screen ) ) {
            return;
        }

        $base_url = plugins_url( 'wp-add-function/tiles/', WPMU_PLUGIN_DIR . '/wp-add-function.php' );

        wp_enqueue_style(
            'waf-tiles',
            $base_url . 'waf-tiles.css',
            [],
            filemtime( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/waf-tiles.css' )
        );

        wp_enqueue_script(
            'waf-tiles',
            $base_url . 'waf-tiles.js',
            [],
            filemtime( WPMU_PLUGIN_DIR . '/wp-add-function/tiles/waf-tiles.js' ),
            true
        );

        wp_localize_script(
            'waf-tiles',
            'WAF_Tiles',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ]
        );
    }

    public static function render_screen( string $screen_id ) : string {
        $screen = self::get_screen( $screen_id );

        if ( empty( $screen ) ) {
            return '';
        }

        if ( ! current_user_can( $screen['capability'] ) ) {
            wp_die( esc_html__( 'Sorry, you are not allowed to view this page.', 'wp-add-function' ) );
        }

        $nonce = wp_create_nonce( 'waf_tiles_render_' . $screen['screen_id'] );
        $html  = self::render_partial( $screen['screen_id'] );

        ob_start();
        ?>
        <div class="waf-tiles"
             data-screen-id="<?php echo esc_attr( $screen['screen_id'] ); ?>"
             data-nonce="<?php echo esc_attr( $nonce ); ?>"
             data-ajax-enabled="<?php echo esc_attr( $screen['ajax_enabled'] ? '1' : '0' ); ?>"
             data-auto-refresh="<?php echo esc_attr( $screen['auto_refresh'] ? '1' : '0' ); ?>"
             data-refresh-interval="<?php echo esc_attr( (string) $screen['refresh_interval'] ); ?>"
             data-refresh-on-focus="<?php echo esc_attr( $screen['refresh_on_focus'] ? '1' : '0' ); ?>">

            <div class="waf-tiles__toolbar">
                <span class="waf-tiles__status" aria-live="polite"></span>
            </div>

            <div class="waf-tiles__body">
                <?php echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
        <?php

        return (string) ob_get_clean();
    }

    public static function render_partial( string $screen_id ) : string {
        $screen = self::get_screen( $screen_id );

        if ( empty( $screen ) ) {
            return '';
        }

        $items  = self::get_items( $screen );
        $schema = self::get_schema( $screen );

        return WAF_Tiles_Renderer::render( $items, $schema, $screen );
    }

    public static function get_items( array $screen ) : array {
        $callback = $screen['provider_callback'] ?? '';

        if ( ! is_callable( $callback ) ) {
            return [];
        }

        $items = call_user_func( $callback, $screen );

        return is_array( $items ) ? $items : [];
    }

    public static function get_schema( array $screen ) : array {
        $callback = $screen['schema_callback'] ?? '';

        if ( is_callable( $callback ) ) {
            $schema = call_user_func( $callback, $screen );
            if ( is_array( $schema ) ) {
                return $schema;
            }
        }

        return [];
    }

    private static function get_current_screen_config() : array {
        $page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

        if ( $page === '' ) {
            return [];
        }

        foreach ( self::$screens as $screen ) {
            if ( $page === sanitize_key( $screen['page_slug'] ) ) {
                return $screen;
            }
        }

        return [];
    }
}

WAF_Tiles_Engine::boot();
