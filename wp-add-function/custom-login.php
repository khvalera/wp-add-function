<?php
/*
 * Plugin Name: wp-add-function
 * Description: Custom Login (/login) + Site Lock
 *    1) Кастомний логотип/заголовок на сторінці входу;
 *    2) заміна wp-login.php на /login на рівні WordPress;
 *    3) редірект незареєстрованих на /login.
 *
 * Для роботи потрібно додати в файл конфігурації віртуального хоста:
 *  <Directory "/srv/http/host">
 *     AllowOverride All
 *     Require all granted
 *  </Directory>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Налаштування
 */
const WAF_LOGIN_SLUG = 'login';

// Якщо true: прямий GET на /wp-login.php буде віддавати 404 (окрім action=postpass).
// Запити з action (logout/resetpass/lostpassword тощо) будуть редіректитись на /login з тим самим query string.
const WAF_BLOCK_DIRECT_WP_LOGIN = true;

/**
 * ============================================================
 *  Login branding (логотип на всіх екранах wp-login.php, включно з розблокуванням)
 * ============================================================
 * Деякі security-плагіни для сторінки розблокування/lockout можуть підставляти
 * свій CSS пізніше. Тому друкуємо інлайн-CSS максимально пізно та з !important.
 *
 * Сумісність:
 * - старий filter `waf_login_logo_url` збережено;
 * - новий filter `waf_login_branding_args` дозволяє змінювати URL, розміри і тексти.
 */
function waf_login__get_branding_args(): array {
    $args = array(
        'logo_url'        => WPMU_PLUGIN_URL . '/wp-add-function/pictures/logo.png',
        'width'           => '100%',
        'height'          => '84px',
        'background_size' => 'contain',
        'margin'          => '0 auto 25px',
        'header_url'      => home_url( '/' ),
        'header_text'     => get_bloginfo( 'name' ),
    );

    // Backward compatibility: старий filter для URL логотипа.
    $args['logo_url'] = apply_filters( 'waf_login_logo_url', $args['logo_url'] );

    // Новий filter для повного брендингу login-сторінки.
    $args = apply_filters( 'waf_login_branding_args', $args );

    // Страхуємося від некоректних значень.
    if ( ! is_array( $args ) ) {
        $args = array();
    }

    $args = wp_parse_args(
        $args,
        array(
            'logo_url'        => WPMU_PLUGIN_URL . '/wp-add-function/pictures/logo.png',
            'width'           => '100%',
            'height'          => '84px',
            'background_size' => 'contain',
            'margin'          => '0 auto 25px',
            'header_url'      => home_url( '/' ),
            'header_text'     => get_bloginfo( 'name' ),
        )
    );

    return $args;
}

function waf_login__print_login_logo_css(): void {
    static $printed = false;
    if ( $printed ) {
        return;
    }
    $printed = true;

    $args = waf_login__get_branding_args();
    ?>
    <style id="waf-login-logo-css">
    body.login #login h1 a,
    body.login .login h1 a {
        background-image: url('<?php echo esc_url( $args['logo_url'] ); ?>') !important;
        background-repeat: no-repeat !important;
        background-position: center !important;
        background-size: <?php echo esc_attr( $args['background_size'] ); ?> !important;
        width: <?php echo esc_attr( $args['width'] ); ?> !important;
        height: <?php echo esc_attr( $args['height'] ); ?> !important;
        margin: <?php echo esc_attr( $args['margin'] ); ?> !important;
    }
    </style>
    <?php
}
add_action( 'login_head', 'waf_login__print_login_logo_css', 99999 );

add_filter( 'login_headerurl', static function () {
    $args = waf_login__get_branding_args();
    return (string) $args['header_url'];
} );

add_filter( 'login_headertext', static function () {
    $args = waf_login__get_branding_args();
    return (string) $args['header_text'];
} );

/**
 * ============================================================
 *  /login замість wp-login.php (реалізація в стилі WPS Hide Login:
 *  без правок ядра, перехоплення запиту, переписування URL фільтрами)
 * ============================================================
 */

$GLOBALS['waf_login__direct_wp_login_request'] = false;

/**
 * Трейлінг-слеш під структуру пермалінків.
 */
function waf_login__use_trailing_slashes(): bool {
    $ps = (string) get_option( 'permalink_structure' );
    return ( $ps !== '' && substr( $ps, -1 ) === '/' );
}

function waf_login__user_trailingslashit( string $path ): string {
    return waf_login__use_trailing_slashes() ? trailingslashit( $path ) : untrailingslashit( $path );
}

/**
 * У plain-permalink режимі не використовуємо ?login,
 * бо WordPress reset-password URL вже містить login=<username>.
 */
function waf_login__page_query_var(): string {
    return 'waf_login';
}

function waf_login__using_plain_permalinks(): bool {
    return (string) get_option( 'permalink_structure' ) === '';
}

function waf_login__login_url( ?string $scheme = null ): string {
    $base = home_url( '/', $scheme );

    if ( ! waf_login__using_plain_permalinks() ) {
        return waf_login__user_trailingslashit( $base . WAF_LOGIN_SLUG );
    }

    return add_query_arg( waf_login__page_query_var(), '1', $base );
}

/**
 * Визначаємо, що зараз запит саме на “наш” /login.
 */

/**
 * Чи варто “жорстко” ховати wp-login.php (404) для поточного запиту.
 */
function waf_login__should_block_direct_wp_login_request(): bool {
    if ( ! WAF_BLOCK_DIRECT_WP_LOGIN ) {
        return false;
    }

    $method = strtoupper( (string) ( $_SERVER['REQUEST_METHOD'] ?? '' ) );
    if ( $method !== 'GET' && $method !== 'HEAD' ) {
        return false; // POST та інші методи не блокуємо
    }

    // Не блокуємо postpass (пароль-захищені записи)
    $q = (string) ( $_SERVER['QUERY_STRING'] ?? '' );
    parse_str( $q, $args );
    if ( isset( $args['action'] ) && $args['action'] === 'postpass' ) {
        return false;
    }

    // Блокуємо “чистий” wp-login.php без query string
    if ( trim( $q ) === '' ) {
        return true;
    }

    // Якщо є action (logout/lostpassword/rp/resetpass/register тощо) — краще редіректити на /login,
    // щоб не ламати старі лінки, але все одно прибирати wp-login.php з адресного рядка.
    return false;
}

function waf_login__is_login_slug_request(): bool {
    $request = parse_url( rawurldecode( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) ) );
    $path    = $request['path'] ?? '';

    // Враховуємо можливу /login або /login/
    $login_path = untrailingslashit( home_url( WAF_LOGIN_SLUG, 'relative' ) );

    return ( $path !== '' && untrailingslashit( $path ) === $login_path );
}

/**
 * Перехоплення запиту:
 * - /login -> поводимося як wp-login.php
 * - /wp-login.php -> ховаємо (робимо “неіснуючим” і потім редіректимо на /login)
 */
add_action( 'plugins_loaded', static function () {
    global $pagenow;

    $request_uri = rawurldecode( (string) ( $_SERVER['REQUEST_URI'] ?? '' ) );

    // 1) Прямий доступ до wp-login.php (зовні) — ховаємо.
    if ( strpos( $request_uri, 'wp-login.php' ) !== false && ! is_admin() ) {
        $GLOBALS['waf_login__direct_wp_login_request'] = true;

        // Маскуємо URI, щоб WP не обробляв wp-login.php напряму.
        $_SERVER['REQUEST_URI'] = waf_login__user_trailingslashit( '/' . str_repeat( '-/', 10 ) );
        $pagenow                = 'index.php';
        return;
    }

    // 2) Наш /login — змушуємо WP працювати як на wp-login.php.
    if (
        waf_login__is_login_slug_request()
        || ( waf_login__using_plain_permalinks() && isset( $_GET[ waf_login__page_query_var() ] ) )
    ) {
        $_SERVER['SCRIPT_NAME'] = WAF_LOGIN_SLUG;
        $pagenow                = 'wp-login.php';
    }
}, 9999 );

/**
 * На wp_loaded:
 * - якщо це був прямий /wp-login.php → редіректимо на /login з тим самим query string
 * - якщо pagenow = wp-login.php → підключаємо core wp-login.php і завершуємо
 */
add_action( 'wp_loaded', static function () {
    global $pagenow;

    // Редірект для прямого wp-login.php (показуємо “правильний” URL /login)
    if ( ! empty( $GLOBALS['waf_login__direct_wp_login_request'] ) ) {
        if ( waf_login__should_block_direct_wp_login_request() ) {
            status_header( 404 );
            nocache_headers();
            echo 'Not Found';
            exit;
        }

        $target = waf_login__login_url( is_ssl() ? 'https' : 'http' );
        if ( ! empty( $_SERVER['QUERY_STRING'] ) ) {
            $query_args = array();
            parse_str( (string) $_SERVER['QUERY_STRING'], $query_args );
            if ( ! empty( $query_args ) ) {
                $target = add_query_arg( $query_args, $target );
            }
        }
        wp_safe_redirect( $target, 302 );
        exit;
    }

    // Віддаємо login-сторінку на /login (і на fallback query var)
    if ( $pagenow === 'wp-login.php' ) {
        // WP core wp-login.php у деяких сценаріях звертається до $user_login без попередньої ініціалізації.
        // Щоб уникнути Warning при підвищеному error_reporting, задамо дефолти.
        $user_login = $user_login ?? '';
        $user_pass  = $user_pass ?? '';
        $error      = $error ?? '';

        require_once ABSPATH . 'wp-login.php';
        exit;
    }
}, 9999 );

/**
 * Переписуємо всі URL, які WP генерує на wp-login.php → /login
 * (за зразком того, як роблять плагіни типу WPS Hide Login).
 */
function waf_login__filter_wp_login_php_url( string $url, ?string $scheme = null ): string {
    // Не чіпаємо postpass (в WP там спеціальна логіка)
    if ( strpos( $url, 'wp-login.php?action=postpass' ) !== false ) {
        return $url;
    }

    if ( strpos( $url, 'wp-login.php' ) === false ) {
        return $url;
    }

    if ( is_ssl() ) {
        $scheme = 'https';
    }

    $parts = explode( '?', $url, 2 );
    $new   = waf_login__login_url( $scheme );

    if ( isset( $parts[1] ) && $parts[1] !== '' ) {
        parse_str( $parts[1], $args );
        $new = add_query_arg( $args, $new );
    }

    return $new;
}

add_filter( 'login_url', static function ( $login_url, $redirect, $force_reauth ) {
    $login_url = (string) $login_url;
    return waf_login__filter_wp_login_php_url( $login_url );
}, 10, 3 );

add_filter( 'site_url', static function ( $url, $path, $scheme, $blog_id ) {
    return waf_login__filter_wp_login_php_url( (string) $url, $scheme ? (string) $scheme : null );
}, 10, 4 );

add_filter( 'network_site_url', static function ( $url, $path, $scheme ) {
    return waf_login__filter_wp_login_php_url( (string) $url, $scheme ? (string) $scheme : null );
}, 10, 3 );

add_filter( 'wp_redirect', static function ( $location, $status ) {
    return waf_login__filter_wp_login_php_url( (string) $location );
}, 10, 2 );

add_filter( 'logout_url', static function ( $logout_url, $redirect ) {
    return waf_login__filter_wp_login_php_url( (string) $logout_url );
}, 10, 2 );

add_filter( 'lostpassword_url', static function ( $lostpassword_url, $redirect ) {
    return waf_login__filter_wp_login_php_url( (string) $lostpassword_url );
}, 10, 2 );

add_filter( 'register_url', static function ( $register_url ) {
    return waf_login__filter_wp_login_php_url( (string) $register_url );
}, 10, 1 );

/**
 * ============================================================
 *  Закриття сайту для незареєстрованих
 * ============================================================
 * Важливо: не ліземо в wp-login.php (/login), REST/AJAX/CRON.
 */
add_action( 'template_redirect', static function () {

    if ( is_user_logged_in() ) {
        return;
    }

    // Адмінка сама редіректить через auth_redirect(), не ламаємо.
    if ( is_admin() ) {
        return;
    }

    if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
        return;
    }

    if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
        return;
    }

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
        return;
    }

    $uri = (string) ( $_SERVER['REQUEST_URI'] ?? '' );

    // wp-json (часто потрібно залишати відкритим для інтеграцій)
    if ( strpos( $uri, '/wp-json/' ) !== false || strpos( $uri, 'rest_route=' ) !== false ) {
        return;
    }

    // Дозволяємо логін-ендпоінт
    if ( waf_login__is_login_slug_request() ) {
        return;
    }

    if ( waf_login__using_plain_permalinks() && isset( $_GET[ waf_login__page_query_var() ] ) ) {
        return;
    }

    global $pagenow;
    if ( $pagenow === 'wp-login.php' ) {
        return;
    }

    // Редіректимо на сторінку реєстрації і передаємо redirect_to (лише шлях)
    $current = $uri !== '' ? $uri : '/';

    $target = add_query_arg(
        array(
            'redirect_to' => $current,
        ),
        wp_registration_url()
    );

    wp_safe_redirect( $target, 302 );
    exit;

}, 1 );

/**
 * Redirect після логіна: на головну (корінь), а не на /wp-admin/profile.php
 */
add_filter( 'login_redirect', static function ( $redirect_to, $requested_redirect_to, $user ) {
    return home_url( '/' );
}, 10, 3 );
