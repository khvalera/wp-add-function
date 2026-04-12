<?php

//===========================================
// Загальні функції
//===========================================

global $color_all;

$color_all = array(
   'red' => '#ce181e',
   'light_brown' => '#b47804'
);

//===========================================
// Функція повертає час
//===========================================
function current_date_time($format = '' ){
   if ( empty( $format ))
      $format = "Y/m/j H:i:s";
   // Отримаємо час
   $timezone  = get_option('gmt_offset');
   $today = gmdate( $format, time() + 3600 * ($timezone + date("I")));
   return $today;
}

//=============================================
// Отримати числовий код локалі користувача для бази db_card
// @return int
//=============================================
function get_user_locale_db_card(): int {

    $locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();
    $locale = strtolower( (string) $locale );

    /*
     * Підтримуємо як повні коди:
     * - uk_UA
     * - ru_RU
     * - en_US
     *
     * так і скорочені:
     * - uk
     * - ru
     * - en
     */
    if ( strpos( $locale, 'uk' ) === 0 ) {
        return 3;
    }

    if ( strpos( $locale, 'ru' ) === 0 ) {
        return 2;
    }

    if ( strpos( $locale, 'en' ) === 0 ) {
        return 1;
    }

    return 1;
}

//===========================================
// потрібно передивитись!!!!!!!!!!
// Натиснута кнопка завантажити зображення
//===========================================
function form_add_picture( $type_name ) {
   $file_name = $_FILES['up_file']['name'];
   if ( empty( $file_name )) {
      display_message( _e( "No file selected for upload!", 'computer-accounting' ));
   }
   $path_to = str_replace('/includes', '', plugin_dir_path(__FILE__)) ."images/".$type_name."/";
   echo $path_to;
   if (! is_dir($path_to)) {
      mkdir($path_to, 0770);
      chmod($path_to, 0770);
   }
   // обработка ошибок
   switch ($_FILES['up_file']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            display_message( _e( 'The file was not uploaded.', 'computer-accounting' ));
        case UPLOAD_ERR_INI_SIZE:
            display_message( _e( 'The size of the received file has exceeded the maximum allowed size, which is specified by the directive upload_max_filesize.', 'computer-accounting' ));
        case UPLOAD_ERR_FORM_SIZE:
            display_message( _e( 'The size of the uploaded file exceeded the value of MAX_FILE_SIZE.', 'computer-accounting' ));
        default:
            display_message( _e( 'Unknown error.', 'computer-accounting' ));
    }
    // проверим тип файла
    $info_mime = new finfo(FILEINFO_MIME_TYPE);
    if ( false === $ext = array_search(
        $info_mime -> file($_FILES['up_file']['tmp_name']),
        array( 'jpg' => 'image/jpeg',
               'png' => 'image/png',
               'gif' => 'image/gif',
        ),
        true
   )) {
      display_message( _e( 'Invalid file format. Are allowed: GIF, JPEG, PNG', 'computer-accounting' ));
   }
   $file_size = getimagesize ($_FILES['up_file']['tmp_name']);
   if (( intval($file_size[0]) > 250) or (intval($file_size[1]) > 250)) {
      display_message( _e( 'Image size should not exceed 250X250 pixels', 'computer-accounting' ));
   }
   $file_in   = $path_to."type-tmp";
   $tmp_file  = $_FILES['up_file']['tmp_name'];
   if ( file_exists( $tmp_file )) {
      if ( ! copy( $tmp_file, $file_in )) {
         display_message( "Could not copy $file_name" );
      }
   } else {
     display_message( "The file not ".$tmp_file." exists" );
   }
   $_SESSION["image"] = $path_to."type-tmp";
   $_SESSION["form_type"] = "edit";
}

//=============================================
// Функция додає маленьку іконку перед назвою елемента меню у верхній адмін-панелі WordPress
//=============================================
function admin_bar_menu_title_icon( $icon_url, $title ){

   $iconspan = '<span class="custom-icon"
                   style="float:left;
                   width:16px !important;
                   height:16px !important;
                   margin-left: 5px !important;
                   margin-top: 5px !important;
                   background-image:url(\'' . $icon_url . '\');">
                </span> &nbsp; &nbsp;';

   return $iconspan . $title;
}



//===========================================
// Отримати відображуване ім'я користувача з WP_User / user_login fallback
//===========================================
if ( ! function_exists( 'wpaf_get_user_display_name' ) ) {
   /**
    * Resolve a human-readable display name for a WordPress user.
    *
    * The helper is intentionally generic and can be reused by any plugin or
    * admin screen that needs a stable display label for a WP user without
    * duplicating `display_name => user_login` fallback logic.
    *
    * @param mixed       $user     WP_User instance or compatible value.
    * @param string|mixed $fallback Optional fallback string.
    *
    * @return string
    */
   function wpaf_get_user_display_name( $user, $fallback = '' ) {
      if ( ! ( $user instanceof WP_User ) ) {
         return is_scalar( $fallback ) ? (string) $fallback : '';
      }

      $user_name = (string) $user->display_name;

      if ( $user_name === '' ) {
         $user_name = (string) $user->user_login;
      }

      if ( $user_name !== '' ) {
         return $user_name;
      }

      return is_scalar( $fallback ) ? (string) $fallback : '';
   }
}

//===========================================
// Отримати відображуване ім'я користувача за ID
//===========================================
if ( ! function_exists( 'wpaf_get_user_display_name_by_id' ) ) {
   /**
    * Resolve a human-readable display name by WordPress user ID.
    *
    * Invalid IDs return the supplied fallback. Missing users fall back to
    * `#<id>` unless a custom fallback string was provided by the caller.
    *
    * @param int|string|mixed $user_id  User ID.
    * @param string|mixed     $fallback Optional fallback for invalid/missing users.
    *
    * @return string
    */
   function wpaf_get_user_display_name_by_id( $user_id, $fallback = '' ) {
      $user_id = (int) $user_id;

      if ( $user_id <= 0 ) {
         return is_scalar( $fallback ) ? (string) $fallback : '';
      }

      $user = get_userdata( $user_id );

      if ( ! ( $user instanceof WP_User ) ) {
         if ( is_scalar( $fallback ) && (string) $fallback !== '' ) {
            return (string) $fallback;
         }

         return '#' . $user_id;
      }

      return wpaf_get_user_display_name( $user, '#' . $user_id );
   }
}

//===========================================
// Отримати відображуване ім'я поточного користувача
//===========================================
if ( ! function_exists( 'wpaf_get_current_user_display_name' ) ) {
   /**
    * Resolve a human-readable display name for the current user.
    *
    * @param string|mixed $fallback Optional fallback string.
    *
    * @return string
    */
   function wpaf_get_current_user_display_name( $fallback = '' ) {
      return wpaf_get_user_display_name( wp_get_current_user(), $fallback );
   }
}

//===========================================
// Універсальні admin helper-механізми для повторного використання в плагінах
//
// Важливо:
// - тут має бути тільки загальна інфраструктура
// - тут не повинно бути бізнес-логіки конкретного модуля
// - allowlists, screen names, notice codes, permissions, query logic,
//   revision logic та інші правила залишаються у плагіні-споживачі
//===========================================

//===========================================
// Побудувати signed token для універсальних helper-механізмів
//===========================================
if ( ! function_exists( 'wpaf_build_signed_token' ) ) {
   /**
    * Build a signed token for lightweight admin state payloads.
    *
    * Use this helper when a plugin needs to keep a small internal state in the
    * query string without exposing multiple technical parameters. The payload
    * must remain a simple associative array owned by the caller module.
    *
    * The helper is intentionally generic: it does not know anything about
    * orders, revisions, statuses, tables, or business rules.
    *
    * @param array        $payload   Arbitrary associative payload to encode.
    * @param string|mixed $namespace Logical namespace that separates tokens of
    *                                different modules or token types.
    *
    * @return string Signed token or empty string on failure.
    */
   function wpaf_build_signed_token( array $payload, $namespace ) {
      $namespace = sanitize_key( (string) $namespace );

      if ( $namespace === '' ) {
         return '';
      }

      $packet = wp_json_encode(
         array(
            'v'  => 1,
            'ns' => $namespace,
            'd'  => $payload,
         )
      );

      if ( ! is_string( $packet ) || $packet === '' ) {
         return '';
      }

      $signature = hash_hmac( 'sha256', $packet, wp_salt( 'auth' ) );
      $encoded   = rtrim( strtr( base64_encode( $packet ), '+/', '-_' ), '=' );

      return $encoded . '.' . $signature;
   }
}

//===========================================
// Розкодувати signed token універсальних helper-механізмів
//===========================================
if ( ! function_exists( 'wpaf_parse_signed_token' ) ) {
   /**
    * Parse and verify a signed token created by wpaf_build_signed_token().
    *
    * The function returns only the decoded payload array. Validation of allowed
    * keys, expected screens, and business semantics must stay in the caller
    * module.
    *
    * @param string|mixed $token     Signed token from the request or URL.
    * @param string|mixed $namespace Namespace expected by the caller.
    *
    * @return array Decoded payload or empty array when the token is invalid.
    */
   function wpaf_parse_signed_token( $token, $namespace ) {
      $token     = is_string( $token ) ? trim( $token ) : '';
      $namespace = sanitize_key( (string) $namespace );

      if ( $token === '' || $namespace === '' || strpos( $token, '.' ) === false ) {
         return array();
      }

      list( $encoded, $signature ) = explode( '.', $token, 2 );

      if ( $encoded === '' || $signature === '' ) {
         return array();
      }

      $padding = strlen( $encoded ) % 4;

      if ( $padding > 0 ) {
         $encoded .= str_repeat( '=', 4 - $padding );
      }

      $packet = base64_decode( strtr( $encoded, '-_', '+/' ), true );

      if ( ! is_string( $packet ) || $packet === '' ) {
         return array();
      }

      $expected_signature = hash_hmac( 'sha256', $packet, wp_salt( 'auth' ) );

      if ( ! hash_equals( $expected_signature, $signature ) ) {
         return array();
      }

      $decoded = json_decode( $packet, true );

      if ( ! is_array( $decoded ) || ! isset( $decoded['ns'], $decoded['d'] ) ) {
         return array();
      }

      if ( sanitize_key( (string) $decoded['ns'] ) !== $namespace ) {
         return array();
      }

      return is_array( $decoded['d'] ) ? $decoded['d'] : array();
   }
}


//===========================================
// Побудувати URL admin.php?page=... для універсальних helper-механізмів
//===========================================
if ( ! function_exists( 'wpaf_admin_page_url' ) ) {
   /**
    * Build a standard admin.php?page=... URL for plugin screens.
    *
    * @param string|mixed $page Admin page slug.
    * @param array        $args Additional query arguments.
    *
    * @return string
    */
   function wpaf_admin_page_url( $page, array $args = array() ) {
      $page = sanitize_key( (string) $page );

      if ( $page === '' ) {
         return add_query_arg( $args, admin_url( 'admin.php' ) );
      }

      return add_query_arg(
         array_merge(
            array( 'page' => $page ),
            $args
         ),
         admin_url( 'admin.php' )
      );
   }
}

//===========================================
// Нормалізувати sanitize_key() значення по allowlist
//===========================================
if ( ! function_exists( 'wpaf_normalize_allowed_key' ) ) {
   /**
    * Sanitize a key and validate it against an allowlist.
    *
    * Useful for generic route names, notice codes, list scopes, or similar
    * module-owned state values.
    *
    * @param string|mixed $value   Raw value.
    * @param array        $allowed Allowed sanitized keys.
    * @param string|mixed $default Default value if the raw value is not allowed.
    *
    * @return string
    */
   function wpaf_normalize_allowed_key( $value, array $allowed, $default = '' ) {
      $value   = sanitize_key( (string) $value );
      $default = sanitize_key( (string) $default );

      if ( in_array( $value, $allowed, true ) ) {
         return $value;
      }

      return in_array( $default, $allowed, true ) ? $default : '';
   }
}

//===========================================
// Query args з signed token для універсальних helper-механізмів
//===========================================
if ( ! function_exists( 'wpaf_get_signed_query_args' ) ) {
   /**
    * Convert a payload into query args with one signed token parameter.
    *
    * @param string|mixed $param_name Query parameter name that will store the token.
    * @param array        $payload    Module-owned state payload.
    * @param string|mixed $namespace  Token namespace.
    *
    * @return array Query args array ready for add_query_arg().
    */
   function wpaf_get_signed_query_args( $param_name, array $payload, $namespace ) {
      $param_name = sanitize_key( (string) $param_name );

      if ( $param_name === '' ) {
         return array();
      }

      if ( ! function_exists( 'wpaf_build_signed_token' ) ) {
         return array();
      }

      $token = wpaf_build_signed_token( $payload, $namespace );

      if ( ! is_string( $token ) || $token === '' ) {
         return array();
      }

      return array( $param_name => $token );
   }
}

//===========================================
// Прочитати signed payload з request для універсальних helper-механізмів
//===========================================
if ( ! function_exists( 'wpaf_get_request_signed_payload' ) ) {
   /**
    * Read and verify a signed payload from the current request.
    *
    * @param string|mixed $param_name Request parameter name.
    * @param string|mixed $namespace  Token namespace.
    *
    * @return array Decoded payload or empty array.
    */
   function wpaf_get_request_signed_payload( $param_name, $namespace ) {
      $param_name = sanitize_key( (string) $param_name );

      if ( $param_name === '' || empty( $_REQUEST[ $param_name ] ) ) {
         return array();
      }

      if ( ! function_exists( 'wpaf_parse_signed_token' ) ) {
         return array();
      }

      $token = sanitize_text_field( wp_unslash( $_REQUEST[ $param_name ] ) );

      return wpaf_parse_signed_token( $token, $namespace );
   }
}

//===========================================
// Побудувати admin URL з signed token параметром
//===========================================
if ( ! function_exists( 'wpaf_admin_page_signed_url' ) ) {
   /**
    * Build an admin.php?page=... URL that includes one signed state token.
    *
    * @param string|mixed $page       Admin page slug.
    * @param string|mixed $param_name Query parameter name for the token.
    * @param array        $payload    Module-owned payload.
    * @param string|mixed $namespace  Token namespace.
    * @param array        $extra      Additional plain query arguments.
    *
    * @return string
    */
   function wpaf_admin_page_signed_url( $page, $param_name, array $payload, $namespace, array $extra = array() ) {
      $signed_args = function_exists( 'wpaf_get_signed_query_args' )
         ? wpaf_get_signed_query_args( $param_name, $payload, $namespace )
         : array();

      return wpaf_admin_page_url( $page, array_merge( $extra, $signed_args ) );
   }
}


//===========================================
// Нормалізувати scalar значення для hidden fields універсальних form helper-ів
//===========================================
if ( ! function_exists( 'wpaf_prepare_hidden_fields' ) ) {
   /**
    * Normalize hidden field values for reusable admin forms.
    *
    * Only scalar values are kept. Complex nested business payloads should stay
    * in the owning plugin and should not be passed through this helper.
    *
    * @param array $fields Raw hidden field map.
    *
    * @return array Sanitized hidden field map.
    */
   function wpaf_prepare_hidden_fields( array $fields ) {
      $prepared = array();

      foreach ( $fields as $name => $value ) {
         $name = sanitize_key( (string) $name );

         if ( $name === '' ) {
            continue;
         }

         if ( is_bool( $value ) ) {
            $value = $value ? '1' : '0';
         } elseif ( is_scalar( $value ) ) {
            $value = (string) $value;
         } else {
            continue;
         }

         $prepared[ $name ] = $value;
      }

      return $prepared;
   }
}

//===========================================
// Вивести hidden fields для універсальних form helper-ів
//===========================================
if ( ! function_exists( 'wpaf_render_hidden_fields' ) ) {
   /**
    * Render hidden input elements for a sanitized field map.
    *
    * @param array $fields Hidden field map.
    *
    * @return void
    */
   function wpaf_render_hidden_fields( array $fields ) {
      foreach ( wpaf_prepare_hidden_fields( $fields ) as $name => $value ) {
         echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" />';
      }
   }
}

//===========================================
// Відкрити універсальну POST-form для admin screens
//===========================================
if ( ! function_exists( 'wpaf_render_post_form_start' ) ) {
   /**
    * Render the opening tag and standard internals for a reusable admin POST form.
    *
    * Supported args:
    * - action
    * - class
    * - id
    * - nonce_action
    * - nonce_name
    * - hidden
    *
    * @param array $args Form rendering options.
    *
    * @return void
    */
   function wpaf_render_post_form_start( array $args = array() ) {
      $action       = isset( $args['action'] ) ? (string) $args['action'] : '';
      $class        = isset( $args['class'] ) ? (string) $args['class'] : '';
      $id           = isset( $args['id'] ) ? (string) $args['id'] : '';
      $nonce_action = isset( $args['nonce_action'] ) ? (string) $args['nonce_action'] : '';
      $nonce_name   = isset( $args['nonce_name'] ) ? (string) $args['nonce_name'] : '';
      $hidden       = isset( $args['hidden'] ) && is_array( $args['hidden'] ) ? $args['hidden'] : array();

      $attributes = ' method="post"';

      if ( $action !== '' ) {
         $attributes .= ' action="' . esc_url( $action ) . '"';
      }

      if ( $class !== '' ) {
         $attributes .= ' class="' . esc_attr( $class ) . '"';
      }

      if ( $id !== '' ) {
         $attributes .= ' id="' . esc_attr( $id ) . '"';
      }

      echo '<form' . $attributes . '>';

      if ( $nonce_action !== '' && $nonce_name !== '' ) {
         wp_nonce_field( $nonce_action, $nonce_name );
      }

      if ( ! empty( $hidden ) ) {
         wpaf_render_hidden_fields( $hidden );
      }
   }
}

//===========================================
// Закрити універсальну admin form
//===========================================
if ( ! function_exists( 'wpaf_render_form_end' ) ) {
   /**
    * Close a form opened by wpaf_render_post_form_start().
    *
    * @return void
    */
   function wpaf_render_form_end() {
      echo '</form>';
   }
}

//===========================================
// Вивести універсальні action buttons для admin form/layout
//===========================================
if ( ! function_exists( 'wpaf_render_action_buttons' ) ) {
   /**
    * Render a generic action-button block for admin forms and confirm screens.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - submit_name
    * - submit_label
    * - submit_value
    * - submit_class
    * - submit_style
    * - cancel_url
    * - cancel_label
    * - cancel_class
    * - extra_actions (pre-rendered HTML owned by the caller)
    * - trailing_actions (optional trailing action HTML, rendered as a right-side group)
    * - leading_wrapper_class
    * - leading_wrapper_style
    * - trailing_wrapper_class
    * - trailing_wrapper_style
    *
    * @param array $args Button block options.
    *
    * @return void
    */
   function wpaf_render_action_buttons( array $args = array() ) {
      $wrapper_class          = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons';
      $wrapper_style          = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '';
      $submit_name            = isset( $args['submit_name'] ) ? (string) $args['submit_name'] : '';
      $submit_label           = isset( $args['submit_label'] ) ? (string) $args['submit_label'] : '';
      $submit_value           = array_key_exists( 'submit_value', $args ) ? (string) $args['submit_value'] : '1';
      $submit_class           = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $submit_style           = isset( $args['submit_style'] ) ? (string) $args['submit_style'] : '';
      $cancel_url             = isset( $args['cancel_url'] ) ? (string) $args['cancel_url'] : '';
      $cancel_label           = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $cancel_class           = isset( $args['cancel_class'] ) ? (string) $args['cancel_class'] : 'button';
      $extra_actions          = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $trailing_actions       = isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '';
      $leading_wrapper_class  = isset( $args['leading_wrapper_class'] ) ? (string) $args['leading_wrapper_class'] : 'wpaf-action-buttons-leading';
      $leading_wrapper_style  = isset( $args['leading_wrapper_style'] ) ? (string) $args['leading_wrapper_style'] : '';
      $trailing_wrapper_class = isset( $args['trailing_wrapper_class'] ) ? (string) $args['trailing_wrapper_class'] : 'wpaf-action-buttons-trailing';
      $trailing_wrapper_style = isset( $args['trailing_wrapper_style'] ) ? (string) $args['trailing_wrapper_style'] : '';

      if ( '' !== $trailing_actions ) {
         if ( '' === $wrapper_style && function_exists( 'wpaf_get_form_action_buttons_wrapper_style' ) ) {
            $wrapper_style = wpaf_get_form_action_buttons_wrapper_style();
         }

         if ( '' === $leading_wrapper_style && function_exists( 'wpaf_get_form_action_buttons_wrapper_style' ) ) {
            $leading_wrapper_style = wpaf_get_form_action_buttons_wrapper_style();
         }

         if ( '' === $trailing_wrapper_style && function_exists( 'wpaf_get_form_action_buttons_wrapper_style' ) ) {
            $trailing_wrapper_style = 'margin-left:auto;' . wpaf_get_form_action_buttons_wrapper_style();
         }
      }

      $wrapper_style_attr          = $wrapper_style !== '' ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';
      $leading_wrapper_style_attr  = $leading_wrapper_style !== '' ? ' style="' . esc_attr( $leading_wrapper_style ) . '"' : '';
      $trailing_wrapper_style_attr = $trailing_wrapper_style !== '' ? ' style="' . esc_attr( $trailing_wrapper_style ) . '"' : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '"' . $wrapper_style_attr . '>';

      if ( '' !== $trailing_actions ) {
         echo '<div class="' . esc_attr( $leading_wrapper_class ) . '"' . $leading_wrapper_style_attr . '>';
      }

      if ( $submit_name !== '' && $submit_label !== '' ) {
         $style_attr = $submit_style !== '' ? ' style="' . esc_attr( $submit_style ) . '"' : '';
         echo '<button type="submit" name="' . esc_attr( $submit_name ) . '" value="' . esc_attr( $submit_value ) . '" class="' . esc_attr( $submit_class ) . '"' . $style_attr . '>' . esc_html( $submit_label ) . '</button>';
      }

      if ( $cancel_url !== '' && $cancel_label !== '' ) {
         echo '<a href="' . esc_url( $cancel_url ) . '" class="' . esc_attr( $cancel_class ) . '">' . esc_html( $cancel_label ) . '</a>';
      }

      if ( $extra_actions !== '' ) {
         echo $extra_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      if ( '' !== $trailing_actions ) {
         echo '</div>';
         echo '<div class="' . esc_attr( $trailing_wrapper_class ) . '"' . $trailing_wrapper_style_attr . '>';
         echo $trailing_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '</div>';
      }

      echo '</div>';
   }
}


//===========================================
// Побудувати inline-style для компактного ряду form action buttons
//===========================================
if ( ! function_exists( 'wpaf_get_form_action_buttons_wrapper_style' ) ) {
   /**
    * Build a reusable inline style string for compact admin form action rows.
    *
    * The helper stays presentation-only: it only standardizes the button-row
    * wrapper layout and does not change submit handling, URLs, nonces, or
    * module business logic.
    *
    * Supported args:
    * - display
    * - align_items
    * - gap
    * - flex_wrap
    *
    * @param array $args Optional style overrides.
    *
    * @return string
    */
   function wpaf_get_form_action_buttons_wrapper_style( array $args = array() ) {
      $defaults = array(
         'display'     => 'flex',
         'align_items' => 'center',
         'gap'         => '6px',
         'flex_wrap'   => 'wrap',
      );

      $args = wp_parse_args( $args, $defaults );

      return 'display:' . trim( (string) $args['display'] ) . ';align-items:' . trim( (string) $args['align_items'] ) . ';gap:' . trim( (string) $args['gap'] ) . ';flex-wrap:' . trim( (string) $args['flex_wrap'] ) . ';';
   }
}


//===========================================
// Побудувати inline-style для confirm layout wrapper
//===========================================
if ( ! function_exists( 'wpaf_get_confirm_layout_wrapper_style' ) ) {
   /**
    * Build a reusable inline style string for compact confirm-layout wrappers.
    *
    * The helper stays presentation-only: it only standardizes spacing around
    * confirm forms and does not change submit flow, state handling, or module
    * business logic.
    *
    * Supported args:
    * - margin_top
    *
    * @param array $args Optional style overrides.
    *
    * @return string
    */
   function wpaf_get_confirm_layout_wrapper_style( array $args = array() ) {
      $margin_top = isset( $args['margin_top'] ) ? trim( (string) $args['margin_top'] ) : '10px';

      return 'margin-top:' . $margin_top . ';';
   }
}


//===========================================
// Підготувати універсальні action links для admin screens
//===========================================
if ( ! function_exists( 'wpaf_prepare_action_link_items' ) ) {
   /**
    * Normalize a list of action-link definitions for admin toolbars and screens.
    *
    * Each valid item may contain:
    * - url (required)
    * - label (required)
    * - class
    * - id
    * - title
    * - target
    * - rel
    *
    * @param array $items Raw action-link definitions.
    *
    * @return array Prepared link definitions.
    */
   function wpaf_prepare_action_link_items( array $items ) {
      $prepared = array();

      foreach ( $items as $item ) {
         if ( ! is_array( $item ) ) {
            continue;
         }

         $url   = isset( $item['url'] ) ? (string) $item['url'] : '';
         $label = isset( $item['label'] ) ? (string) $item['label'] : '';

         if ( $url === '' || $label === '' ) {
            continue;
         }

         $prepared_item = array(
            'url'   => $url,
            'label' => $label,
            'class' => isset( $item['class'] ) ? (string) $item['class'] : '',
         );

         if ( isset( $item['id'] ) && is_scalar( $item['id'] ) ) {
            $prepared_item['id'] = (string) $item['id'];
         }

         if ( isset( $item['title'] ) && is_scalar( $item['title'] ) ) {
            $prepared_item['title'] = (string) $item['title'];
         }

         if ( isset( $item['target'] ) && is_scalar( $item['target'] ) ) {
            $prepared_item['target'] = (string) $item['target'];
         }

         if ( isset( $item['rel'] ) && is_scalar( $item['rel'] ) ) {
            $prepared_item['rel'] = (string) $item['rel'];
         }

         $prepared[] = $prepared_item;
      }

      return $prepared;
   }
}

//===========================================
// Повернути HTML універсальних action links для admin screens
//===========================================
if ( ! function_exists( 'wpaf_get_action_links_html' ) ) {
   /**
    * Build HTML for a reusable action-link block.
    *
    * Supported args:
    * - separator
    * - wrapper_tag
    * - wrapper_class
    * - item_class
    *
    * @param array $items Link definitions.
    * @param array $args  Rendering options.
    *
    * @return string
    */
   function wpaf_get_action_links_html( array $items, array $args = array() ) {
      $prepared           = wpaf_prepare_action_link_items( $items );
      $separator          = isset( $args['separator'] ) ? (string) $args['separator'] : ' ';
      $wrapper_tag        = isset( $args['wrapper_tag'] ) ? sanitize_key( (string) $args['wrapper_tag'] ) : '';
      $wrapper_class      = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : '';
      $default_item_class = isset( $args['item_class'] ) ? (string) $args['item_class'] : '';

      if ( empty( $prepared ) ) {
         return '';
      }

      if ( $wrapper_tag !== '' && ! in_array( $wrapper_tag, array( 'div', 'span', 'p', 'nav' ), true ) ) {
         $wrapper_tag = 'div';
      }

      $html_items = array();

      foreach ( $prepared as $item ) {
         $classes = trim( $default_item_class . ' ' . ( isset( $item['class'] ) ? $item['class'] : '' ) );
         $attrs   = ' href="' . esc_url( $item['url'] ) . '"';

         if ( $classes !== '' ) {
            $attrs .= ' class="' . esc_attr( $classes ) . '"';
         }

         if ( ! empty( $item['id'] ) ) {
            $attrs .= ' id="' . esc_attr( $item['id'] ) . '"';
         }

         if ( ! empty( $item['title'] ) ) {
            $attrs .= ' title="' . esc_attr( $item['title'] ) . '"';
         }

         if ( ! empty( $item['target'] ) ) {
            $attrs .= ' target="' . esc_attr( $item['target'] ) . '"';
         }

         if ( ! empty( $item['rel'] ) ) {
            $attrs .= ' rel="' . esc_attr( $item['rel'] ) . '"';
         }

         $html_items[] = '<a' . $attrs . '>' . esc_html( $item['label'] ) . '</a>';
      }

      $html = implode( $separator, $html_items );

      if ( $wrapper_tag !== '' ) {
         $class_attr = $wrapper_class !== '' ? ' class="' . esc_attr( $wrapper_class ) . '"' : '';
         $html       = '<' . $wrapper_tag . $class_attr . '>' . $html . '</' . $wrapper_tag . '>';
      }

      return $html;
   }
}


//===========================================
// Побудувати HTML для leading/trailing action link groups
//===========================================
if ( ! function_exists( 'wpaf_get_action_link_groups_html' ) ) {
   /**
    * Build reusable HTML for split action-link groups.
    *
    * This helper stays render-only: callers still own URLs, labels, permissions,
    * routing, and all module business semantics. It simply converts prepared
    * leading/trailing link definitions into HTML blocks that can be passed to
    * shared action-row renderers.
    *
    * Supported args:
    * - leading_args  (render args for wpaf_get_action_links_html on leading links)
    * - trailing_args (render args for wpaf_get_action_links_html on trailing links)
    *
    * @param array $leading_items  Leading action-link definitions.
    * @param array $trailing_items Trailing action-link definitions.
    * @param array $args           Optional render configuration.
    *
    * @return array{
    *    extra_actions:string,
    *    trailing_actions:string
    * }
    */
   function wpaf_get_action_link_groups_html( array $leading_items = array(), array $trailing_items = array(), array $args = array() ) {
      $leading_args  = isset( $args['leading_args'] ) && is_array( $args['leading_args'] ) ? $args['leading_args'] : array();
      $trailing_args = isset( $args['trailing_args'] ) && is_array( $args['trailing_args'] ) ? $args['trailing_args'] : array();

      return array(
         'extra_actions'    => wpaf_get_action_links_html( $leading_items, $leading_args ),
         'trailing_actions' => wpaf_get_action_links_html( $trailing_items, $trailing_args ),
      );
   }
}

//===========================================
// Вивести універсальні action links для admin screens
//===========================================
if ( ! function_exists( 'wpaf_render_action_links' ) ) {
   /**
    * Echo a reusable action-link block.
    *
    * @param array $items Link definitions.
    * @param array $args  Rendering options.
    *
    * @return void
    */
   function wpaf_render_action_links( array $items, array $args = array() ) {
      echo wpaf_get_action_links_html( $items, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
   }
}


//===========================================
// Побудувати action links block для notice / fallback screens
//===========================================
if ( ! function_exists( 'wpaf_get_notice_action_links_html' ) ) {
   /**
    * Build a small reusable action-link block for notice/error fallback states.
    *
    * This helper stays render-only. Callers still own messages, URLs, labels,
    * permissions, routing, and all module business semantics.
    *
    * Supported args:
    * - wrapper_tag   (default: p)
    * - wrapper_class
    * - separator
    * - item_class
    *
    * @param array $items Action-link definitions.
    * @param array $args  Rendering options.
    *
    * @return string
    */
   function wpaf_get_notice_action_links_html( array $items, array $args = array() ) {
      $args = array_merge(
         array(
            'wrapper_tag' => 'p',
         ),
         $args
      );

      return wpaf_get_action_links_html( $items, $args );
   }
}


//===========================================
// Побудувати стандартний button action item для admin screens
//===========================================
if ( ! function_exists( 'wpaf_get_button_action_item' ) ) {
   /**
    * Build a reusable button-like action-link item.
    *
    * This helper is intentionally tiny and render-agnostic. The caller still
    * owns the destination URL, label text, permissions, routing, and any
    * screen-specific semantics.
    *
    * Supported args:
    * - class   (default: button)
    * - id
    * - title
    * - target
    * - rel
    *
    * @param string|mixed $url   Destination URL.
    * @param string|mixed $label Visible label text.
    * @param array        $args  Action item options.
    *
    * @return array
    */
   function wpaf_get_button_action_item( $url, $label, array $args = array() ) {
      $item = array(
         'url'   => is_scalar( $url ) ? trim( (string) $url ) : '',
         'label' => is_scalar( $label ) ? trim( (string) $label ) : '',
         'class' => isset( $args['class'] ) ? (string) $args['class'] : 'button',
      );

      foreach ( array( 'id', 'title', 'target', 'rel' ) as $key ) {
         if ( isset( $args[ $key ] ) && '' !== (string) $args[ $key ] ) {
            $item[ $key ] = (string) $args[ $key ];
         }
      }

      return $item;
   }
}

//===========================================
// Побудувати стандартний primary button action item для admin screens
//===========================================
if ( ! function_exists( 'wpaf_get_primary_button_action_item' ) ) {
   /**
    * Build a reusable primary button-like action-link item.
    *
    * This helper stays render-only. It only prepares a shared primary button
    * action definition and does not own any module-specific semantics.
    *
    * Supported args:
    * - class   (default: button button-primary)
    * - id
    * - title
    * - target
    * - rel
    *
    * @param string|mixed $url   Destination URL.
    * @param string|mixed $label Visible label text.
    * @param array        $args  Action item options.
    *
    * @return array
    */
   function wpaf_get_primary_button_action_item( $url, $label, array $args = array() ) {
      if ( empty( $args['class'] ) ) {
         $args['class'] = 'button button-primary';
      }

      return wpaf_get_button_action_item( $url, $label, $args );
   }
}


//===========================================
// Побудувати набір button action items із компактних definitions
//===========================================
if ( ! function_exists( 'wpaf_build_button_action_items' ) ) {
   /**
    * Build reusable button-like action items from compact definitions.
    *
    * This helper stays generic and render-only. It lets modules describe
    * small action sets declaratively instead of repeatedly assembling the same
    * url/label/class arrays by hand.
    *
    * Each definition supports:
    * - url       (required)
    * - label     (required)
    * - condition (default: true)
    * - primary   (default: false)
    * - class
    * - id
    * - title
    * - target
    * - rel
    *
    * @param array $definitions Compact action item definitions.
    *
    * @return array
    */
   function wpaf_build_button_action_items( array $definitions ) {
      $items = array();

      foreach ( $definitions as $definition ) {
         if ( ! is_array( $definition ) ) {
            continue;
         }

         if ( array_key_exists( 'condition', $definition ) && ! $definition['condition'] ) {
            continue;
         }

         $url   = isset( $definition['url'] ) && is_scalar( $definition['url'] ) ? trim( (string) $definition['url'] ) : '';
         $label = isset( $definition['label'] ) && is_scalar( $definition['label'] ) ? trim( (string) $definition['label'] ) : '';

         if ( '' === $url || '' === $label ) {
            continue;
         }

         $item_args = array();

         foreach ( array( 'class', 'id', 'title', 'target', 'rel' ) as $key ) {
            if ( isset( $definition[ $key ] ) && '' !== (string) $definition[ $key ] ) {
               $item_args[ $key ] = (string) $definition[ $key ];
            }
         }

         if ( ! empty( $definition['primary'] ) ) {
            $items[] = wpaf_get_primary_button_action_item( $url, $label, $item_args );
         } else {
            $items[] = wpaf_get_button_action_item( $url, $label, $item_args );
         }
      }

      return $items;
   }
}


//===========================================
// Побудувати leading/trailing button action groups із компактних definitions
//===========================================
if ( ! function_exists( 'wpaf_get_button_action_groups_html' ) ) {
   /**
    * Build reusable split action-link HTML from compact button definitions.
    *
    * This helper combines `wpaf_build_button_action_items()` with
    * `wpaf_get_action_link_groups_html()` so modules can describe readonly /
    * fallback action groups declaratively without repeatedly building the
    * intermediate action-item arrays by hand.
    *
    * Supported args:
    * - group_args (arguments forwarded to wpaf_get_action_link_groups_html())
    *
    * @param array $leading_definitions  Compact leading action definitions.
    * @param array $trailing_definitions Compact trailing action definitions.
    * @param array $args                 Optional render arguments.
    *
    * @return array{extra_actions:string,trailing_actions:string}
    */
   function wpaf_get_button_action_groups_html( array $leading_definitions = array(), array $trailing_definitions = array(), array $args = array() ) {

      $group_args = isset( $args['group_args'] ) && is_array( $args['group_args'] )
         ? $args['group_args']
         : array();

      return wpaf_get_action_link_groups_html(
         wpaf_build_button_action_items( $leading_definitions ),
         wpaf_build_button_action_items( $trailing_definitions ),
         $group_args
      );
   }
}

//===========================================
// Побудувати заголовок сторінки з action links
//===========================================
if ( ! function_exists( 'wpaf_get_title_with_actions_html' ) ) {
   /**
    * Build HTML for a page title with optional action links.
    *
    * This helper is intentionally render-only. The caller still owns the title
    * text, action labels, permissions, URLs, and any business semantics.
    *
    * Supported args:
    * - layout: inline|right (default: inline)
    * - actions_args: arguments forwarded to wpaf_get_action_links_html()
    * - actions_wrapper_class: extra class for the actions wrapper
    *
    * @param string|mixed $title Raw title text.
    * @param array        $items Action-link definitions.
    * @param array        $args  Rendering options.
    *
    * @return string
    */
   function wpaf_get_title_with_actions_html( $title, array $items = array(), array $args = array() ) {
      $title                 = is_scalar( $title ) ? trim( (string) $title ) : '';
      $layout                = isset( $args['layout'] ) ? sanitize_key( (string) $args['layout'] ) : 'inline';
      $actions_args          = isset( $args['actions_args'] ) && is_array( $args['actions_args'] ) ? $args['actions_args'] : array();
      $actions_wrapper_class = isset( $args['actions_wrapper_class'] ) ? (string) $args['actions_wrapper_class'] : '';
      $actions_html          = wpaf_get_action_links_html( $items, $actions_args );

      if ( $title === '' && $actions_html === '' ) {
         return '';
      }

      if ( $actions_html === '' ) {
         return esc_html( $title );
      }

      if ( ! in_array( $layout, array( 'inline', 'right' ), true ) ) {
         $layout = 'inline';
      }

      $wrapper_style = 'display:inline-block;white-space:nowrap;margin-left:4px;vertical-align:middle;';

      if ( $layout === 'right' ) {
         $wrapper_style = 'float:right;white-space:nowrap;margin-left:12px;';
      }

      $wrapper_class_attr = $actions_wrapper_class !== '' ? ' class="' . esc_attr( $actions_wrapper_class ) . '"' : '';
      $title_html         = esc_html( $title );

      if ( $layout !== 'right' ) {
         if ( $title_html === '' ) {
            return $actions_html;
         }

         return $title_html . ' ' . $actions_html;
      }

      $actions_wrapper = '<span' . $wrapper_class_attr . ' style="' . esc_attr( $wrapper_style ) . '">' . $actions_html . '</span>';

      if ( $title_html === '' ) {
         return $actions_wrapper;
      }

      return $title_html . $actions_wrapper;
   }
}



//===========================================
// Побудувати стандартний title action item для page header
//===========================================
if ( ! function_exists( 'wpaf_get_title_action_item' ) ) {
   /**
    * Build a reusable page-title action item.
    *
    * This helper stays render-only. The caller still owns the URL, label,
    * permissions, and any screen-specific navigation semantics.
    *
    * Supported args:
    * - class
    *
    * @param string|mixed $url   Destination URL.
    * @param string|mixed $label Action label.
    * @param array        $args  Item options.
    *
    * @return array
    */
   function wpaf_get_title_action_item( $url, $label, array $args = array() ) {
      $class = isset( $args['class'] ) ? (string) $args['class'] : 'page-title-action';

      return array(
         'url'   => is_scalar( $url ) ? trim( (string) $url ) : '',
         'label' => is_scalar( $label ) ? trim( (string) $label ) : '',
         'class' => $class,
      );
   }
}

//===========================================
// Побудувати стандартний primary title action item для page header
//===========================================
if ( ! function_exists( 'wpaf_get_primary_title_action_item' ) ) {
   /**
    * Build a reusable primary page-title action item.
    *
    * This helper is intentionally render-only. The caller still owns the URL,
    * label, permissions, and any screen-specific navigation semantics.
    *
    * Supported args:
    * - class
    *
    * @param string|mixed $url   Destination URL.
    * @param string|mixed $label Action label.
    * @param array        $args  Item options.
    *
    * @return array
    */
   function wpaf_get_primary_title_action_item( $url, $label, array $args = array() ) {
      if ( ! isset( $args['class'] ) || ! is_scalar( $args['class'] ) || trim( (string) $args['class'] ) === '' ) {
         $args['class'] = 'page-title-action wpaf-button wpaf-button-primary';
      }

      return wpaf_get_title_action_item( $url, $label, $args );
   }
}

//===========================================
// Побудувати title action items із compact definitions
//===========================================
if ( ! function_exists( 'wpaf_build_title_action_items' ) ) {
   /**
    * Build a reusable array of page-title action items from compact definitions.
    *
    * Each definition may contain:
    * - condition (bool, optional)
    * - primary (bool, optional)
    * - url
    * - label
    * - class (optional)
    *
    * @param array $definitions Compact item definitions.
    *
    * @return array
    */
   function wpaf_build_title_action_items( array $definitions ) {
      $items = array();

      foreach ( $definitions as $definition ) {
         if ( ! is_array( $definition ) ) {
            continue;
         }

         if ( array_key_exists( 'condition', $definition ) && ! $definition['condition'] ) {
            continue;
         }

         $url   = isset( $definition['url'] ) && is_scalar( $definition['url'] ) ? trim( (string) $definition['url'] ) : '';
         $label = isset( $definition['label'] ) && is_scalar( $definition['label'] ) ? trim( (string) $definition['label'] ) : '';

         if ( $url === '' || $label === '' ) {
            continue;
         }

         $item_args = array();

         if ( isset( $definition['class'] ) && is_scalar( $definition['class'] ) ) {
            $class = trim( (string) $definition['class'] );

            if ( $class !== '' ) {
               $item_args['class'] = $class;
            }
         }

         if ( ! empty( $definition['primary'] ) ) {
            $items[] = wpaf_get_primary_title_action_item( $url, $label, $item_args );
            continue;
         }

         $items[] = wpaf_get_title_action_item( $url, $label, $item_args );
      }

      return $items;
   }
}

//===========================================
// Побудувати заголовок сторінки з title actions із compact definitions
//===========================================
if ( ! function_exists( 'wpaf_get_title_with_button_actions_html' ) ) {
   /**
    * Build page-title HTML directly from compact title-action definitions.
    *
    * This helper keeps a common admin-header composition in one shared call:
    * callers provide the raw title text plus compact action definitions, while
    * the shared layer builds title items and renders the final title HTML.
    *
    * Supported args:
    * - title_args (forwarded to wpaf_get_title_with_actions_html())
    *
    * @param string|mixed $title       Raw title text.
    * @param array        $definitions Compact title-action definitions.
    * @param array        $args        Rendering options.
    *
    * @return string
    */
   function wpaf_get_title_with_button_actions_html( $title, array $definitions = array(), array $args = array() ) {
      $items      = function_exists( 'wpaf_build_title_action_items' )
         ? wpaf_build_title_action_items( $definitions )
         : array();
      $title_args = isset( $args['title_args'] ) && is_array( $args['title_args'] ) ? $args['title_args'] : array();

      return function_exists( 'wpaf_get_title_with_actions_html' )
         ? wpaf_get_title_with_actions_html( $title, $items, $title_args )
         : ( is_scalar( $title ) ? (string) $title : '' );
   }
}


//===========================================
// Вивести заголовок сторінки з title actions із compact definitions
//===========================================
if ( ! function_exists( 'wpaf_render_title_with_button_actions' ) ) {
   /**
    * Render page-title HTML directly from compact title-action definitions.
    *
    * This is the shortest shared path for simple list/journal screens:
    * callers provide the raw title and compact action definitions, while the
    * shared layer builds title items and echoes the final HTML.
    *
    * Supported args:
    * - title_args (forwarded to wpaf_get_title_with_button_actions_html())
    *
    * @param string|mixed $title       Raw title text.
    * @param array        $definitions Compact title-action definitions.
    * @param array        $args        Rendering options.
    *
    * @return void
    */
   function wpaf_render_title_with_button_actions( $title, array $definitions = array(), array $args = array() ) {
      echo wpaf_get_title_with_button_actions_html( $title, $definitions, $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
   }
}



//===========================================
// Вивести simple report/list screen із title actions та notice-code map
//===========================================
if ( ! function_exists( 'wpaf_render_report_screen_with_button_actions' ) ) {
   /**
    * Render a simple report/list screen from compact shared config.
    *
    * This helper is the shortest shared path for modules that need only:
    * - page title text
    * - compact title-action definitions
    * - optional notice_code => notice map rendering
    * - standard form_report() shell
    *
    * The helper stays presentation-only: callers still own plural name,
    * descriptions, URLs, labels, notice codes, and all business rules.
    *
    * Supported args:
    * - plural_name (required for form_report())
    * - title
    * - title_definitions
    * - title_html (optional prebuilt title HTML)
    * - title_args
    * - description1
    * - description2
    * - notice_code
    * - notice_map
    * - notice_args
    *
    * @param array $args Report/list screen arguments.
    *
    * @return bool True when the helper rendered the report shell.
    */
   function wpaf_render_report_screen_with_button_actions( array $args = array() ) {
      if ( function_exists( 'wpaf_normalize_report_screen_runtime_args' ) ) {
         $args = wpaf_normalize_report_screen_runtime_args( $args );
      }

      $plural_name       = isset( $args['plural_name'] ) && is_scalar( $args['plural_name'] ) ? trim( (string) $args['plural_name'] ) : '';
      $title             = isset( $args['title'] ) && is_scalar( $args['title'] ) ? (string) $args['title'] : '';
      $title_html        = isset( $args['title_html'] ) && is_scalar( $args['title_html'] ) ? (string) $args['title_html'] : '';
      $title_definitions = isset( $args['title_definitions'] ) && is_array( $args['title_definitions'] ) ? $args['title_definitions'] : array();
      $title_args        = isset( $args['title_args'] ) && is_array( $args['title_args'] ) ? $args['title_args'] : array();
      $description1      = isset( $args['description1'] ) && is_scalar( $args['description1'] ) ? (string) $args['description1'] : '';
      $description2      = isset( $args['description2'] ) && is_scalar( $args['description2'] ) ? (string) $args['description2'] : '';
      $notice_code             = isset( $args['notice_code'] ) && is_scalar( $args['notice_code'] ) ? trim( (string) $args['notice_code'] ) : '';
      $notice_map              = isset( $args['notice_map'] ) && is_array( $args['notice_map'] ) ? $args['notice_map'] : array();
      $notice_args             = isset( $args['notice_args'] ) && is_array( $args['notice_args'] ) ? $args['notice_args'] : array();
      $notice_message          = isset( $args['notice_message'] ) && is_scalar( $args['notice_message'] ) ? (string) $args['notice_message'] : '';
      $notice_type             = isset( $args['notice_type'] ) && is_scalar( $args['notice_type'] ) ? (string) $args['notice_type'] : 'info';
      $notice_dismissible      = ! empty( $args['notice_dismissible'] );
      $notice_class            = isset( $args['notice_class'] ) && is_scalar( $args['notice_class'] ) ? (string) $args['notice_class'] : '';
      $notice_message_tag      = isset( $args['message_tag'] ) && is_scalar( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p';
      $notice_button_definitions = isset( $args['notice_button_definitions'] ) && is_array( $args['notice_button_definitions'] ) ? $args['notice_button_definitions'] : array();
      $notice_action_links_args  = isset( $args['notice_action_links_args'] ) && is_array( $args['notice_action_links_args'] ) ? $args['notice_action_links_args'] : array();

      if ( $title_html === '' && function_exists( 'wpaf_get_title_with_button_actions_html' ) ) {
         $title_html = wpaf_get_title_with_button_actions_html( $title, $title_definitions, array( 'title_args' => $title_args ) );
      }

      if ( $title_html === '' ) {
         $title_html = $title;
      }

      if ( $notice_code !== '' && ! empty( $notice_map ) && function_exists( 'wpaf_render_admin_notice_by_code' ) ) {
         wpaf_render_admin_notice_by_code( $notice_code, $notice_map, $notice_args );
      } elseif ( $notice_message !== '' ) {
         if ( ! empty( $notice_button_definitions ) && function_exists( 'wpaf_render_admin_notice_with_button_actions' ) ) {
            wpaf_render_admin_notice_with_button_actions(
               $notice_message,
               $notice_button_definitions,
               array(
                  'type'              => $notice_type,
                  'dismissible'       => $notice_dismissible,
                  'class'             => $notice_class,
                  'message_tag'       => $notice_message_tag,
                  'action_links_args' => $notice_action_links_args,
               )
            );
         } elseif ( function_exists( 'wpaf_render_admin_notice' ) ) {
            wpaf_render_admin_notice(
               $notice_message,
               function_exists( 'wpaf_get_admin_notice_args' )
                  ? wpaf_get_admin_notice_args(
                     $notice_type,
                     $notice_dismissible,
                     array(
                        'class'       => $notice_class,
                        'message_tag' => $notice_message_tag,
                     )
                  )
                  : array(
                     'type'        => $notice_type,
                     'dismissible' => $notice_dismissible,
                     'class'       => $notice_class,
                     'message_tag' => $notice_message_tag,
                  )
            );
         }
      }

      if ( $plural_name === '' || ! function_exists( 'form_report' ) ) {
         return false;
      }

      form_report( $plural_name, $title_html, $description1, $description2 );

      return true;
   }
}



//===========================================
// Нормалізувати компактний runtime DSL для report/list screen helper-а
//===========================================
if ( ! function_exists( 'wpaf_normalize_report_screen_runtime_args' ) ) {
   /**
    * Expand compact runtime DSL for wpaf_render_report_screen_with_button_actions().
    *
    * This keeps report/journal pages short and declarative, similar to the
    * standard-screen runtime DSL, while staying presentation-only.
    *
    * Supported compact aliases:
    * - title_actions => title_definitions
    * - notice =>
    *    - code / map / args
    *    - message / type / dismissible / class / message_tag
    *    - buttons / button_definitions / action_links_args
    *
    * @param array $args Report/list runtime args.
    *
    * @return array<string,mixed>
    */
   function wpaf_normalize_report_screen_runtime_args( array $args = array() ) {
      $normalized = $args;

      if ( ! isset( $normalized['title_definitions'] ) && isset( $normalized['title_actions'] ) && is_array( $normalized['title_actions'] ) ) {
         $normalized['title_definitions'] = $normalized['title_actions'];
      }

      if ( isset( $normalized['notice'] ) && is_array( $normalized['notice'] ) ) {
         $notice = $normalized['notice'];

         if ( ! isset( $normalized['notice_code'] ) && isset( $notice['code'] ) && is_scalar( $notice['code'] ) ) {
            $normalized['notice_code'] = (string) $notice['code'];
         }

         if ( ! isset( $normalized['notice_map'] ) && isset( $notice['map'] ) && is_array( $notice['map'] ) ) {
            $normalized['notice_map'] = $notice['map'];
         }

         if ( ! isset( $normalized['notice_args'] ) && isset( $notice['args'] ) && is_array( $notice['args'] ) ) {
            $normalized['notice_args'] = $notice['args'];
         }

         if ( ! isset( $normalized['notice_message'] ) && isset( $notice['message'] ) && is_scalar( $notice['message'] ) ) {
            $normalized['notice_message'] = (string) $notice['message'];
         }

         if ( ! isset( $normalized['notice_type'] ) && isset( $notice['type'] ) && is_scalar( $notice['type'] ) ) {
            $normalized['notice_type'] = (string) $notice['type'];
         }

         if ( ! isset( $normalized['notice_dismissible'] ) && array_key_exists( 'dismissible', $notice ) ) {
            $normalized['notice_dismissible'] = ! empty( $notice['dismissible'] );
         }

         if ( ! isset( $normalized['notice_class'] ) && isset( $notice['class'] ) && is_scalar( $notice['class'] ) ) {
            $normalized['notice_class'] = (string) $notice['class'];
         }

         if ( ! isset( $normalized['message_tag'] ) && isset( $notice['message_tag'] ) && is_scalar( $notice['message_tag'] ) ) {
            $normalized['message_tag'] = (string) $notice['message_tag'];
         }

         if ( ! isset( $normalized['notice_button_definitions'] ) ) {
            if ( isset( $notice['button_definitions'] ) && is_array( $notice['button_definitions'] ) ) {
               $normalized['notice_button_definitions'] = $notice['button_definitions'];
            } elseif ( isset( $notice['buttons'] ) && is_array( $notice['buttons'] ) ) {
               $normalized['notice_button_definitions'] = $notice['buttons'];
            }
         }

         if ( ! isset( $normalized['notice_action_links_args'] ) && isset( $notice['action_links_args'] ) && is_array( $notice['action_links_args'] ) ) {
            $normalized['notice_action_links_args'] = $notice['action_links_args'];
         }
      }

      return $normalized;
   }
}

//===========================================
// Вивести document-like screen одним shared викликом
//===========================================
if ( ! function_exists( 'wpaf_render_document_screen' ) ) {
   /**
    * Render a simple document-like admin screen in one shared call.
    *
    * This helper keeps the common shell for simple create/edit/view/history
    * screens in one place: start the admin screen by screen key, optionally
    * render a notice, print optional HTML before/after the content, call a
    * content callback, and close the screen wrapper.
    *
    * The helper stays presentation-only. The caller still owns screen maps,
    * notice texts, action definitions, callbacks, URLs, permissions, queries,
    * and all business semantics.
    *
    * Supported args:
    * - header_args / wrap_args / render_messages
    * - notice_message / notice_type / notice_dismissible / notice_class / message_tag
    * - notice_button_definitions / notice_action_links_args
    * - before_content_html / content_html / after_content_html
    * - content_callback / content_args
    *
    * @param string|mixed $screen_key Screen key resolved from the caller-owned map.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared document-screen options.
    *
    * @return bool True when the helper rendered the screen shell.
    */
   function wpaf_render_document_screen( $screen_key, array $screen_map = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_admin_screen_start_by_key' ) || ! function_exists( 'wpaf_render_admin_screen_end' ) ) {
         return false;
      }

      $screen_key                = is_scalar( $screen_key ) ? sanitize_key( (string) $screen_key ) : '';
      $notice_message            = isset( $args['notice_message'] ) && is_scalar( $args['notice_message'] ) ? (string) $args['notice_message'] : '';
      $notice_type               = isset( $args['notice_type'] ) && is_scalar( $args['notice_type'] ) ? (string) $args['notice_type'] : 'info';
      $notice_dismissible        = ! empty( $args['notice_dismissible'] );
      $notice_class              = isset( $args['notice_class'] ) && is_scalar( $args['notice_class'] ) ? (string) $args['notice_class'] : '';
      $notice_message_tag        = isset( $args['message_tag'] ) && is_scalar( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p';
      $notice_button_definitions = isset( $args['notice_button_definitions'] ) && is_array( $args['notice_button_definitions'] ) ? $args['notice_button_definitions'] : array();
      $notice_action_links_args  = isset( $args['notice_action_links_args'] ) && is_array( $args['notice_action_links_args'] ) ? $args['notice_action_links_args'] : array();
      $before_content_html       = isset( $args['before_content_html'] ) && is_scalar( $args['before_content_html'] ) ? (string) $args['before_content_html'] : '';
      $content_html              = isset( $args['content_html'] ) && is_scalar( $args['content_html'] ) ? (string) $args['content_html'] : '';
      $after_content_html        = isset( $args['after_content_html'] ) && is_scalar( $args['after_content_html'] ) ? (string) $args['after_content_html'] : '';
      $content_callback          = isset( $args['content_callback'] ) ? $args['content_callback'] : null;
      $has_content_args          = array_key_exists( 'content_args', $args );
      $content_args              = $has_content_args ? $args['content_args'] : null;

      wpaf_render_admin_screen_start_by_key(
         $screen_key,
         $screen_map,
         array(
            'header_args'     => isset( $args['header_args'] ) && is_array( $args['header_args'] ) ? $args['header_args'] : array(),
            'wrap_args'       => isset( $args['wrap_args'] ) && is_array( $args['wrap_args'] ) ? $args['wrap_args'] : array(),
            'render_messages' => ! array_key_exists( 'render_messages', $args ) || ! empty( $args['render_messages'] ),
         )
      );

      if ( $notice_message !== '' ) {
         if ( ! empty( $notice_button_definitions ) && function_exists( 'wpaf_render_admin_notice_with_button_actions' ) ) {
            wpaf_render_admin_notice_with_button_actions(
               $notice_message,
               $notice_button_definitions,
               array(
                  'type'              => $notice_type,
                  'dismissible'       => $notice_dismissible,
                  'class'             => $notice_class,
                  'message_tag'       => $notice_message_tag,
                  'action_links_args' => $notice_action_links_args,
               )
            );
         } elseif ( function_exists( 'wpaf_render_admin_notice' ) ) {
            wpaf_render_admin_notice(
               $notice_message,
               function_exists( 'wpaf_get_admin_notice_args' )
                  ? wpaf_get_admin_notice_args(
                     $notice_type,
                     $notice_dismissible,
                     array(
                        'class'       => $notice_class,
                        'message_tag' => $notice_message_tag,
                     )
                  )
                  : array(
                     'type'        => $notice_type,
                     'dismissible' => $notice_dismissible,
                  )
            );
         } else {
            $raw_classes = 'notice notice-' . sanitize_key( $notice_type );

            if ( $notice_dismissible ) {
               $raw_classes .= ' is-dismissible';
            }

            if ( $notice_class !== '' ) {
               $raw_classes .= ' ' . trim( $notice_class );
            }

            echo '<div class="' . esc_attr( $raw_classes ) . '"><' . tag_escape( $notice_message_tag ) . '>' . esc_html( $notice_message ) . '</' . tag_escape( $notice_message_tag ) . '></div>';
         }
      }

      if ( $before_content_html !== '' ) {
         echo $before_content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      if ( is_callable( $content_callback ) ) {
         if ( $has_content_args ) {
            call_user_func( $content_callback, $content_args );
         } else {
            call_user_func( $content_callback );
         }
      } elseif ( $content_html !== '' ) {
         echo $content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      if ( $after_content_html !== '' ) {
         echo $after_content_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      wpaf_render_admin_screen_end();

      return true;
   }
}

//===========================================
// Вивести form document-like screen одним shared викликом
//===========================================
if ( ! function_exists( 'wpaf_render_form_document_screen' ) ) {
   /**
    * Render a form/document-like admin screen in one shared call.
    *
    * This is a semantic alias over wpaf_render_document_screen() for
    * create/edit-style pages.
    *
    * @param string|mixed $screen_key Screen key resolved from the caller-owned map.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared document-screen options.
    *
    * @return bool
    */
   function wpaf_render_form_document_screen( $screen_key, array $screen_map = array(), array $args = array() ) {
      return function_exists( 'wpaf_render_document_screen' )
         ? wpaf_render_document_screen( $screen_key, $screen_map, $args )
         : false;
   }
}

//===========================================
// Вивести readonly document-like screen одним shared викликом
//===========================================
if ( ! function_exists( 'wpaf_render_readonly_document_screen' ) ) {
   /**
    * Render a readonly document-like admin screen in one shared call.
    *
    * This is a semantic alias over wpaf_render_document_screen() for
    * view/history-style pages.
    *
    * @param string|mixed $screen_key Screen key resolved from the caller-owned map.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared document-screen options.
    *
    * @return bool
    */
   function wpaf_render_readonly_document_screen( $screen_key, array $screen_map = array(), array $args = array() ) {
      return function_exists( 'wpaf_render_document_screen' )
         ? wpaf_render_document_screen( $screen_key, $screen_map, $args )
         : false;
   }
}

//===========================================
// Вивести confirm document-like screen одним shared викликом
//===========================================
if ( ! function_exists( 'wpaf_render_confirm_document_screen' ) ) {
   /**
    * Render a confirm/document-like admin screen in one shared call.
    *
    * This helper is a short path for simple delete/restore/apply/cancel
    * confirmation screens. The caller still owns screen maps, nonce values,
    * submit names, labels, URLs, permissions, and all business semantics.
    *
    * Supported args:
    * - all args accepted by wpaf_render_document_screen()
    * - nonce_action / nonce_name / submit_name / submit_label / cancel_url
    * - confirm_args (forwarded to wpaf_render_confirm_button_form())
    *
    * If the caller already passed content_callback or content_html, this helper
    * leaves that content intact and only acts as a semantic wrapper.
    *
    * @param string|mixed $screen_key Screen key resolved from the caller-owned map.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared confirm/document-screen options.
    *
    * @return bool
    */
   function wpaf_render_confirm_document_screen( $screen_key, array $screen_map = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_document_screen' ) ) {
         return false;
      }

      $has_content_callback = isset( $args['content_callback'] ) && is_callable( $args['content_callback'] );
      $has_content_html     = isset( $args['content_html'] ) && is_scalar( $args['content_html'] ) && (string) $args['content_html'] !== '';

      if ( ! $has_content_callback && ! $has_content_html && function_exists( 'wpaf_render_confirm_button_form' ) ) {
         $nonce_action = isset( $args['nonce_action'] ) && is_scalar( $args['nonce_action'] ) ? (string) $args['nonce_action'] : '';
         $nonce_name   = isset( $args['nonce_name'] ) && is_scalar( $args['nonce_name'] ) ? (string) $args['nonce_name'] : '';
         $submit_name  = isset( $args['submit_name'] ) && is_scalar( $args['submit_name'] ) ? (string) $args['submit_name'] : '';
         $submit_label = isset( $args['submit_label'] ) && is_scalar( $args['submit_label'] ) ? (string) $args['submit_label'] : '';
         $cancel_url   = isset( $args['cancel_url'] ) && is_scalar( $args['cancel_url'] ) ? (string) $args['cancel_url'] : '';
         $confirm_args = isset( $args['confirm_args'] ) && is_array( $args['confirm_args'] ) ? $args['confirm_args'] : array();

         if ( '' !== $nonce_action && '' !== $nonce_name && '' !== $submit_name && '' !== $submit_label ) {
            ob_start();
            wpaf_render_confirm_button_form(
               $nonce_action,
               $nonce_name,
               $submit_name,
               $submit_label,
               $cancel_url,
               $confirm_args
            );
            $args['content_html'] = (string) ob_get_clean();
         }
      }

      return wpaf_render_document_screen( $screen_key, $screen_map, $args );
   }
}






//===========================================
// Побудувати компактний notice-block для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_notice' ) ) {
   /**
    * Build a compact `notice` block for wpaf_render_standard_screen().
    *
    * The helper keeps the top-level framework API short for simple modules:
    * a caller may describe a notice in one place and pass the returned array
    * directly into the shared standard-screen DSL.
    *
    * Supported args:
    * - code / map / args
    * - dismissible / class / message_tag
    * - buttons / button_definitions / action_links_args
    *
    * @param string|array $message Notice message or a prebuilt notice block.
    * @param string       $type    Admin notice type.
    * @param array        $args    Additional notice options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_notice( $message = '', $type = '', array $args = array() ) {
      if ( is_array( $message ) ) {
         return $message;
      }

      $notice = $args;

      if ( '' !== (string) $message && ! isset( $notice['message'] ) ) {
         $notice['message'] = (string) $message;
      }

      if ( '' !== (string) $type && ! isset( $notice['type'] ) ) {
         $notice['type'] = (string) $type;
      }

      return $notice;
   }
}


//===========================================
// Побудувати компактний notice-block з button-like actions для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_notice_with_buttons' ) ) {
   /**
    * Build a compact standard-screen notice block with optional button actions.
    *
    * This shortcut keeps module code shorter when a screen needs a simple
    * `message + type + buttons` notice without manually nesting a `buttons`
    * array inside the generic notice DSL.
    *
    * Supported args:
    * - dismissible / class / message_tag
    * - button_definitions / action_links_args
    * - any other notice-block keys supported by wpaf_get_standard_screen_notice()
    *
    * @param string|array $message Notice message or a prebuilt notice block.
    * @param array        $buttons Button-like action definitions.
    * @param string       $type    Admin notice type.
    * @param array        $args    Additional notice options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_notice_with_buttons( $message = '', array $buttons = array(), $type = 'info', array $args = array() ) {
      if ( is_array( $message ) ) {
         return $message;
      }

      $notice = $args;

      if ( ! isset( $notice['buttons'] ) && ! empty( $buttons ) ) {
         $notice['buttons'] = $buttons;
      }

      return function_exists( 'wpaf_get_standard_screen_notice' )
         ? wpaf_get_standard_screen_notice( $message, $type, $notice )
         : array_merge( array( 'message' => (string) $message, 'type' => (string) $type ), $notice );
   }
}

//===========================================
// Побудувати компактний error-notice для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_error_notice' ) ) {
   /**
    * Build a compact standard-screen error notice.
    *
    * @param string|array $message Notice message or a prebuilt notice block.
    * @param array        $buttons Optional button-like action definitions.
    * @param array        $args    Additional notice options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_error_notice( $message = '', array $buttons = array(), array $args = array() ) {
      return function_exists( 'wpaf_get_standard_screen_notice_with_buttons' )
         ? wpaf_get_standard_screen_notice_with_buttons( $message, $buttons, 'error', $args )
         : array_merge( array( 'message' => (string) $message, 'type' => 'error', 'buttons' => $buttons ), $args );
   }
}

//===========================================
// Побудувати компактний warning-notice для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_warning_notice' ) ) {
   /**
    * Build a compact standard-screen warning notice.
    *
    * @param string|array $message Notice message or a prebuilt notice block.
    * @param array        $buttons Optional button-like action definitions.
    * @param array        $args    Additional notice options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_warning_notice( $message = '', array $buttons = array(), array $args = array() ) {
      return function_exists( 'wpaf_get_standard_screen_notice_with_buttons' )
         ? wpaf_get_standard_screen_notice_with_buttons( $message, $buttons, 'warning', $args )
         : array_merge( array( 'message' => (string) $message, 'type' => 'warning', 'buttons' => $buttons ), $args );
   }
}

//===========================================
// Побудувати компактний notice-by-code block для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_code_notice' ) ) {
   /**
    * Build a compact `notice_code => notice map` block for standard screens.
    *
    * @param string|array $notice_code Notice code or a prebuilt notice block.
    * @param array        $notice_map  Keyed notice map.
    * @param array        $args        Additional notice options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_code_notice( $notice_code = '', array $notice_map = array(), array $args = array() ) {
      if ( is_array( $notice_code ) ) {
         return $notice_code;
      }

      $notice = $args;

      if ( ! isset( $notice['code'] ) ) {
         $notice['code'] = (string) $notice_code;
      }

      if ( ! isset( $notice['map'] ) ) {
         $notice['map'] = $notice_map;
      }

      return $notice;
   }
}

//===========================================
// Побудувати компактний content-block для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_content' ) ) {
   /**
    * Build a compact `content` block for wpaf_render_standard_screen().
    *
    * Supported args:
    * - html
    * - before_html
    * - after_html
    *
    * @param callable|string|array|null $callback     Content callback or a prebuilt content block.
    * @param mixed                      $content_args Callback args.
    * @param array                      $args         Additional content options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_content( $callback = null, $content_args = array(), array $args = array() ) {
      if ( is_array( $callback ) ) {
         return $callback;
      }

      $content = $args;

      if ( null !== $callback && ! isset( $content['callback'] ) ) {
         $content['callback'] = $callback;
      }

      if ( ! array_key_exists( 'args', $content ) ) {
         $content['args'] = $content_args;
      }

      return $content;
   }
}

//===========================================
// Побудувати компактний confirm-block для standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_confirm' ) ) {
   /**
    * Build a compact `confirm` block for wpaf_render_standard_screen().
    *
    * Supported args:
    * - args => confirm renderer args
    *
    * @param string|array $nonce_action Nonce action or a prebuilt confirm block.
    * @param string       $nonce_name   Nonce field name.
    * @param string       $submit_name  Submit button name.
    * @param string       $submit_label Submit button label.
    * @param string       $cancel_url   Cancel URL.
    * @param array        $args         Additional confirm options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_confirm( $nonce_action, $nonce_name = '', $submit_name = '', $submit_label = '', $cancel_url = '', array $args = array() ) {
      if ( is_array( $nonce_action ) ) {
         return $nonce_action;
      }

      return array_merge(
         array(
            'nonce_action' => (string) $nonce_action,
            'nonce_name'   => (string) $nonce_name,
            'submit_name'  => (string) $submit_name,
            'submit_label' => (string) $submit_label,
            'cancel_url'   => (string) $cancel_url,
         ),
         $args
      );
   }
}


//===========================================
// Shortcut для callback-content block у standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_callback_content' ) ) {
   /**
    * Build a compact callback-based `content` block for standard screens.
    *
    * This shortcut keeps module code shorter for the most common case:
    * a screen renders content through a callback with optional callback args.
    *
    * @param callable|string|array $callback     Content callback or a prebuilt content block.
    * @param mixed                 $content_args Callback args.
    * @param array                 $args         Additional content options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_callback_content( $callback, $content_args = array(), array $args = array() ) {
      if ( is_array( $callback ) ) {
         return $callback;
      }

      return function_exists( 'wpaf_get_standard_screen_content' )
         ? wpaf_get_standard_screen_content( $callback, $content_args, $args )
         : array_merge( array( 'callback' => $callback, 'args' => $content_args ), $args );
   }
}

//===========================================
// Shortcut для HTML-content block у standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_html_content' ) ) {
   /**
    * Build a compact HTML-based `content` block for standard screens.
    *
    * @param string|array $html HTML content or a prebuilt content block.
    * @param array        $args Additional content options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_html_content( $html = '', array $args = array() ) {
      if ( is_array( $html ) ) {
         return $html;
      }

      $content = $args;

      if ( ! isset( $content['html'] ) ) {
         $content['html'] = (string) $html;
      }

      return $content;
   }
}

//===========================================
// Shortcut для confirm-block з окремими confirm args у standard screen runtime DSL
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_confirm_buttons' ) ) {
   /**
    * Build a compact confirm block for standard screens with direct confirm args.
    *
    * This shortcut avoids the extra nested `array( 'args' => ... )` wrapper in
    * module code for the common case of confirm screens with shared button-form
    * rendering.
    *
    * @param string|array $nonce_action Nonce action or a prebuilt confirm block.
    * @param string       $nonce_name   Nonce field name.
    * @param string       $submit_name  Submit button name.
    * @param string       $submit_label Submit button label.
    * @param string       $cancel_url   Cancel URL.
    * @param array        $confirm_args Confirm renderer args.
    * @param array        $args         Additional confirm-block options.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_confirm_buttons( $nonce_action, $nonce_name = '', $submit_name = '', $submit_label = '', $cancel_url = '', array $confirm_args = array(), array $args = array() ) {
      if ( is_array( $nonce_action ) ) {
         return $nonce_action;
      }

      $confirm = $args;

      if ( ! array_key_exists( 'args', $confirm ) ) {
         $confirm['args'] = $confirm_args;
      }

      return function_exists( 'wpaf_get_standard_screen_confirm' )
         ? wpaf_get_standard_screen_confirm( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $confirm )
         : array_merge(
            array(
               'nonce_action' => (string) $nonce_action,
               'nonce_name'   => (string) $nonce_name,
               'submit_name'  => (string) $submit_name,
               'submit_label' => (string) $submit_label,
               'cancel_url'   => (string) $cancel_url,
               'args'         => $confirm_args,
            ),
            $args
         );
   }
}

//===========================================
// Нормалізувати компактні runtime-args для стандартного screen API
//===========================================
if ( ! function_exists( 'wpaf_normalize_standard_screen_runtime_args' ) ) {
   /**
    * Expand compact runtime DSL for wpaf_render_standard_screen().
    *
    * This helper keeps the top-level framework API shorter for simple modules.
    * A caller may pass small nested blocks like `notice`, `content`, `confirm`,
    * or `title_actions`, while the shared layer expands them into the flat arg
    * shape already understood by lower-level shared renderers.
    *
    * Supported compact aliases:
    * - title_actions => title_definitions
    * - notice =>
    *    - code / map / args (for report-style notice-by-code flow)
    *    - message / type / dismissible / class / message_tag
    *    - buttons / button_definitions / action_links_args
    * - content =>
    *    - callback / args / html / before_html / after_html
    * - confirm =>
    *    - nonce_action / nonce_name / submit_name / submit_label / cancel_url
    *    - args => confirm_args
    *
    * The helper stays presentation-only and does not know anything about a
    * module's business logic, permissions, URLs, queries, or schema.
    *
    * @param array $args Standard-screen runtime args.
    *
    * @return array<string,mixed>
    */
   function wpaf_normalize_standard_screen_runtime_args( array $args = array() ) {
      $normalized = $args;

      if ( ! isset( $normalized['title_definitions'] ) && isset( $normalized['title_actions'] ) && is_array( $normalized['title_actions'] ) ) {
         $normalized['title_definitions'] = $normalized['title_actions'];
      }

      if ( isset( $normalized['notice'] ) && is_array( $normalized['notice'] ) ) {
         $notice = $normalized['notice'];

         if ( ! isset( $normalized['notice_code'] ) && isset( $notice['code'] ) && is_scalar( $notice['code'] ) ) {
            $normalized['notice_code'] = (string) $notice['code'];
         }

         if ( ! isset( $normalized['notice_map'] ) && isset( $notice['map'] ) && is_array( $notice['map'] ) ) {
            $normalized['notice_map'] = $notice['map'];
         }

         if ( ! isset( $normalized['notice_args'] ) && isset( $notice['args'] ) && is_array( $notice['args'] ) ) {
            $normalized['notice_args'] = $notice['args'];
         }

         if ( ! isset( $normalized['notice_message'] ) && isset( $notice['message'] ) && is_scalar( $notice['message'] ) ) {
            $normalized['notice_message'] = (string) $notice['message'];
         }

         if ( ! isset( $normalized['notice_type'] ) && isset( $notice['type'] ) && is_scalar( $notice['type'] ) ) {
            $normalized['notice_type'] = (string) $notice['type'];
         }

         if ( ! array_key_exists( 'notice_dismissible', $normalized ) && array_key_exists( 'dismissible', $notice ) ) {
            $normalized['notice_dismissible'] = ! empty( $notice['dismissible'] );
         }

         if ( ! isset( $normalized['notice_class'] ) && isset( $notice['class'] ) && is_scalar( $notice['class'] ) ) {
            $normalized['notice_class'] = (string) $notice['class'];
         }

         if ( ! isset( $normalized['message_tag'] ) && isset( $notice['message_tag'] ) && is_scalar( $notice['message_tag'] ) ) {
            $normalized['message_tag'] = (string) $notice['message_tag'];
         }

         if ( ! isset( $normalized['notice_button_definitions'] ) ) {
            if ( isset( $notice['button_definitions'] ) && is_array( $notice['button_definitions'] ) ) {
               $normalized['notice_button_definitions'] = $notice['button_definitions'];
            } elseif ( isset( $notice['buttons'] ) && is_array( $notice['buttons'] ) ) {
               $normalized['notice_button_definitions'] = $notice['buttons'];
            }
         }

         if ( ! isset( $normalized['notice_action_links_args'] ) && isset( $notice['action_links_args'] ) && is_array( $notice['action_links_args'] ) ) {
            $normalized['notice_action_links_args'] = $notice['action_links_args'];
         }

         unset( $normalized['notice'] );
      }

      if ( isset( $normalized['content'] ) && is_array( $normalized['content'] ) ) {
         $content = $normalized['content'];

         if ( ! isset( $normalized['content_callback'] ) && isset( $content['callback'] ) ) {
            $normalized['content_callback'] = $content['callback'];
         }

         if ( ! array_key_exists( 'content_args', $normalized ) && array_key_exists( 'args', $content ) ) {
            $normalized['content_args'] = $content['args'];
         }

         if ( ! isset( $normalized['content_html'] ) && isset( $content['html'] ) && is_scalar( $content['html'] ) ) {
            $normalized['content_html'] = (string) $content['html'];
         }

         if ( ! isset( $normalized['before_content_html'] ) && isset( $content['before_html'] ) && is_scalar( $content['before_html'] ) ) {
            $normalized['before_content_html'] = (string) $content['before_html'];
         }

         if ( ! isset( $normalized['after_content_html'] ) && isset( $content['after_html'] ) && is_scalar( $content['after_html'] ) ) {
            $normalized['after_content_html'] = (string) $content['after_html'];
         }

         unset( $normalized['content'] );
      }

      if ( isset( $normalized['confirm'] ) && is_array( $normalized['confirm'] ) ) {
         $confirm_keys = array(
            'nonce_action',
            'nonce_name',
            'submit_name',
            'submit_label',
            'cancel_url',
         );

         foreach ( $confirm_keys as $confirm_key ) {
            if ( ! isset( $normalized[ $confirm_key ] ) && isset( $normalized['confirm'][ $confirm_key ] ) && is_scalar( $normalized['confirm'][ $confirm_key ] ) ) {
               $normalized[ $confirm_key ] = (string) $normalized['confirm'][ $confirm_key ];
            }
         }

         if ( ! isset( $normalized['confirm_args'] ) && isset( $normalized['confirm']['args'] ) && is_array( $normalized['confirm']['args'] ) ) {
            $normalized['confirm_args'] = $normalized['confirm']['args'];
         }

         unset( $normalized['confirm'] );
      }

      return $normalized;
   }
}

//===========================================
// Вивести стандартний screen одним shared entry point
//===========================================
if ( ! function_exists( 'wpaf_render_standard_screen' ) ) {
   /**
    * Render a standard admin screen through one shared entry point.
    *
    * This helper keeps the top-level framework API intentionally short for
    * simple modules. The caller describes the screen type and passes the same
    * compact arguments that are already supported by the lower-level helpers,
    * while the shared layer dispatches to the proper renderer.
    *
    * Supported types:
    * - report   => wpaf_render_report_screen_with_button_actions()
    * - document => wpaf_render_document_screen()
    * - form     => wpaf_render_form_document_screen()
    * - readonly => wpaf_render_readonly_document_screen()
    * - confirm  => wpaf_render_confirm_document_screen()
    *
    * Required by type:
    * - report: plural_name plus standard report args
    * - document/form/readonly/confirm: screen_key, screen_map
    *
    * All remaining args are forwarded unchanged to the selected renderer.
    * The helper stays presentation-only and does not know anything about a
    * caller module's business rules, URLs, permissions, queries, or schema.
    *
    * @param array $args Shared standard-screen arguments.
    *
    * @return bool True when the selected shared renderer handled the screen.
    */
   function wpaf_render_standard_screen( array $args = array() ) {
      if ( function_exists( 'wpaf_normalize_standard_screen_runtime_args' ) ) {
         $args = wpaf_normalize_standard_screen_runtime_args( $args );
      }

      $type = isset( $args['type'] ) && is_scalar( $args['type'] ) ? sanitize_key( (string) $args['type'] ) : 'document';

      if ( in_array( $type, array( 'report', 'list', 'journal' ), true ) ) {
         return function_exists( 'wpaf_render_report_screen_with_button_actions' )
            ? wpaf_render_report_screen_with_button_actions( $args )
            : false;
      }

      $screen_key = isset( $args['screen_key'] ) ? $args['screen_key'] : '';
      $screen_map = isset( $args['screen_map'] ) && is_array( $args['screen_map'] ) ? $args['screen_map'] : array();

      unset( $args['type'], $args['screen_key'], $args['screen_map'] );

      if ( in_array( $type, array( 'form', 'create', 'edit' ), true ) ) {
         return function_exists( 'wpaf_render_form_document_screen' )
            ? wpaf_render_form_document_screen( $screen_key, $screen_map, $args )
            : false;
      }

      if ( in_array( $type, array( 'readonly', 'view', 'history' ), true ) ) {
         return function_exists( 'wpaf_render_readonly_document_screen' )
            ? wpaf_render_readonly_document_screen( $screen_key, $screen_map, $args )
            : false;
      }

      if ( 'confirm' === $type ) {
         return function_exists( 'wpaf_render_confirm_document_screen' )
            ? wpaf_render_confirm_document_screen( $screen_key, $screen_map, $args )
            : false;
      }

      return function_exists( 'wpaf_render_document_screen' )
         ? wpaf_render_document_screen( $screen_key, $screen_map, $args )
         : false;
   }
}



//===========================================
// Побудувати одну стандартну screen-дефініцію з компактного опису
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_definition' ) ) {
   /**
    * Normalize one compact standard-screen definition.
    *
    * This helper keeps the framework easier for simple modules. A module may
    * describe a screen in a very short form — for example just `'edit'` or
    * `'confirm'` — while the shared layer expands it into the full definition
    * shape expected by the higher-level screen renderers.
    *
    * Supported input formats:
    * - scalar string: treated as `array( 'type' => <value> )`
    * - array: kept as-is and merged with shared defaults
    *
    * Supported defaults:
    * - any top-level standard-screen keys
    * - optional nested `args` array
    *
    * The helper stays presentation-only: it does not know anything about a
    * module's business logic, URLs, permissions, queries, or schema.
    *
    * @param string|mixed $screen_key  Screen-definition key.
    * @param mixed        $definition  Compact screen definition.
    * @param array        $defaults    Shared defaults for all screens in a map.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_definition( $screen_key, $definition = array(), array $defaults = array() ) {
      $screen_key    = sanitize_key( (string) $screen_key );
      $default_args  = isset( $defaults['args'] ) && is_array( $defaults['args'] ) ? $defaults['args'] : array();
      $resolved_args = array();

      unset( $defaults['args'] );

      if ( is_scalar( $definition ) ) {
         $definition = array(
            'type' => sanitize_key( (string) $definition ),
         );
      } elseif ( ! is_array( $definition ) ) {
         $definition = array();
      }

      if ( isset( $definition['args'] ) && is_array( $definition['args'] ) ) {
         $resolved_args = array_merge( $default_args, $definition['args'] );
         unset( $definition['args'] );
      } elseif ( ! empty( $default_args ) ) {
         $resolved_args = $default_args;
      }

      $resolved = array_merge( $defaults, $definition );

      if ( ! array_key_exists( 'screen_key', $resolved ) && '' !== $screen_key ) {
         $resolved['screen_key'] = $screen_key;
      }

      if ( ! empty( $resolved_args ) ) {
         $resolved['args'] = $resolved_args;
      }

      return $resolved;
   }
}

//===========================================
// Побудувати карту стандартних screen-дефініцій з компактного опису
//===========================================
if ( ! function_exists( 'wpaf_build_standard_screen_definitions' ) ) {
   /**
    * Build a keyed map of reusable standard-screen definitions.
    *
    * This helper is the shortest shared path for modules that want to keep a
    * declarative screen map very small. A module may describe screens with
    * compact scalar values like `'view'`, `'confirm'`, or `'report'`, while the
    * shared layer expands every entry with common defaults such as `screen_map`
    * and `header_args`.
    *
    * Example:
    * $defs = wpaf_build_standard_screen_definitions(
    *    array(
    *       'list'   => array( 'type' => 'report' ),
    *       'create' => 'create',
    *       'edit'   => 'edit',
    *       'view'   => 'view',
    *    ),
    *    array(
    *       'screen_map'  => $screen_map,
    *       'header_args' => $header_args,
    *    )
    * );
    *
    * @param array $definitions Compact keyed screen definitions.
    * @param array $defaults    Shared defaults applied to every definition.
    *
    * @return array<string,array<string,mixed>>
    */
   function wpaf_build_standard_screen_definitions( array $definitions = array(), array $defaults = array() ) {
      $resolved = array();

      foreach ( $definitions as $screen_key => $definition ) {
         $resolved[ $screen_key ] = function_exists( 'wpaf_get_standard_screen_definition' )
            ? wpaf_get_standard_screen_definition( $screen_key, $definition, $defaults )
            : array_merge( $defaults, is_array( $definition ) ? $definition : array( 'type' => sanitize_key( (string) $definition ) ) );
      }

      return $resolved;
   }
}

//===========================================
// Побудувати стандартні screen args за screen key з декларативної карти
//===========================================
if ( ! function_exists( 'wpaf_get_standard_screen_args_by_key' ) ) {
   /**
    * Build compact standard-screen arguments from a keyed screen-definition map.
    *
    * This helper keeps the framework simpler for small modules: a module can
    * describe reusable standard-screen defaults once in a keyed map and then
    * override only the small, screen-specific runtime values per request.
    *
    * Supported map entry fields are the same as for wpaf_render_standard_screen().
    * In addition, an entry may contain a nested `args` array for readability.
    *
    * @param string|mixed $screen_key          Screen-definition key.
    * @param array        $screen_definitions  Map of reusable standard-screen definitions.
    * @param array        $args                Per-request overrides.
    *
    * @return array<string,mixed>
    */
   function wpaf_get_standard_screen_args_by_key( $screen_key, array $screen_definitions = array(), array $args = array() ) {
      $screen_key = sanitize_key( (string) $screen_key );
      $entry      = isset( $screen_definitions[ $screen_key ] ) && is_array( $screen_definitions[ $screen_key ] )
         ? $screen_definitions[ $screen_key ]
         : array();
      $entry_args = isset( $entry['args'] ) && is_array( $entry['args'] ) ? $entry['args'] : array();

      unset( $entry['args'] );

      $resolved = array_merge( $entry, $entry_args );

      if ( ! array_key_exists( 'screen_key', $resolved ) && '' !== $screen_key ) {
         $resolved['screen_key'] = $screen_key;
      }

      return array_merge( $resolved, $args );
   }
}

//===========================================
// Вивести стандартний screen за screen key з декларативної карти
//===========================================
if ( ! function_exists( 'wpaf_render_standard_screen_by_key' ) ) {
   /**
    * Render a standard screen from a keyed reusable definition map.
    *
    * This is the shortest shared entry point for modules that keep a compact
    * `screen_key => standard-screen args` map. The helper stays presentation-only:
    * it resolves shared defaults from the map, applies per-request overrides,
    * and forwards the final args to wpaf_render_standard_screen().
    *
    * @param string|mixed $screen_key          Screen-definition key.
    * @param array        $screen_definitions  Map of reusable standard-screen definitions.
    * @param array        $args                Per-request overrides.
    *
    * @return bool True when the shared standard-screen renderer handled the screen.
    */
   function wpaf_render_standard_screen_by_key( $screen_key, array $screen_definitions = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_standard_screen' ) ) {
         return false;
      }

      return wpaf_render_standard_screen(
         function_exists( 'wpaf_get_standard_screen_args_by_key' )
            ? wpaf_get_standard_screen_args_by_key( $screen_key, $screen_definitions, $args )
            : array_merge( array( 'screen_key' => sanitize_key( (string) $screen_key ) ), $args )
      );
   }
}


//===========================================
// Вивести standard screen by key з коротким notice runtime block
//===========================================
if ( ! function_exists( 'wpaf_render_standard_notice_screen_by_key' ) ) {
   /**
    * Render a keyed standard screen with a compact notice block.
    *
    * This keeps simple modules shorter for the common case when a screen only
    * needs a shared header/shell plus one admin notice.
    *
    * @param string|mixed $screen_key         Screen-definition key.
    * @param array        $screen_definitions Map of reusable standard-screen definitions.
    * @param array|mixed  $notice             Notice block or notice DSL shortcut.
    * @param array        $args               Additional runtime args.
    *
    * @return bool
    */
   function wpaf_render_standard_notice_screen_by_key( $screen_key, array $screen_definitions = array(), $notice = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_standard_screen_by_key' ) ) {
         return false;
      }

      if ( ! array_key_exists( 'notice', $args ) ) {
         $args['notice'] = is_array( $notice )
            ? $notice
            : ( function_exists( 'wpaf_get_standard_screen_notice' )
               ? wpaf_get_standard_screen_notice( $notice )
               : array( 'message' => is_scalar( $notice ) ? (string) $notice : '' ) );
      }

      return wpaf_render_standard_screen_by_key( $screen_key, $screen_definitions, $args );
   }
}

//===========================================
// Вивести standard screen by key з callback-content block
//===========================================
if ( ! function_exists( 'wpaf_render_standard_content_screen_by_key' ) ) {
   /**
    * Render a keyed standard screen with callback-based content.
    *
    * @param string|mixed        $screen_key         Screen-definition key.
    * @param array               $screen_definitions Map of reusable standard-screen definitions.
    * @param callable|string|mixed $callback         Content callback or a prebuilt content block.
    * @param mixed               $content_args       Callback args.
    * @param array               $args               Additional runtime args.
    *
    * @return bool
    */
   function wpaf_render_standard_content_screen_by_key( $screen_key, array $screen_definitions = array(), $callback = null, $content_args = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_standard_screen_by_key' ) ) {
         return false;
      }

      if ( ! array_key_exists( 'content', $args ) ) {
         $args['content'] = is_array( $callback )
            ? $callback
            : ( function_exists( 'wpaf_get_standard_screen_callback_content' )
               ? wpaf_get_standard_screen_callback_content( $callback, $content_args )
               : array(
                  'callback' => $callback,
                  'args'     => $content_args,
               ) );
      }

      return wpaf_render_standard_screen_by_key( $screen_key, $screen_definitions, $args );
   }
}

//===========================================
// Вивести standard screen by key з confirm block
//===========================================
if ( ! function_exists( 'wpaf_render_standard_confirm_screen_by_key' ) ) {
   /**
    * Render a keyed standard screen with a compact confirm block.
    *
    * @param string|mixed $screen_key         Screen-definition key.
    * @param array        $screen_definitions Map of reusable standard-screen definitions.
    * @param string|mixed $nonce_action       Nonce action or a prebuilt confirm block.
    * @param string       $nonce_name         Nonce field name.
    * @param string       $submit_name        Submit button name.
    * @param string       $submit_label       Submit button label.
    * @param string       $cancel_url         Cancel URL.
    * @param array        $confirm_args       Confirm renderer args.
    * @param array        $args               Additional runtime args.
    *
    * @return bool
    */
   function wpaf_render_standard_confirm_screen_by_key( $screen_key, array $screen_definitions, $nonce_action, $nonce_name = '', $submit_name = '', $submit_label = '', $cancel_url = '', array $confirm_args = array(), array $args = array() ) {
      if ( ! function_exists( 'wpaf_render_standard_screen_by_key' ) ) {
         return false;
      }

      if ( ! array_key_exists( 'confirm', $args ) ) {
         $args['confirm'] = is_array( $nonce_action )
            ? $nonce_action
            : ( function_exists( 'wpaf_get_standard_screen_confirm_buttons' )
               ? wpaf_get_standard_screen_confirm_buttons( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url, $confirm_args )
               : array_merge(
                  array(
                     'nonce_action' => (string) $nonce_action,
                     'nonce_name'   => (string) $nonce_name,
                     'submit_name'  => (string) $submit_name,
                     'submit_label' => (string) $submit_label,
                     'cancel_url'   => (string) $cancel_url,
                  ),
                  $confirm_args
               ) );
      }

      return wpaf_render_standard_screen_by_key( $screen_key, $screen_definitions, $args );
   }
}

//===========================================
// Побудувати стандартний Return action item для title/actions block
//===========================================
if ( ! function_exists( 'wpaf_get_return_action_item' ) ) {
   /**
    * Build a reusable Return action-link item for admin page headers.
    *
    * This helper is intentionally tiny and render-agnostic. The caller still
    * owns the destination URL and any screen-specific navigation semantics.
    *
    * Supported args:
    * - label
    * - class
    *
    * @param string|mixed $url  Destination URL.
    * @param array        $args Action item options.
    *
    * @return array
    */
   function wpaf_get_return_action_item( $url, array $args = array() ) {
      $label = isset( $args['label'] ) ? (string) $args['label'] : __( 'Return', 'wp-add-function' );
      $class = isset( $args['class'] ) ? (string) $args['class'] : 'page-title-action';

      return array(
         'url'   => is_scalar( $url ) ? trim( (string) $url ) : '',
         'label' => $label,
         'class' => $class,
      );
   }
}


//===========================================
// Вивести універсальну шапку list-screen з Return action та intro-box
//===========================================
if ( ! function_exists( 'wpaf_render_return_list_header' ) ) {
   /**
    * Render a shared list-screen header with a Return action and intro box.
    *
    * This helper stays render-only. The caller still owns the return URL,
    * title text, descriptions, and all screen-specific navigation semantics.
    *
    * Supported args:
    * - title
    * - return_url
    * - return_args
    * - picture_url
    * - description1
    * - description2
    * - header_args
    *
    * @param array $args Header options.
    *
    * @return void
    */
   function wpaf_render_return_list_header( array $args = array() ) {
      $title        = isset( $args['title'] ) ? $args['title'] : '';
      $return_url   = isset( $args['return_url'] ) ? trim( (string) $args['return_url'] ) : '';
      $return_args  = isset( $args['return_args'] ) && is_array( $args['return_args'] ) ? $args['return_args'] : array();
      $picture_url  = isset( $args['picture_url'] ) ? (string) $args['picture_url'] : '';
      $description1 = isset( $args['description1'] ) ? (string) $args['description1'] : '';
      $description2 = isset( $args['description2'] ) ? (string) $args['description2'] : '';
      $header_args  = isset( $args['header_args'] ) && is_array( $args['header_args'] ) ? $args['header_args'] : array();
      $intro_args   = isset( $header_args['intro_args'] ) && is_array( $header_args['intro_args'] ) ? $header_args['intro_args'] : array();
      $title_html   = $title;
      $return_item  = array();

      if ( $return_url !== '' ) {
         $return_item = wpaf_get_return_action_item( $return_url, $return_args );
         $title_actions = array( $return_item );

         if ( function_exists( 'wpaf_get_title_with_actions_html' ) ) {
            $title_html = wpaf_get_title_with_actions_html( $title, $title_actions );
         }
      }

      $header_args['intro_args'] = array_merge(
         array(
            'wrapper_style' => 'background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;',
         ),
         $intro_args
      );

      if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
         $header_args = array_merge(
            array(
               'title'         => $title_html,
               'title_is_html' => true,
               'picture_url'   => $picture_url,
               'description1'  => $description1,
               'description2'  => $description2,
            ),
            $header_args
         );

         wpaf_render_admin_screen_header( $header_args );
         return;
      }

      $return_label = isset( $return_item['label'] ) ? (string) $return_item['label'] : __( 'Return', 'wp-add-function' );
      $return_class = isset( $return_item['class'] ) ? (string) $return_item['class'] : 'page-title-action';
      $wrapper_style = isset( $header_args['intro_args']['wrapper_style'] ) ? (string) $header_args['intro_args']['wrapper_style'] : 'background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;';

      echo '<div id="icon-users" class="icon32"><br/></div>';
      echo '<h2>';
      echo esc_html( is_scalar( $title ) ? (string) $title : '' );

      if ( $return_url !== '' ) {
         echo '<a href="' . esc_url( $return_url ) . '" class="' . esc_attr( $return_class ) . '">';
         echo esc_html( $return_label );
         echo '</a>';
      }

      echo '</h2>';

      if ( '' !== $picture_url || '' !== $description1 || '' !== $description2 ) {
         echo '<div style="' . esc_attr( $wrapper_style ) . '">';
         echo '<p>';
         echo '<table class="wpuf-table">';
         echo '<th>';

         if ( '' !== $picture_url ) {
            echo '<img src="' . esc_url( $picture_url ) . '" name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>';
         }

         echo '</th>';
         echo '<td>';
         echo $description1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

         if ( '' !== $description2 ) {
            echo '<br/><br/>';
            echo $description2; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         }

         echo '</td>';
         echo '</table>';
         echo '</p>';
         echo '</div>';
      }
   }
}


//===========================================
// Вивести універсальну шапку simple confirm-screen для admin pages
//===========================================
if ( ! function_exists( 'wpaf_render_directory_confirm_header' ) ) {
   /**
    * Render a shared header shell for simple directory confirm screens.
    *
    * This helper stays render-only: the caller still owns message texts,
    * submit names, permissions, redirects, and all business semantics.
    *
    * Supported args:
    * - title
    * - picture_url
    * - description1
    * - description2
    * - header_args
    *
    * @param array $args Confirm-screen header options.
    *
    * @return void
    */
   function wpaf_render_directory_confirm_header( array $args = array() ) {
      $title        = isset( $args['title'] ) ? $args['title'] : '';
      $picture_url  = isset( $args['picture_url'] ) ? (string) $args['picture_url'] : '';
      $description1 = isset( $args['description1'] ) ? (string) $args['description1'] : '';
      $description2 = isset( $args['description2'] ) ? (string) $args['description2'] : '';
      $header_args  = isset( $args['header_args'] ) && is_array( $args['header_args'] ) ? $args['header_args'] : array();

      if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
         $header_args = array_merge(
            array(
               'title'        => $title,
               'picture_url'  => $picture_url,
               'description1' => $description1,
               'description2' => $description2,
            ),
            $header_args
         );

         wpaf_render_admin_screen_header( $header_args );
         return;
      }

      html_title( $title, $picture_url, $description1, $description2 );
   }
}


//===========================================
// Отримати HTML submit-кнопки для повторного використання в admin screens
//===========================================
if ( ! function_exists( 'wpaf_get_submit_button_html' ) ) {
   /**
    * Build a reusable submit-button HTML fragment with WordPress submit_button().
    *
    * This helper stays render-only: the caller still owns button names,
    * labels, classes, submit flow, and business semantics.
    *
    * @param string $label      Button label.
    * @param string $button_css Button style passed to submit_button().
    * @param string $name       Submit field name.
    * @param bool   $wrap       Whether submit_button() should wrap output.
    *
    * @return string
    */
   function wpaf_get_submit_button_html( $label, $button_css = 'primary', $name = 'submit', $wrap = false ) {
      ob_start();
      submit_button( $label, $button_css, $name, $wrap );
      return (string) ob_get_clean();
   }
}



//===========================================
// Побудувати універсальний actions_args масив для confirm-form helper
//===========================================
if ( ! function_exists( 'wpaf_get_confirm_actions_args' ) ) {
   /**
    * Build a reusable actions_args array for wpaf_render_confirm_form().
    *
    * This helper stays render-only: the caller still owns submit names,
    * labels, permissions, redirect logic, and POST semantics.
    *
    * Supported args:
    * - submit_class
    * - cancel_name
    * - cancel_label
    * - cancel_class
    * - extra_actions
    *
    * @param string $submit_name  Primary submit field name.
    * @param string $submit_label Primary submit label.
    * @param array  $args         Additional action options.
    *
    * @return array
    */
   function wpaf_get_confirm_actions_args( $submit_name, $submit_label, array $args = array() ) {
      $submit_name   = (string) $submit_name;
      $submit_label  = (string) $submit_label;
      $submit_class  = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button';
      $cancel_name   = isset( $args['cancel_name'] ) ? (string) $args['cancel_name'] : 'button_cancel';
      $cancel_label  = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $cancel_class  = isset( $args['cancel_class'] ) ? (string) $args['cancel_class'] : 'primary';
      $extra_actions = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';

      if ( '' !== $cancel_label ) {
         $cancel_html = function_exists( 'wpaf_get_submit_button_html' )
            ? wpaf_get_submit_button_html( $cancel_label, $cancel_class, $cancel_name, false )
            : '';

         if ( '' === $cancel_html && function_exists( 'submit_button' ) ) {
            ob_start();
            submit_button( $cancel_label, $cancel_class, $cancel_name, false );
            $cancel_html = (string) ob_get_clean();
         }

         if ( '' !== $cancel_html ) {
            $extra_actions .= $cancel_html;
         }
      }

      return array(
         'submit_name'   => $submit_name,
         'submit_label'  => $submit_label,
         'submit_class'  => $submit_class,
         'extra_actions' => $extra_actions,
      );
   }
}


//===========================================
// Побудувати args масив для confirm action buttons з cancel-link
//===========================================
if ( ! function_exists( 'wpaf_get_confirm_action_buttons_args' ) ) {
   /**
    * Build reusable args for wpaf_render_action_buttons() on confirm screens.
    *
    * This helper stays render-only: callers still own submit names, nonce
    * handling, URLs, redirect flow, and all business semantics.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - submit_value
    * - submit_class
    * - submit_style
    * - cancel_label
    * - cancel_class
    * - cancel_text_domain
    * - extra_actions
    *
    * @param string $submit_name  Primary submit field name.
    * @param string $submit_label Primary submit label.
    * @param string $cancel_url   Cancel-link URL.
    * @param array  $args         Additional action options.
    *
    * @return array
    */
   function wpaf_get_confirm_action_buttons_args( $submit_name, $submit_label, $cancel_url = '', array $args = array() ) {
      $wrapper_class      = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-actions';
      $wrapper_style      = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '';
      $submit_value       = array_key_exists( 'submit_value', $args ) ? (string) $args['submit_value'] : '1';
      $submit_class       = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $submit_style       = isset( $args['submit_style'] ) ? (string) $args['submit_style'] : '';
      $cancel_label       = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $cancel_class       = isset( $args['cancel_class'] ) ? (string) $args['cancel_class'] : 'button';
      $cancel_text_domain = isset( $args['cancel_text_domain'] ) ? (string) $args['cancel_text_domain'] : 'wp-add-function';
      $extra_actions      = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';

      if ( '' === $wrapper_style ) {
         $wrapper_style = function_exists( 'wpaf_get_form_action_buttons_wrapper_style' )
            ? wpaf_get_form_action_buttons_wrapper_style()
            : 'display:flex;align-items:center;gap:6px;flex-wrap:wrap;';
      }

      if ( '' === $cancel_label && '' !== (string) $cancel_url ) {
         $cancel_label = function_exists( 'wpaf_get_cancel_button_label' )
            ? wpaf_get_cancel_button_label( '', $cancel_text_domain )
            : __( 'Cancel', $cancel_text_domain );
      }

      return array(
         'wrapper_class' => $wrapper_class,
         'wrapper_style' => $wrapper_style,
         'submit_name'   => (string) $submit_name,
         'submit_label'  => (string) $submit_label,
         'submit_value'  => $submit_value,
         'submit_class'  => $submit_class,
         'submit_style'  => $submit_style,
         'cancel_url'    => (string) $cancel_url,
         'cancel_label'  => $cancel_label,
         'cancel_class'  => $cancel_class,
         'extra_actions' => $extra_actions,
      );
   }
}


//===========================================
// Побудувати args масив для create/edit form action rows
//===========================================
if ( ! function_exists( 'wpaf_get_form_action_buttons_args' ) ) {
   /**
    * Build reusable args for wpaf_render_action_buttons() on create/edit forms.
    *
    * This helper stays render-only: callers still own submit names, labels,
    * cancel URLs, extra action HTML, routing, and all module business logic.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - submit_value
    * - submit_class
    * - submit_style
    * - cancel_label
    * - cancel_class
    * - cancel_text_domain
    * - extra_actions
    * - trailing_actions
    * - leading_wrapper_class
    * - leading_wrapper_style
    * - trailing_wrapper_class
    * - trailing_wrapper_style
    *
    * @param string $submit_name  Primary submit field name.
    * @param string $submit_label Primary submit label.
    * @param string $cancel_url   Cancel-link URL.
    * @param array  $args         Additional action options.
    *
    * @return array
    */
   function wpaf_get_form_action_buttons_args( $submit_name, $submit_label, $cancel_url = '', array $args = array() ) {
      $wrapper_class          = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons';
      $wrapper_style          = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '';
      $submit_value           = array_key_exists( 'submit_value', $args ) ? (string) $args['submit_value'] : '1';
      $submit_class           = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $submit_style           = isset( $args['submit_style'] ) ? (string) $args['submit_style'] : '';
      $cancel_label           = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $cancel_class           = isset( $args['cancel_class'] ) ? (string) $args['cancel_class'] : 'button';
      $cancel_text_domain     = isset( $args['cancel_text_domain'] ) ? (string) $args['cancel_text_domain'] : 'wp-add-function';
      $extra_actions          = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $trailing_actions       = isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '';
      $leading_wrapper_class  = isset( $args['leading_wrapper_class'] ) ? (string) $args['leading_wrapper_class'] : 'wpaf-action-buttons-leading';
      $leading_wrapper_style  = isset( $args['leading_wrapper_style'] ) ? (string) $args['leading_wrapper_style'] : '';
      $trailing_wrapper_class = isset( $args['trailing_wrapper_class'] ) ? (string) $args['trailing_wrapper_class'] : 'wpaf-action-buttons-trailing';
      $trailing_wrapper_style = isset( $args['trailing_wrapper_style'] ) ? (string) $args['trailing_wrapper_style'] : '';

      if ( '' === $cancel_label && '' !== (string) $cancel_url ) {
         $cancel_label = function_exists( 'wpaf_get_cancel_button_label' )
            ? wpaf_get_cancel_button_label( '', $cancel_text_domain )
            : __( 'Cancel', $cancel_text_domain );
      }

      return array(
         'wrapper_class'          => $wrapper_class,
         'wrapper_style'          => $wrapper_style,
         'submit_name'            => (string) $submit_name,
         'submit_label'           => (string) $submit_label,
         'submit_value'           => $submit_value,
         'submit_class'           => $submit_class,
         'submit_style'           => $submit_style,
         'cancel_url'             => (string) $cancel_url,
         'cancel_label'           => $cancel_label,
         'cancel_class'           => $cancel_class,
         'extra_actions'          => $extra_actions,
         'trailing_actions'       => $trailing_actions,
         'leading_wrapper_class'  => $leading_wrapper_class,
         'leading_wrapper_style'  => $leading_wrapper_style,
         'trailing_wrapper_class' => $trailing_wrapper_class,
         'trailing_wrapper_style' => $trailing_wrapper_style,
      );
   }
}



//===========================================
// Побудувати args масив для readonly/view action rows
//===========================================
if ( ! function_exists( 'wpaf_get_readonly_action_buttons_args' ) ) {
   /**
    * Build reusable args for wpaf_render_action_buttons() on readonly/view screens.
    *
    * This helper stays render-only: callers still own URLs, labels,
    * permissions, routing, and all module business semantics.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - extra_actions
    * - trailing_actions
    * - leading_wrapper_class
    * - leading_wrapper_style
    * - trailing_wrapper_class
    * - trailing_wrapper_style
    *
    * @param array $args Optional action-row options.
    *
    * @return array
    */
   function wpaf_get_readonly_action_buttons_args( array $args = array() ) {
      return array(
         'wrapper_class'          => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons',
         'wrapper_style'          => isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '',
         'extra_actions'          => isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '',
         'trailing_actions'       => isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '',
         'leading_wrapper_class'  => isset( $args['leading_wrapper_class'] ) ? (string) $args['leading_wrapper_class'] : 'wpaf-action-buttons-leading',
         'leading_wrapper_style'  => isset( $args['leading_wrapper_style'] ) ? (string) $args['leading_wrapper_style'] : '',
         'trailing_wrapper_class' => isset( $args['trailing_wrapper_class'] ) ? (string) $args['trailing_wrapper_class'] : 'wpaf-action-buttons-trailing',
         'trailing_wrapper_style' => isset( $args['trailing_wrapper_style'] ) ? (string) $args['trailing_wrapper_style'] : '',
      );
   }
}


//===========================================
// Побудувати args масив для create/edit action rows із button definitions
//===========================================
if ( ! function_exists( 'wpaf_get_form_button_actions_args' ) ) {
   /**
    * Build reusable args for wpaf_render_action_buttons() on create/edit forms
    * from compact button definitions.
    *
    * This helper remains presentation-only. Callers still own submit names,
    * labels, cancel URLs, routing, permissions, and all business semantics.
    *
    * Supported args:
    * - group_args   (forwarded to wpaf_get_button_action_groups_html())
    * - any other args supported by wpaf_get_form_action_buttons_args()
    *
    * @param string $submit_name          Primary submit field name.
    * @param string $submit_label         Primary submit label.
    * @param string $cancel_url           Cancel-link URL.
    * @param array  $leading_definitions  Leading button definitions.
    * @param array  $trailing_definitions Trailing button definitions.
    * @param array  $args                 Additional action-row options.
    *
    * @return array
    */
   function wpaf_get_form_button_actions_args( $submit_name, $submit_label, $cancel_url = '', array $leading_definitions = array(), array $trailing_definitions = array(), array $args = array() ) {
      $group_args = isset( $args['group_args'] ) && is_array( $args['group_args'] ) ? $args['group_args'] : array();
      $args       = is_array( $args ) ? $args : array();

      unset( $args['group_args'] );

      $grouped_actions = function_exists( 'wpaf_get_button_action_groups_html' )
         ? wpaf_get_button_action_groups_html( $leading_definitions, $trailing_definitions, array( 'group_args' => $group_args ) )
         : array(
            'extra_actions'    => '',
            'trailing_actions' => '',
         );

      $args['extra_actions']    = isset( $grouped_actions['extra_actions'] ) ? (string) $grouped_actions['extra_actions'] : '';
      $args['trailing_actions'] = isset( $grouped_actions['trailing_actions'] ) ? (string) $grouped_actions['trailing_actions'] : '';

      return function_exists( 'wpaf_get_form_action_buttons_args' )
         ? wpaf_get_form_action_buttons_args( $submit_name, $submit_label, $cancel_url, $args )
         : $args;
   }
}


//===========================================
// Побудувати args масив для readonly/view action rows із button definitions
//===========================================
if ( ! function_exists( 'wpaf_get_readonly_button_actions_args' ) ) {
   /**
    * Build reusable args for wpaf_render_action_buttons() on readonly/view
    * screens from compact button definitions.
    *
    * This helper remains presentation-only. Callers still own URLs, labels,
    * permissions, routing, and all module business semantics.
    *
    * Supported args:
    * - group_args   (forwarded to wpaf_get_button_action_groups_html())
    * - any other args supported by wpaf_get_readonly_action_buttons_args()
    *
    * @param array $leading_definitions  Leading button definitions.
    * @param array $trailing_definitions Trailing button definitions.
    * @param array $args                 Additional action-row options.
    *
    * @return array
    */
   function wpaf_get_readonly_button_actions_args( array $leading_definitions = array(), array $trailing_definitions = array(), array $args = array() ) {
      $group_args = isset( $args['group_args'] ) && is_array( $args['group_args'] ) ? $args['group_args'] : array();
      $args       = is_array( $args ) ? $args : array();

      unset( $args['group_args'] );

      $grouped_actions = function_exists( 'wpaf_get_button_action_groups_html' )
         ? wpaf_get_button_action_groups_html( $leading_definitions, $trailing_definitions, array( 'group_args' => $group_args ) )
         : array(
            'extra_actions'    => '',
            'trailing_actions' => '',
         );

      $args['extra_actions']    = isset( $grouped_actions['extra_actions'] ) ? (string) $grouped_actions['extra_actions'] : '';
      $args['trailing_actions'] = isset( $grouped_actions['trailing_actions'] ) ? (string) $grouped_actions['trailing_actions'] : '';

      return function_exists( 'wpaf_get_readonly_action_buttons_args' )
         ? wpaf_get_readonly_action_buttons_args( $args )
         : $args;
   }
}


//===========================================
// Вивести create/edit action row одразу з compact button definitions
//===========================================
if ( ! function_exists( 'wpaf_render_form_button_actions' ) ) {
   /**
    * Render a create/edit action row directly from compact button definitions.
    *
    * This is a short shared path for simple admin forms: callers provide the
    * submit/cancel data plus optional button definitions, and the shared layer
    * handles args composition and rendering.
    *
    * Supported args:
    * - any args supported by wpaf_get_form_button_actions_args()
    * - extra_actions / trailing_actions for non-definition fallback
    *
    * @param string $submit_name          Primary submit field name.
    * @param string $submit_label         Primary submit label.
    * @param string $cancel_url           Cancel-link URL.
    * @param array  $leading_definitions  Leading button definitions.
    * @param array  $trailing_definitions Trailing button definitions.
    * @param array  $args                 Additional action-row options.
    *
    * @return void
    */
   function wpaf_render_form_button_actions( $submit_name, $submit_label, $cancel_url = '', array $leading_definitions = array(), array $trailing_definitions = array(), array $args = array() ) {
      if ( function_exists( 'wpaf_render_action_buttons' ) && function_exists( 'wpaf_get_form_button_actions_args' ) ) {
         wpaf_render_action_buttons(
            wpaf_get_form_button_actions_args( $submit_name, $submit_label, $cancel_url, $leading_definitions, $trailing_definitions, $args )
         );
         return;
      }

      if ( function_exists( 'wpaf_render_action_buttons' ) && function_exists( 'wpaf_get_form_action_buttons_args' ) ) {
         wpaf_render_action_buttons(
            wpaf_get_form_action_buttons_args(
               $submit_name,
               $submit_label,
               $cancel_url,
               array(
                  'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : '',
                  'submit_class'     => isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary',
                  'cancel_label'     => isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '',
                  'extra_actions'    => isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '',
                  'trailing_actions' => isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '',
               )
            )
         );
         return;
      }

      $wrapper_class    = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons';
      $submit_class     = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $cancel_label     = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $extra_actions    = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $trailing_actions = isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '">';
      echo '<div class="wpaf-action-buttons-leading">';
      echo '<button type="submit" name="' . esc_attr( (string) $submit_name ) . '" value="1" class="' . esc_attr( $submit_class ) . '">' . esc_html( (string) $submit_label ) . '</button>';

      if ( '' !== $cancel_url && '' !== $cancel_label ) {
         echo '<a href="' . esc_url( (string) $cancel_url ) . '" class="button">' . esc_html( $cancel_label ) . '</a>';
      }

      if ( '' !== $extra_actions ) {
         echo $extra_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</div>';

      if ( '' !== $trailing_actions ) {
         echo '<div class="wpaf-action-buttons-trailing">';
         echo $trailing_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '</div>';
      }

      echo '</div>';
   }
}

//===========================================
// Вивести readonly/view action row одразу з compact button definitions
//===========================================
if ( ! function_exists( 'wpaf_render_readonly_button_actions' ) ) {
   /**
    * Render a readonly/view action row directly from compact button definitions.
    *
    * This is a short shared path for simple readonly/history screens: callers
    * provide compact leading/trailing definitions and the shared layer handles
    * args composition and rendering.
    *
    * Supported args:
    * - any args supported by wpaf_get_readonly_button_actions_args()
    * - extra_actions / trailing_actions for non-definition fallback
    *
    * @param array $leading_definitions  Leading button definitions.
    * @param array $trailing_definitions Trailing button definitions.
    * @param array $args                 Additional action-row options.
    *
    * @return void
    */
   function wpaf_render_readonly_button_actions( array $leading_definitions = array(), array $trailing_definitions = array(), array $args = array() ) {
      if ( function_exists( 'wpaf_render_action_buttons' ) && function_exists( 'wpaf_get_readonly_button_actions_args' ) ) {
         wpaf_render_action_buttons(
            wpaf_get_readonly_button_actions_args( $leading_definitions, $trailing_definitions, $args )
         );
         return;
      }

      if ( function_exists( 'wpaf_render_action_buttons' ) && function_exists( 'wpaf_get_readonly_action_buttons_args' ) ) {
         wpaf_render_action_buttons(
            wpaf_get_readonly_action_buttons_args(
               array(
                  'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : '',
                  'extra_actions'    => isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '',
                  'trailing_actions' => isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '',
               )
            )
         );
         return;
      }

      $wrapper_class    = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons';
      $extra_actions    = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $trailing_actions = isset( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '">';
      echo '<div class="wpaf-action-buttons-leading">';

      if ( '' !== $extra_actions ) {
         echo $extra_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</div>';

      if ( '' !== $trailing_actions ) {
         echo '<div class="wpaf-action-buttons-trailing">';
         echo $trailing_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '</div>';
      }

      echo '</div>';
   }
}



//===========================================
// Вивести універсальний screen action row одним викликом
//===========================================
if ( ! function_exists( 'wpaf_render_screen_button_actions' ) ) {
   /**
    * Render a standard admin screen action row in one shared call.
    *
    * The helper keeps the shared branching between form and readonly modes in
    * one place so simple plugins do not need to manually choose between form,
    * readonly, shared args, and raw HTML fallback paths.
    *
    * Supported args:
    * - mode: form|readonly
    * - submit_name / submit_label / cancel_url / cancel_label / submit_class
    * - wrapper_class
    * - leading_definitions / trailing_definitions
    * - extra_actions / trailing_actions
    *
    * @param array $args Screen action-row configuration.
    *
    * @return void
    */
   function wpaf_render_screen_button_actions( array $args = array() ) {
      $mode                 = isset( $args['mode'] ) && is_scalar( $args['mode'] ) ? sanitize_key( (string) $args['mode'] ) : 'readonly';
      $submit_name          = isset( $args['submit_name'] ) && is_scalar( $args['submit_name'] ) ? (string) $args['submit_name'] : '';
      $submit_label         = isset( $args['submit_label'] ) && is_scalar( $args['submit_label'] ) ? (string) $args['submit_label'] : '';
      $cancel_url           = isset( $args['cancel_url'] ) && is_scalar( $args['cancel_url'] ) ? (string) $args['cancel_url'] : '';
      $cancel_label         = isset( $args['cancel_label'] ) && is_scalar( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $wrapper_class        = isset( $args['wrapper_class'] ) && is_scalar( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-action-buttons';
      $submit_class         = isset( $args['submit_class'] ) && is_scalar( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $leading_definitions  = isset( $args['leading_definitions'] ) && is_array( $args['leading_definitions'] ) ? $args['leading_definitions'] : array();
      $trailing_definitions = isset( $args['trailing_definitions'] ) && is_array( $args['trailing_definitions'] ) ? $args['trailing_definitions'] : array();
      $extra_actions        = isset( $args['extra_actions'] ) && is_scalar( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $trailing_actions     = isset( $args['trailing_actions'] ) && is_scalar( $args['trailing_actions'] ) ? (string) $args['trailing_actions'] : '';

      if ( $mode === 'form' ) {
         if ( ( ! empty( $leading_definitions ) || ! empty( $trailing_definitions ) ) && function_exists( 'wpaf_render_form_button_actions' ) ) {
            wpaf_render_form_button_actions(
               $submit_name,
               $submit_label,
               $cancel_url,
               $leading_definitions,
               $trailing_definitions,
               array(
                  'wrapper_class'    => $wrapper_class,
                  'submit_class'     => $submit_class,
                  'cancel_label'     => $cancel_label,
                  'extra_actions'    => $extra_actions,
                  'trailing_actions' => $trailing_actions,
               )
            );
            return;
         }

         if ( function_exists( 'wpaf_render_action_buttons' ) ) {
            $render_args = function_exists( 'wpaf_get_form_action_buttons_args' )
               ? wpaf_get_form_action_buttons_args(
                  $submit_name,
                  $submit_label,
                  $cancel_url,
                  array(
                     'wrapper_class'    => $wrapper_class,
                     'submit_class'     => $submit_class,
                     'cancel_label'     => $cancel_label,
                     'extra_actions'    => $extra_actions,
                     'trailing_actions' => $trailing_actions,
                  )
               )
               : array(
                  'wrapper_class'    => $wrapper_class,
                  'submit_name'      => $submit_name,
                  'submit_label'     => $submit_label,
                  'submit_class'     => $submit_class,
                  'cancel_url'       => $cancel_url,
                  'cancel_label'     => $cancel_label,
                  'extra_actions'    => $extra_actions,
                  'trailing_actions' => $trailing_actions,
               );

            wpaf_render_action_buttons( $render_args );
            return;
         }

         echo '<div class="' . esc_attr( $wrapper_class ) . '">';
         echo '<div class="wpaf-action-buttons-leading">';

         if ( $submit_name !== '' && $submit_label !== '' ) {
            echo '<button type="submit" name="' . esc_attr( $submit_name ) . '" value="1" class="' . esc_attr( $submit_class ) . '">' . esc_html( $submit_label ) . '</button>';
         }

         if ( $cancel_url !== '' && $cancel_label !== '' ) {
            echo '<a href="' . esc_url( $cancel_url ) . '" class="button">' . esc_html( $cancel_label ) . '</a>';
         }

         if ( $extra_actions !== '' ) {
            echo $extra_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         }

         echo '</div>';

         if ( $trailing_actions !== '' ) {
            echo '<div class="wpaf-action-buttons-trailing">';
            echo $trailing_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo '</div>';
         }

         echo '</div>';
         return;
      }

      if ( ( ! empty( $leading_definitions ) || ! empty( $trailing_definitions ) ) && function_exists( 'wpaf_render_readonly_button_actions' ) ) {
         wpaf_render_readonly_button_actions(
            $leading_definitions,
            $trailing_definitions,
            array(
               'wrapper_class'    => $wrapper_class,
               'extra_actions'    => $extra_actions,
               'trailing_actions' => $trailing_actions,
            )
         );
         return;
      }

      if ( function_exists( 'wpaf_render_action_buttons' ) ) {
         $render_args = function_exists( 'wpaf_get_readonly_action_buttons_args' )
            ? wpaf_get_readonly_action_buttons_args(
               array(
                  'wrapper_class'    => $wrapper_class,
                  'extra_actions'    => $extra_actions,
                  'trailing_actions' => $trailing_actions,
               )
            )
            : array(
               'wrapper_class'    => $wrapper_class,
               'extra_actions'    => $extra_actions,
               'trailing_actions' => $trailing_actions,
            );

         wpaf_render_action_buttons( $render_args );
         return;
      }

      echo '<div class="' . esc_attr( $wrapper_class ) . '">';
      echo '<div class="wpaf-action-buttons-leading">';

      if ( $extra_actions !== '' ) {
         echo $extra_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</div>';

      if ( $trailing_actions !== '' ) {
         echo '<div class="wpaf-action-buttons-trailing">';
         echo $trailing_actions; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
         echo '</div>';
      }

      echo '</div>';
   }
}



//===========================================
// Вивести confirm-form екран одразу з submit/cancel даними
//===========================================
if ( ! function_exists( 'wpaf_render_confirm_button_form' ) ) {
   /**
    * Render a simple confirm screen in one step from nonce + submit/cancel data.
    *
    * This helper is intentionally presentation-only: callers still own nonce
    * values, URLs, submit names, permissions, redirects, and all business
    * semantics. The shared layer only assembles confirm action rows and form
    * shells using existing reusable helpers.
    *
    * Supported args:
    * - wrapper_class / wrapper_style / message / message_tag / message_class
    * - before_form_html / after_form_html / form_args
    * - actions_wrapper_class / actions_wrapper_style
    * - submit_value / submit_class / submit_style
    * - cancel_label / cancel_class / cancel_text_domain
    * - extra_actions
    * - raw_submit_value / raw_submit_class / raw_submit_style
    * - raw_cancel_label / raw_cancel_class / raw_cancel_style
    *
    * @param string $nonce_action Nonce action string.
    * @param string $nonce_name   Nonce field name.
    * @param string $submit_name  Primary submit field name.
    * @param string $submit_label Primary submit label.
    * @param string $cancel_url   Cancel-link URL.
    * @param array  $args         Additional confirm render options.
    *
    * @return void
    */
   function wpaf_render_confirm_button_form( $nonce_action, $nonce_name, $submit_name, $submit_label, $cancel_url = '', array $args = array() ) {
      $actions_wrapper_class = isset( $args['actions_wrapper_class'] ) ? (string) $args['actions_wrapper_class'] : 'wpaf-confirm-actions';
      $actions_wrapper_style = isset( $args['actions_wrapper_style'] ) ? (string) $args['actions_wrapper_style'] : '';
      $submit_value          = array_key_exists( 'submit_value', $args ) ? $args['submit_value'] : '1';
      $submit_class          = isset( $args['submit_class'] ) ? (string) $args['submit_class'] : 'button button-primary';
      $submit_style          = isset( $args['submit_style'] ) ? (string) $args['submit_style'] : '';
      $cancel_label          = isset( $args['cancel_label'] ) ? (string) $args['cancel_label'] : '';
      $cancel_class          = isset( $args['cancel_class'] ) ? (string) $args['cancel_class'] : 'button';
      $cancel_text_domain    = isset( $args['cancel_text_domain'] ) ? (string) $args['cancel_text_domain'] : 'wp-add-function';
      $extra_actions         = isset( $args['extra_actions'] ) ? (string) $args['extra_actions'] : '';
      $form_args             = isset( $args['form_args'] ) && is_array( $args['form_args'] ) ? $args['form_args'] : array();

      $actions_args = function_exists( 'wpaf_get_confirm_action_buttons_args' )
         ? wpaf_get_confirm_action_buttons_args(
            $submit_name,
            $submit_label,
            $cancel_url,
            array(
               'wrapper_class'      => $actions_wrapper_class,
               'wrapper_style'      => $actions_wrapper_style,
               'submit_value'       => $submit_value,
               'submit_class'       => $submit_class,
               'submit_style'       => $submit_style,
               'cancel_label'       => $cancel_label,
               'cancel_class'       => $cancel_class,
               'cancel_text_domain' => $cancel_text_domain,
               'extra_actions'      => $extra_actions,
            )
         )
         : array(
            'wrapper_class' => $actions_wrapper_class,
            'wrapper_style' => '' !== $actions_wrapper_style
               ? $actions_wrapper_style
               : ( function_exists( 'wpaf_get_form_action_buttons_wrapper_style' )
                  ? wpaf_get_form_action_buttons_wrapper_style()
                  : 'display:flex;align-items:center;gap:6px;flex-wrap:wrap;' ),
            'submit_name'   => (string) $submit_name,
            'submit_label'  => (string) $submit_label,
            'submit_value'  => $submit_value,
            'submit_class'  => $submit_class,
            'submit_style'  => $submit_style,
            'cancel_url'    => (string) $cancel_url,
            'cancel_label'  => $cancel_label,
            'cancel_class'  => $cancel_class,
            'extra_actions' => $extra_actions,
         );

      $confirm_args = function_exists( 'wpaf_get_confirm_form_args' )
         ? wpaf_get_confirm_form_args(
            $nonce_action,
            $nonce_name,
            $actions_args,
            array(
               'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-box',
               'wrapper_style'    => isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '',
               'message'          => isset( $args['message'] ) ? (string) $args['message'] : '',
               'message_tag'      => isset( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p',
               'message_class'    => isset( $args['message_class'] ) ? (string) $args['message_class'] : 'wpaf-confirm-message',
               'before_form_html' => isset( $args['before_form_html'] ) ? (string) $args['before_form_html'] : '',
               'after_form_html'  => isset( $args['after_form_html'] ) ? (string) $args['after_form_html'] : '',
               'form_args'        => $form_args,
            )
         )
         : array(
            'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-box',
            'wrapper_style'    => isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '',
            'message'          => isset( $args['message'] ) ? (string) $args['message'] : '',
            'message_tag'      => isset( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p',
            'message_class'    => isset( $args['message_class'] ) ? (string) $args['message_class'] : 'wpaf-confirm-message',
            'before_form_html' => isset( $args['before_form_html'] ) ? (string) $args['before_form_html'] : '',
            'after_form_html'  => isset( $args['after_form_html'] ) ? (string) $args['after_form_html'] : '',
            'form_args'        => array_merge(
               array(
                  'nonce_action' => (string) $nonce_action,
                  'nonce_name'   => (string) $nonce_name,
               ),
               $form_args
            ),
            'actions_args'     => $actions_args,
         );

      if ( function_exists( 'wpaf_render_confirm_form_with_fallback' ) ) {
         wpaf_render_confirm_form_with_fallback(
            array(
               'confirm_args'      => $confirm_args,
               'raw_submit_name'   => (string) $submit_name,
               'raw_submit_label'  => (string) $submit_label,
               'raw_submit_value'  => array_key_exists( 'raw_submit_value', $args ) ? $args['raw_submit_value'] : $submit_value,
               'raw_submit_class'  => isset( $args['raw_submit_class'] ) ? (string) $args['raw_submit_class'] : $submit_class,
               'raw_submit_style'  => isset( $args['raw_submit_style'] ) ? (string) $args['raw_submit_style'] : $submit_style,
               'raw_cancel_url'    => (string) $cancel_url,
               'raw_cancel_label'  => isset( $args['raw_cancel_label'] ) ? (string) $args['raw_cancel_label'] : $cancel_label,
               'raw_cancel_class'  => isset( $args['raw_cancel_class'] ) ? (string) $args['raw_cancel_class'] : $cancel_class,
               'raw_cancel_style'  => isset( $args['raw_cancel_style'] ) ? (string) $args['raw_cancel_style'] : '',
            )
         );
         return;
      }

      if ( function_exists( 'wpaf_render_confirm_form' ) ) {
         wpaf_render_confirm_form( $confirm_args );
         return;
      }

      $wrapper_class = isset( $confirm_args['wrapper_class'] ) ? (string) $confirm_args['wrapper_class'] : 'wpaf-confirm-box';
      $wrapper_style = isset( $confirm_args['wrapper_style'] ) ? (string) $confirm_args['wrapper_style'] : '';
      $style_attr    = '' !== $wrapper_style ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '"' . $style_attr . '>';
      wpaf_render_action_buttons( $actions_args );
      echo '</div>';
   }
}


//===========================================
// Рендерити admin notice разом із button-like action definitions
//===========================================
if ( ! function_exists( 'wpaf_render_admin_notice_with_button_actions' ) ) {
   /**
    * Render an admin notice together with compact button-like action links.
    *
    * This helper is generic and render-only. Callers still own the message,
    * button definitions, URLs, permissions, and business branching.
    *
    * Supported args:
    * - notice_args       (forwarded to wpaf_render_admin_notice())
    * - action_links_args (forwarded to wpaf_get_notice_action_links_html())
    *
    * @param string $message     Notice text.
    * @param array  $definitions Compact button definitions.
    * @param array  $args        Additional render options.
    *
    * @return void
    */
   function wpaf_render_admin_notice_with_button_actions( $message, array $definitions = array(), array $args = array() ) {
      $items = function_exists( 'wpaf_build_button_action_items' )
         ? wpaf_build_button_action_items( $definitions )
         : array();

      if ( function_exists( 'wpaf_render_admin_notice_with_actions' ) ) {
         wpaf_render_admin_notice_with_actions( $message, $items, $args );
         return;
      }

      $notice_args       = isset( $args['notice_args'] ) && is_array( $args['notice_args'] )
         ? $args['notice_args']
         : ( function_exists( 'wpaf_get_admin_notice_args' )
            ? wpaf_get_admin_notice_args(
               isset( $args['type'] ) ? $args['type'] : 'info',
               ! empty( $args['dismissible'] ),
               array(
                  'class'       => isset( $args['class'] ) ? $args['class'] : '',
                  'message_tag' => isset( $args['message_tag'] ) ? $args['message_tag'] : 'p',
               )
            )
            : array()
         );
      $action_links_args = isset( $args['action_links_args'] ) && is_array( $args['action_links_args'] ) ? $args['action_links_args'] : array();

      if ( function_exists( 'wpaf_render_admin_notice' ) ) {
         wpaf_render_admin_notice( $message, $notice_args );
      }

      if ( empty( $items ) ) {
         return;
      }

      if ( ! isset( $action_links_args['wrapper_tag'] ) ) {
         $action_links_args['wrapper_tag'] = 'p';
      }

      echo wpaf_get_action_links_html( $items, $action_links_args );
   }
}


//===========================================
// Побудувати label для кнопки пошуку з іконкою
//===========================================

//===========================================
// Побудувати label для кнопки фільтра з іконкою
//===========================================
if ( ! function_exists( 'wpaf_get_filter_button_label' ) ) {
   /**
    * Build a reusable filter button label with a compact filter icon.
    *
    * The helper stays presentation-only: it does not change filter logic,
    * request parsing, saved state, or list-table behavior.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_filter_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Filter', $text_domain );
      }

      if ( 0 === strpos( $label, '⏳' ) ) {
         return $label;
      }

      return '⏳ ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки скидання з іконкою
//===========================================
if ( ! function_exists( 'wpaf_get_reset_button_label' ) ) {
   /**
    * Build a reusable reset button label with a compact reset icon.
    *
    * The helper stays presentation-only: it does not change reset logic,
    * query cleanup, redirects, or list-table behavior.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_reset_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Reset', $text_domain );
      }

      if ( 0 === strpos( $label, '🔄' ) ) {
         return $label;
      }

      return '🔄 ' . $label;
   }
}



//===========================================
// Рендерити стандартні кнопки Filter + Reset для list/table screens
//===========================================
if ( ! function_exists( 'wpaf_render_filter_reset_actions' ) ) {
   /**
    * Render reusable Filter and Reset toolbar actions for list/table screens.
    *
    * The helper stays presentation-only: modules keep their own filter fields,
    * saved state, request parsing, redirects, and business rules. The helper
    * only renders shared action buttons via button_action().
    *
    * Supported args:
    * - show_filter (bool)
    * - show_reset (bool)
    * - filter_label (string)
    * - reset_label (string)
    * - filter_button_name (string)
    * - reset_button_name (string)
    * - text_domain (string)
    *
    * @param array $args Render arguments.
    *
    * @return bool True when button_action() was used, false otherwise.
    */
   function wpaf_render_filter_reset_actions( array $args = array() ) {
      if ( ! function_exists( 'button_action' ) ) {
         return false;
      }

      $show_filter        = isset( $args['show_filter'] ) ? (bool) $args['show_filter'] : true;
      $show_reset         = isset( $args['show_reset'] ) ? (bool) $args['show_reset'] : true;
      $filter_label       = isset( $args['filter_label'] ) ? (string) $args['filter_label'] : '';
      $reset_label        = isset( $args['reset_label'] ) ? (string) $args['reset_label'] : '';
      $filter_button_name = isset( $args['filter_button_name'] ) ? (string) $args['filter_button_name'] : 'button_filter';
      $reset_button_name  = isset( $args['reset_button_name'] ) ? (string) $args['reset_button_name'] : 'button_reset';
      $text_domain        = isset( $args['text_domain'] ) ? (string) $args['text_domain'] : 'wp-add-function';

      if ( '' === $filter_button_name ) {
         $filter_button_name = 'button_filter';
      }

      if ( '' === $reset_button_name ) {
         $reset_button_name = 'button_reset';
      }

      if ( $show_filter ) {
         if ( function_exists( 'wpaf_get_filter_button_label' ) ) {
            $filter_label = wpaf_get_filter_button_label( $filter_label, $text_domain );
         } elseif ( '' === trim( $filter_label ) ) {
            $filter_label = __( 'Filter', $text_domain );
         }

         button_action( $filter_label, $filter_button_name );
      }

      if ( $show_reset ) {
         if ( function_exists( 'wpaf_get_reset_button_label' ) ) {
            $reset_label = wpaf_get_reset_button_label( $reset_label, $text_domain );
         } elseif ( '' === trim( $reset_label ) ) {
            $reset_label = __( 'Reset', $text_domain );
         }

         button_action( $reset_label, $reset_button_name );
      }

      return true;
   }
}


//===========================================
// Побудувати label для кнопки Apply з іконкою підтвердження
//===========================================
if ( ! function_exists( 'wpaf_get_apply_button_label' ) ) {
   /**
    * Build a reusable Apply button label with a compact confirmation icon.
    *
    * This helper is generic and is safe for non-period submit buttons.
    * Period/date controls should use wpaf_get_period_apply_button_label().
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_apply_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Apply', $text_domain );
      }

      if ( 0 === strpos( $label, '✅' ) ) {
         return $label;
      }

      return '✅ ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки застосування періоду з іконкою календаря
//===========================================
if ( ! function_exists( 'wpaf_get_period_apply_button_label' ) ) {
   /**
    * Build a reusable Apply button label for date/period controls.
    *
    * The helper stays presentation-only: it does not change period logic,
    * request parsing, saved state, or list-table behavior.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_period_apply_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Apply', $text_domain );
      }

      if ( 0 === strpos( $label, '🗓️' ) ) {
         return $label;
      }

      return '🗓️ ' . $label;
   }
}


//===========================================
// Рендерити стандартну кнопку застосування періоду для list/table screens
//===========================================
if ( ! function_exists( 'wpaf_render_period_apply_action' ) ) {
   /**
    * Render a reusable period-apply action button for list/table toolbars.
    *
    * The helper stays presentation-only: modules keep their own date fields,
    * period validation, request parsing, and business rules. The helper only
    * renders a shared button via button_action().
    *
    * Supported args:
    * - label (string)
    * - text_domain (string)
    * - button_name (string)
    *
    * @param array $args Render arguments.
    *
    * @return bool True when button_action() was used, false otherwise.
    */
   function wpaf_render_period_apply_action( array $args = array() ) {
      if ( ! function_exists( 'button_action' ) ) {
         return false;
      }

      $label       = isset( $args['label'] ) ? (string) $args['label'] : '';
      $text_domain = isset( $args['text_domain'] ) ? (string) $args['text_domain'] : 'wp-add-function';
      $button_name = isset( $args['button_name'] ) ? (string) $args['button_name'] : 'button_period';

      if ( '' === $button_name ) {
         $button_name = 'button_period';
      }

      if ( function_exists( 'wpaf_get_period_apply_button_label' ) ) {
         $label = wpaf_get_period_apply_button_label( $label, $text_domain );
      } elseif ( '' === trim( $label ) ) {
         $label = __( 'Apply', $text_domain );
      }

      button_action( $label, $button_name );

      return true;
   }
}

//===========================================
// Побудувати label для кнопки скасування зі стрілкою
//===========================================
if ( ! function_exists( 'wpaf_get_cancel_button_label' ) ) {
   /**
    * Build a reusable Cancel button label with a back arrow icon.
    *
    * The helper stays presentation-only: it does not change navigation,
    * redirects, confirm flow, form handling, or module-specific rules.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_cancel_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Cancel', $text_domain );
      }

      if ( 0 === strpos( $label, '⬅️' ) ) {
         return $label;
      }

      return '⬅️ ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки додавання з іконкою плюса
//===========================================
if ( ! function_exists( 'wpaf_get_add_button_label' ) ) {
   /**
    * Build a reusable Add button label with a plus icon.
    *
    * The helper stays presentation-only: it does not change add/create
    * semantics, form behavior, row templates, or module-specific rules.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_add_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Add', $text_domain );
      }

      if ( 0 === strpos( $label, '➕' ) ) {
         return $label;
      }

      return '➕ ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки збереження з іконкою дискети
//===========================================
if ( ! function_exists( 'wpaf_get_save_button_label' ) ) {
   /**
    * Build a reusable Save button label with a floppy-disk icon.
    *
    * The helper stays presentation-only: it does not change save logic,
    * form handling, validation, nonces, or module-specific rules.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_save_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Save', $text_domain );
      }

      if ( 0 === strpos( $label, '💾' ) ) {
         return $label;
      }

      return '💾 ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки видалення з іконкою кошика
//===========================================
if ( ! function_exists( 'wpaf_get_edit_button_label' ) ) {
   /**
    * Build a reusable Edit button label with a compact edit icon.
    *
    * The helper stays presentation-only: it only standardizes the button text
    * and does not change edit permissions, routes, state handling, or module
    * business logic.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_edit_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Edit', $text_domain );
      }

      if ( 0 === strpos( $label, '📝' ) ) {
         return $label;
      }

      return '📝 ' . $label;
   }
}


//===========================================
// Побудувати label для кнопки повернення з іконкою книги
//===========================================
if ( ! function_exists( 'wpaf_get_return_button_label' ) ) {
   /**
    * Build a reusable Return button label with a compact book icon.
    *
    * The helper stays presentation-only: it only standardizes the button text
    * and does not change navigation URLs, redirects, router behavior, or any
    * module-specific flow.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_return_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Return', $text_domain );
      }

      if ( 0 === strpos( $label, '📖' ) ) {
         return $label;
      }

      return '📖 ' . $label;
   }
}




//===========================================
// Побудувати label для кнопки історії з іконкою сувою
//===========================================
if ( ! function_exists( 'wpaf_get_history_button_label' ) ) {
   /**
    * Build a reusable History button label with a compact scroll icon.
    *
    * The helper stays presentation-only: it only standardizes the button text
    * and does not change history routing, revision lookup, or module-specific
    * business logic.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_history_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'History', $text_domain );
      }

      if ( 0 === strpos( $label, '📜' ) ) {
         return $label;
      }

      return '📜 ' . $label;
   }
}

//===========================================
// Побудувати label для кнопки відкриття з іконкою теки
//===========================================
if ( ! function_exists( 'wpaf_get_open_button_label' ) ) {
   /**
    * Build a reusable Open button label with a compact folder icon.
    *
    * The helper stays presentation-only: it only standardizes the button text
    * and does not change routes, revision lookup, permissions, or module-
    * specific business logic.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_open_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Open', $text_domain );
      }

      if ( 0 === strpos( $label, '📂' ) ) {
         return $label;
      }

      return '📂 ' . $label;
   }
}



//===========================================
// Побудувати label для кнопки видалення з іконкою кошика
//===========================================
if ( ! function_exists( 'wpaf_get_delete_button_label' ) ) {
   /**
    * Build a reusable Delete button label with a trash icon.
    *
    * The helper stays presentation-only: it does not change delete logic,
    * nonces, permissions, confirmations, or module-specific rules.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_delete_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Delete', $text_domain );
      }

      if ( 0 === strpos( $label, '🗑️' ) ) {
         return $label;
      }

      return '🗑️ ' . $label;
   }
}




//===========================================
// Побудувати label для кнопки видалення рядка з іконкою хрестика
//===========================================
if ( ! function_exists( 'wpaf_get_remove_button_label' ) ) {
   /**
    * Build a reusable Remove button label with a cross icon.
    *
    * The helper stays presentation-only: it does not change row deletion
    * behavior, JS handlers, row indexing, validation, or module-specific rules.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_remove_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Remove', $text_domain );
      }

      if ( 0 === strpos( $label, '❌' ) ) {
         return $label;
      }

      return '❌ ' . $label;
   }
}

if ( ! function_exists( 'wpaf_get_search_button_label' ) ) {
   /**
    * Build a reusable search button label with a search icon.
    *
    * The helper stays presentation-only: it does not change search logic,
    * request parsing, filters, or list-table behavior.
    *
    * @param string $label       Optional custom label.
    * @param string $text_domain Text domain for the default label.
    *
    * @return string
    */
   function wpaf_get_search_button_label( $label = '', $text_domain = 'wp-add-function' ) {
      $label = trim( (string) $label );

      if ( '' === $label ) {
         $label = __( 'Search', $text_domain );
      }

      if ( 0 === strpos( $label, '🔍' ) ) {
         return $label;
      }

      return '🔍 ' . $label;
   }
}


//===========================================
// Рендерити стандартні export actions для list/table screens
//===========================================
if ( ! function_exists( 'wpaf_render_list_export_actions' ) ) {
   /**
    * Render a reusable export-action block for list/table toolbars.
    *
    * The helper stays presentation-only: modules still decide whether a
    * specific export should be available. The helper only renders shared UI
    * controls for CSV / HTML / PDF exports and keeps request-based export URLs
    * in one place.
    *
    * Supported args:
    * - show_csv (bool)
    * - show_html (bool)
    * - show_pdf (bool)
    * - text_domain (string)
    * - csv_action (string)       Submit button name for CSV export.
    * - csv_label (string)        Optional custom CSV label.
    * - html_label (string)       Optional custom HTML label.
    * - pdf_label (string)        Optional custom PDF label.
    * - html_format (string)      Export format for the HTML link.
    * - pdf_format (string)       Export format for the PDF link.
    * - html_target (string)      Target attribute for the HTML link.
    * - pdf_target (string)       Target attribute for the PDF link.
    * - html_link_class (string)  CSS classes for the HTML link.
    * - pdf_link_class (string)   CSS classes for the PDF link.
    * - pdf_rel (string)          Rel attribute for the PDF link.
    *
    * @param array $args Export rendering options.
    *
    * @return void
    */
   function wpaf_render_list_export_actions( array $args = array() ) {
      $defaults = array(
         'show_csv'        => false,
         'show_html'       => false,
         'show_pdf'        => false,
         'text_domain'     => 'wp-add-function',
         'csv_action'      => 'button_export_csv',
         'csv_label'       => '',
         'html_label'      => '',
         'pdf_label'       => '',
         'html_format'     => 'html',
         'pdf_format'      => 'pdf_viewer',
         'html_target'     => '_blank',
         'pdf_target'      => '_blank',
         'html_link_class' => 'page-title-action button',
         'pdf_link_class'  => 'page-title-action button',
         'pdf_rel'         => 'noopener noreferrer',
      );

      $args = wp_parse_args( $args, $defaults );

      $text_domain = (string) $args['text_domain'];
      $csv_label   = '' !== trim( (string) $args['csv_label'] ) ? (string) $args['csv_label'] : __( 'CSV', $text_domain );
      $html_label  = '' !== trim( (string) $args['html_label'] ) ? (string) $args['html_label'] : __( 'HTML', $text_domain );
      $pdf_label   = '' !== trim( (string) $args['pdf_label'] ) ? (string) $args['pdf_label'] : __( 'PDF', $text_domain );

      if ( ! empty( $args['show_csv'] ) && function_exists( 'button_action' ) ) {
         button_action( $csv_label, (string) $args['csv_action'] );
      }

      if ( ! empty( $args['show_html'] ) && function_exists( 'wpaf_get_export_url_from_request' ) ) {
         $export_url = wpaf_get_export_url_from_request( (string) $args['html_format'] );
         ?>
         <a href="<?php echo esc_url( $export_url ); ?>" target="<?php echo esc_attr( (string) $args['html_target'] ); ?>" class="<?php echo esc_attr( (string) $args['html_link_class'] ); ?>"><?php echo esc_html( $html_label ); ?></a>
         <?php
      }

      if ( ! empty( $args['show_pdf'] ) && function_exists( 'wpaf_get_export_url_from_request' ) ) {
         $export_url = wpaf_get_export_url_from_request( (string) $args['pdf_format'] );
         ?>
         <a href="<?php echo esc_url( $export_url ); ?>" target="<?php echo esc_attr( (string) $args['pdf_target'] ); ?>" rel="<?php echo esc_attr( (string) $args['pdf_rel'] ); ?>" class="<?php echo esc_attr( (string) $args['pdf_link_class'] ); ?>"><?php echo esc_html( $pdf_label ); ?></a>
         <?php
      }
   }
}


//===========================================
// Рендерити повний reusable toolbar action set для list/table screens
//===========================================
if ( ! function_exists( 'wpaf_render_list_toolbar_actions' ) ) {
   /**
    * Render a reusable toolbar action set for list/table screens.
    *
    * The helper is composition-only: it combines shared Filter / Reset and
    * CSV / HTML / PDF actions, while modules keep their own filter fields,
    * date controls, search box, request parsing, permissions, and business
    * rules. This keeps toolbar wiring out of module classes without moving
    * any domain logic into wp-add-function.
    *
    * Supported args:
    * - show_filter (bool)
    * - show_reset (bool)
    * - filter_label (string)
    * - reset_label (string)
    * - filter_button_name (string)
    * - reset_button_name (string)
    * - show_csv (bool)
    * - show_html (bool)
    * - show_pdf (bool)
    * - text_domain (string)
    * - csv_action (string)
    * - csv_label (string)
    * - html_label (string)
    * - pdf_label (string)
    * - html_format (string)
    * - pdf_format (string)
    * - html_target (string)
    * - pdf_target (string)
    * - html_link_class (string)
    * - pdf_link_class (string)
    * - pdf_rel (string)
    *
    * @param array $args Toolbar rendering options.
    *
    * @return bool True when at least one shared helper/fallback rendered.
    */
   function wpaf_render_list_toolbar_actions( array $args = array() ) {
      $defaults = array(
         'show_filter'        => true,
         'show_reset'         => true,
         'filter_label'       => '',
         'reset_label'        => '',
         'filter_button_name' => 'button_filter',
         'reset_button_name'  => 'button_reset',
         'show_csv'           => false,
         'show_html'          => false,
         'show_pdf'           => false,
         'text_domain'        => 'wp-add-function',
         'csv_action'         => 'button_export_csv',
         'csv_label'          => '',
         'html_label'         => '',
         'pdf_label'          => '',
         'html_format'        => 'html',
         'pdf_format'         => 'pdf_viewer',
         'html_target'        => '_blank',
         'pdf_target'         => '_blank',
         'html_link_class'    => 'page-title-action button',
         'pdf_link_class'     => 'page-title-action button',
         'pdf_rel'            => 'noopener noreferrer',
      );

      $args     = wp_parse_args( $args, $defaults );
      $rendered = false;

      if ( function_exists( 'wpaf_render_filter_reset_actions' ) ) {
         $rendered = wpaf_render_filter_reset_actions(
            array(
               'show_filter'        => ! empty( $args['show_filter'] ),
               'show_reset'         => ! empty( $args['show_reset'] ),
               'filter_label'       => (string) $args['filter_label'],
               'reset_label'        => (string) $args['reset_label'],
               'filter_button_name' => (string) $args['filter_button_name'],
               'reset_button_name'  => (string) $args['reset_button_name'],
               'text_domain'        => (string) $args['text_domain'],
            )
         ) || $rendered;
      } elseif ( function_exists( 'button_action' ) ) {
         if ( ! empty( $args['show_filter'] ) ) {
            $filter_label = '' !== trim( (string) $args['filter_label'] ) ? (string) $args['filter_label'] : __( 'Filter', (string) $args['text_domain'] );
            button_action( $filter_label, (string) $args['filter_button_name'] );
            $rendered = true;
         }

         if ( ! empty( $args['show_reset'] ) ) {
            $reset_label = '' !== trim( (string) $args['reset_label'] ) ? (string) $args['reset_label'] : __( 'Reset', (string) $args['text_domain'] );
            button_action( $reset_label, (string) $args['reset_button_name'] );
            $rendered = true;
         }
      }

      if ( function_exists( 'wpaf_render_list_export_actions' ) ) {
         wpaf_render_list_export_actions(
            array(
               'show_csv'        => ! empty( $args['show_csv'] ),
               'show_html'       => ! empty( $args['show_html'] ),
               'show_pdf'        => ! empty( $args['show_pdf'] ),
               'text_domain'     => (string) $args['text_domain'],
               'csv_action'      => (string) $args['csv_action'],
               'csv_label'       => (string) $args['csv_label'],
               'html_label'      => (string) $args['html_label'],
               'pdf_label'       => (string) $args['pdf_label'],
               'html_format'     => (string) $args['html_format'],
               'pdf_format'      => (string) $args['pdf_format'],
               'html_target'     => (string) $args['html_target'],
               'pdf_target'      => (string) $args['pdf_target'],
               'html_link_class' => (string) $args['html_link_class'],
               'pdf_link_class'  => (string) $args['pdf_link_class'],
               'pdf_rel'         => (string) $args['pdf_rel'],
            )
         );

         if ( ! empty( $args['show_csv'] ) || ! empty( $args['show_html'] ) || ! empty( $args['show_pdf'] ) ) {
            $rendered = true;
         }
      } elseif ( function_exists( 'button_action' ) || function_exists( 'wpaf_get_export_url_from_request' ) ) {
         $text_domain = (string) $args['text_domain'];

         if ( ! empty( $args['show_csv'] ) && function_exists( 'button_action' ) ) {
            $csv_label = '' !== trim( (string) $args['csv_label'] ) ? (string) $args['csv_label'] : __( 'CSV', $text_domain );
            button_action( $csv_label, (string) $args['csv_action'] );
            $rendered = true;
         }

         if ( ! empty( $args['show_html'] ) && function_exists( 'wpaf_get_export_url_from_request' ) ) {
            $html_label = '' !== trim( (string) $args['html_label'] ) ? (string) $args['html_label'] : __( 'HTML', $text_domain );
            $export_url = wpaf_get_export_url_from_request( (string) $args['html_format'] );
            echo '<a href="' . esc_url( $export_url ) . '" target="' . esc_attr( (string) $args['html_target'] ) . '" class="' . esc_attr( (string) $args['html_link_class'] ) . '">' . esc_html( $html_label ) . '</a>';
            $rendered = true;
         }

         if ( ! empty( $args['show_pdf'] ) && function_exists( 'wpaf_get_export_url_from_request' ) ) {
            $pdf_label  = '' !== trim( (string) $args['pdf_label'] ) ? (string) $args['pdf_label'] : __( 'PDF', $text_domain );
            $export_url = wpaf_get_export_url_from_request( (string) $args['pdf_format'] );
            echo '<a href="' . esc_url( $export_url ) . '" target="' . esc_attr( (string) $args['pdf_target'] ) . '" rel="' . esc_attr( (string) $args['pdf_rel'] ) . '" class="' . esc_attr( (string) $args['pdf_link_class'] ) . '">' . esc_html( $pdf_label ) . '</a>';
            $rendered = true;
         }
      }

      return $rendered;
   }
}



//===========================================
// Отримати універсальний confirm-message HTML для admin screens
//===========================================
if ( ! function_exists( 'wpaf_get_confirm_message_html' ) ) {
   /**
    * Build a small reusable confirm-message block for admin screens.
    *
    * This helper stays render-only: the caller still owns the message text,
    * semantics, submit flow, redirects, and permissions.
    *
    * Supported args:
    * - tag
    * - color
    * - style
    * - class
    *
    * @param string $message Confirm message text.
    * @param array  $args    Confirm message options.
    *
    * @return string
    */
   function wpaf_get_confirm_message_html( $message, array $args = array() ) {
      $message = (string) $message;
      $tag     = isset( $args['tag'] ) ? sanitize_key( (string) $args['tag'] ) : 'h4';
      $color   = isset( $args['color'] ) ? trim( (string) $args['color'] ) : '';
      $style   = isset( $args['style'] ) ? trim( (string) $args['style'] ) : '';
      $class   = isset( $args['class'] ) ? trim( (string) $args['class'] ) : '';

      if ( ! in_array( $tag, array( 'p', 'div', 'h4' ), true ) ) {
         $tag = 'h4';
      }

      if ( $color !== '' ) {
         $style = trim( 'color:' . $color . ( $style !== '' ? ';' . $style : '' ), ';' );
      }

      $attrs = '';

      if ( $class !== '' ) {
         $attrs .= ' class="' . esc_attr( $class ) . '"';
      }

      if ( $style !== '' ) {
         $attrs .= ' style="' . esc_attr( $style ) . '"';
      }

      return '<' . $tag . $attrs . '>' . esc_html( $message ) . '</' . $tag . '>';
   }
}


//===========================================
// Побудувати args масив для confirm-form/layout screens
//===========================================
if ( ! function_exists( 'wpaf_get_confirm_form_args' ) ) {
   /**
    * Build reusable args for wpaf_render_confirm_form().
    *
    * This helper stays render-only: callers still own nonce names, URLs,
    * submit names, permission checks, redirects, and all business semantics.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - message
    * - message_tag
    * - message_class
    * - before_form_html
    * - after_form_html
    * - form_args
    *
    * @param string $nonce_action Nonce action string.
    * @param string $nonce_name   Nonce field name.
    * @param array  $actions_args Ready actions_args for wpaf_render_action_buttons().
    * @param array  $args         Additional confirm-form options.
    *
    * @return array
    */
   function wpaf_get_confirm_form_args( $nonce_action, $nonce_name, array $actions_args = array(), array $args = array() ) {
      $form_args = isset( $args['form_args'] ) && is_array( $args['form_args'] ) ? $args['form_args'] : array();

      $form_args = array_merge(
         array(
            'nonce_action' => (string) $nonce_action,
            'nonce_name'   => (string) $nonce_name,
         ),
         $form_args
      );

      return array(
         'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-box',
         'wrapper_style'    => isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '',
         'message'          => isset( $args['message'] ) ? (string) $args['message'] : '',
         'message_tag'      => isset( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p',
         'message_class'    => isset( $args['message_class'] ) ? (string) $args['message_class'] : 'wpaf-confirm-message',
         'before_form_html' => isset( $args['before_form_html'] ) ? (string) $args['before_form_html'] : '',
         'after_form_html'  => isset( $args['after_form_html'] ) ? (string) $args['after_form_html'] : '',
         'form_args'        => $form_args,
         'actions_args'     => $actions_args,
      );
   }
}




//===========================================
// Вивести універсальний confirm-form/layout із fallback-шарами
//===========================================
if ( ! function_exists( 'wpaf_render_confirm_form_with_fallback' ) ) {
   /**
    * Render a confirm form shell using the modern helper when available and
    * fall back to progressively simpler shared/raw form rendering otherwise.
    *
    * This helper remains render-only. Callers still own nonce values,
    * submit names, labels, URLs, permissions, redirects, and all business
    * semantics.
    *
    * Supported args:
    * - confirm_args
    * - wrapper_class
    * - wrapper_style
    * - message
    * - message_tag
    * - message_class
    * - before_form_html
    * - after_form_html
    * - form_args
    * - actions_args
    * - raw_submit_name
    * - raw_submit_label
    * - raw_submit_value
    * - raw_submit_class
    * - raw_submit_style
    * - raw_cancel_url
    * - raw_cancel_label
    * - raw_cancel_class
    * - raw_cancel_style
    *
    * @param array $args Confirm-layout render options.
    *
    * @return void
    */
   function wpaf_render_confirm_form_with_fallback( array $args = array() ) {
      $confirm_args = isset( $args['confirm_args'] ) && is_array( $args['confirm_args'] ) ? $args['confirm_args'] : array();

      if ( empty( $confirm_args ) ) {
         $confirm_args = array(
            'wrapper_class'    => isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-box',
            'wrapper_style'    => isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '',
            'message'          => isset( $args['message'] ) ? (string) $args['message'] : '',
            'message_tag'      => isset( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p',
            'message_class'    => isset( $args['message_class'] ) ? (string) $args['message_class'] : 'wpaf-confirm-message',
            'before_form_html' => isset( $args['before_form_html'] ) ? (string) $args['before_form_html'] : '',
            'after_form_html'  => isset( $args['after_form_html'] ) ? (string) $args['after_form_html'] : '',
            'form_args'        => isset( $args['form_args'] ) && is_array( $args['form_args'] ) ? $args['form_args'] : array(),
            'actions_args'     => isset( $args['actions_args'] ) && is_array( $args['actions_args'] ) ? $args['actions_args'] : array(),
         );
      }

      $wrapper_class     = isset( $confirm_args['wrapper_class'] ) ? (string) $confirm_args['wrapper_class'] : 'wpaf-confirm-box';
      $wrapper_style     = isset( $confirm_args['wrapper_style'] ) ? (string) $confirm_args['wrapper_style'] : '';
      $message           = isset( $confirm_args['message'] ) ? (string) $confirm_args['message'] : '';
      $message_tag       = isset( $confirm_args['message_tag'] ) ? sanitize_key( (string) $confirm_args['message_tag'] ) : 'p';
      $message_class     = isset( $confirm_args['message_class'] ) ? (string) $confirm_args['message_class'] : 'wpaf-confirm-message';
      $before_form_html  = isset( $confirm_args['before_form_html'] ) ? (string) $confirm_args['before_form_html'] : '';
      $after_form_html   = isset( $confirm_args['after_form_html'] ) ? (string) $confirm_args['after_form_html'] : '';
      $form_args         = isset( $confirm_args['form_args'] ) && is_array( $confirm_args['form_args'] ) ? $confirm_args['form_args'] : array();
      $actions_args      = isset( $confirm_args['actions_args'] ) && is_array( $confirm_args['actions_args'] ) ? $confirm_args['actions_args'] : array();
      $raw_submit_name   = isset( $args['raw_submit_name'] ) ? (string) $args['raw_submit_name'] : ( isset( $actions_args['submit_name'] ) ? (string) $actions_args['submit_name'] : '' );
      $raw_submit_label  = isset( $args['raw_submit_label'] ) ? (string) $args['raw_submit_label'] : ( isset( $actions_args['submit_label'] ) ? (string) $actions_args['submit_label'] : '' );
      $raw_submit_value  = isset( $args['raw_submit_value'] ) ? $args['raw_submit_value'] : ( isset( $actions_args['submit_value'] ) ? $actions_args['submit_value'] : 1 );
      $raw_submit_class  = isset( $args['raw_submit_class'] ) ? (string) $args['raw_submit_class'] : ( isset( $actions_args['submit_class'] ) ? (string) $actions_args['submit_class'] : 'button button-primary' );
      $raw_submit_style  = isset( $args['raw_submit_style'] ) ? (string) $args['raw_submit_style'] : ( isset( $actions_args['submit_style'] ) ? (string) $actions_args['submit_style'] : '' );
      $raw_cancel_url    = isset( $args['raw_cancel_url'] ) ? (string) $args['raw_cancel_url'] : ( isset( $actions_args['cancel_url'] ) ? (string) $actions_args['cancel_url'] : '' );
      $raw_cancel_label  = isset( $args['raw_cancel_label'] ) ? (string) $args['raw_cancel_label'] : ( isset( $actions_args['cancel_label'] ) ? (string) $actions_args['cancel_label'] : '' );
      $raw_cancel_class  = isset( $args['raw_cancel_class'] ) ? (string) $args['raw_cancel_class'] : ( isset( $actions_args['cancel_class'] ) ? (string) $actions_args['cancel_class'] : 'button' );
      $raw_cancel_style  = isset( $args['raw_cancel_style'] ) ? (string) $args['raw_cancel_style'] : '';

      if ( ! in_array( $message_tag, array( 'p', 'div' ), true ) ) {
         $message_tag = 'p';
      }

      if ( '' === $wrapper_style && function_exists( 'wpaf_get_confirm_layout_wrapper_style' ) ) {
         $wrapper_style = wpaf_get_confirm_layout_wrapper_style();
      }

      if ( function_exists( 'wpaf_render_confirm_form' ) ) {
         $confirm_args['wrapper_class'] = $wrapper_class;
         $confirm_args['wrapper_style'] = $wrapper_style;
         $confirm_args['message'] = $message;
         $confirm_args['message_tag'] = $message_tag;
         $confirm_args['message_class'] = $message_class;
         $confirm_args['before_form_html'] = $before_form_html;
         $confirm_args['after_form_html'] = $after_form_html;
         $confirm_args['form_args'] = $form_args;
         $confirm_args['actions_args'] = $actions_args;

         wpaf_render_confirm_form( $confirm_args );
         return;
      }

      $wrapper_style_attr = $wrapper_style !== '' ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '"' . $wrapper_style_attr . '>';

      if ( $message !== '' ) {
         echo '<' . $message_tag . ' class="' . esc_attr( $message_class ) . '">' . esc_html( $message ) . '</' . $message_tag . '>';
      }

      if ( $before_form_html !== '' ) {
         echo $before_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      if ( function_exists( 'wpaf_render_post_form_start' ) && function_exists( 'wpaf_render_action_buttons' ) && function_exists( 'wpaf_render_form_end' ) ) {
         wpaf_render_post_form_start( $form_args );
         wpaf_render_action_buttons( $actions_args );
         wpaf_render_form_end();
      } else {
         $nonce_action = isset( $form_args['nonce_action'] ) ? (string) $form_args['nonce_action'] : '';
         $nonce_name   = isset( $form_args['nonce_name'] ) ? (string) $form_args['nonce_name'] : '';
         $submit_style_attr = $raw_submit_style !== '' ? ' style="' . esc_attr( $raw_submit_style ) . '"' : '';
         $cancel_style_attr = $raw_cancel_style !== '' ? ' style="' . esc_attr( $raw_cancel_style ) . '"' : '';

         echo '<form method="post">';

         if ( $nonce_action !== '' && $nonce_name !== '' ) {
            wp_nonce_field( $nonce_action, $nonce_name );
         }

         echo '<div class="wpaf-confirm-actions" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">';

         if ( $raw_submit_name !== '' && $raw_submit_label !== '' ) {
            echo '<button type="submit" name="' . esc_attr( $raw_submit_name ) . '" value="' . esc_attr( $raw_submit_value ) . '" class="' . esc_attr( $raw_submit_class ) . '"' . $submit_style_attr . '>' . esc_html( $raw_submit_label ) . '</button>';
         }

         if ( $raw_cancel_url !== '' && $raw_cancel_label !== '' ) {
            echo '<a href="' . esc_url( $raw_cancel_url ) . '" class="' . esc_attr( $raw_cancel_class ) . '"' . $cancel_style_attr . '>' . esc_html( $raw_cancel_label ) . '</a>';
         }

         echo '</div>';
         echo '</form>';
      }

      if ( $after_form_html !== '' ) {
         echo $after_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</div>';
   }
}

//===========================================
// Вивести універсальну confirm-form/layout для admin screens
//===========================================
if ( ! function_exists( 'wpaf_render_confirm_form' ) ) {
   /**
    * Render a generic confirm layout with message, POST form, and actions.
    *
    * The caller still owns all business semantics: permission checks, submit
    * names, nonce names, messages, redirects, and state transitions.
    *
    * Supported args:
    * - wrapper_class
    * - wrapper_style
    * - message
    * - message_tag
    * - message_class
    * - before_form_html
    * - after_form_html
    * - form_args
    * - actions_args
    *
    * @param array $args Confirm layout options.
    *
    * @return void
    */
   function wpaf_render_confirm_form( array $args = array() ) {
      $wrapper_class   = isset( $args['wrapper_class'] ) ? (string) $args['wrapper_class'] : 'wpaf-confirm-box';
      $wrapper_style   = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : '';
      $message         = isset( $args['message'] ) ? (string) $args['message'] : '';
      $message_tag     = isset( $args['message_tag'] ) ? sanitize_key( (string) $args['message_tag'] ) : 'p';
      $message_class   = isset( $args['message_class'] ) ? (string) $args['message_class'] : 'wpaf-confirm-message';
      $before_form_html = isset( $args['before_form_html'] ) ? (string) $args['before_form_html'] : '';
      $after_form_html  = isset( $args['after_form_html'] ) ? (string) $args['after_form_html'] : '';
      $form_args       = isset( $args['form_args'] ) && is_array( $args['form_args'] ) ? $args['form_args'] : array();
      $actions_args    = isset( $args['actions_args'] ) && is_array( $args['actions_args'] ) ? $args['actions_args'] : array();

      if ( ! in_array( $message_tag, array( 'p', 'div' ), true ) ) {
         $message_tag = 'p';
      }

      if ( '' === $wrapper_style && function_exists( 'wpaf_get_confirm_layout_wrapper_style' ) ) {
         $wrapper_style = wpaf_get_confirm_layout_wrapper_style();
      }

      $wrapper_style_attr = $wrapper_style !== '' ? ' style="' . esc_attr( $wrapper_style ) . '"' : '';

      echo '<div class="' . esc_attr( $wrapper_class ) . '"' . $wrapper_style_attr . '>';

      if ( $message !== '' ) {
         echo '<' . $message_tag . ' class="' . esc_attr( $message_class ) . '">' . esc_html( $message ) . '</' . $message_tag . '>';
      }

      if ( $before_form_html !== '' ) {
         echo $before_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      wpaf_render_post_form_start( $form_args );
      wpaf_render_action_buttons( $actions_args );
      wpaf_render_form_end();

      if ( $after_form_html !== '' ) {
         echo $after_form_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</div>';
   }
}




//===========================================
// Побудувати args масив для універсального admin notice
//===========================================
if ( ! function_exists( 'wpaf_get_admin_notice_args' ) ) {
   /**
    * Build reusable args for wpaf_render_admin_notice().
    *
    * This helper stays render-only. Callers still own message texts,
    * branching, permissions, redirects, and all business semantics.
    *
    * Supported args:
    * - class
    * - message_tag
    *
    * @param string|mixed $type        Notice type.
    * @param bool|mixed   $dismissible Whether the notice is dismissible.
    * @param array        $args        Additional render options.
    *
    * @return array
    */
   function wpaf_get_admin_notice_args( $type = 'info', $dismissible = false, array $args = array() ) {
      return array(
         'type'        => is_scalar( $type ) ? sanitize_key( (string) $type ) : 'info',
         'dismissible' => ! empty( $dismissible ),
         'class'       => isset( $args['class'] ) ? (string) $args['class'] : '',
         'message_tag' => isset( $args['message_tag'] ) ? (string) $args['message_tag'] : 'p',
      );
   }
}


//===========================================
// Отримати конфігурацію notice за notice code із reusable map
//===========================================
if ( ! function_exists( 'wpaf_get_notice_entry_by_code' ) ) {
   /**
    * Resolve a reusable notice entry by notice code from a caller-owned map.
    *
    * This helper stays render-only. Callers still own notice codes, messages,
    * translations, and all business semantics. The helper only normalizes a
    * small shared map entry into a reusable notice config.
    *
    * Supported map entry formats:
    * - 'code' => 'Message text'
    * - 'code' => array(
    *     'message'     => 'Message text',
    *     'type'        => 'success|error|warning|info',
    *     'dismissible' => true,
    *     'class'       => '',
    *     'message_tag' => 'p',
    *   )
    *
    * Supported args:
    * - type
    * - dismissible
    * - class
    * - message_tag
    *
    * @param string|mixed $notice_code Notice code from the current request.
    * @param array        $notice_map  Caller-owned notice map.
    * @param array        $args        Default rendering options.
    *
    * @return array{
    *    message:string,
    *    notice_args:array
    * }|array{}
    */
   function wpaf_get_notice_entry_by_code( $notice_code, array $notice_map = array(), array $args = array() ) {
      $notice_code = is_scalar( $notice_code ) ? (string) $notice_code : '';

      if ( $notice_code === '' || ! isset( $notice_map[ $notice_code ] ) ) {
         return array();
      }

      $entry = $notice_map[ $notice_code ];

      if ( is_scalar( $entry ) ) {
         $message = (string) $entry;
         $entry   = array();
      } elseif ( is_array( $entry ) ) {
         $message = isset( $entry['message'] ) && is_scalar( $entry['message'] ) ? (string) $entry['message'] : '';
      } else {
         return array();
      }

      if ( $message === '' ) {
         return array();
      }

      $notice_args = wpaf_get_admin_notice_args(
         isset( $entry['type'] ) ? $entry['type'] : ( isset( $args['type'] ) ? $args['type'] : 'success' ),
         array_key_exists( 'dismissible', $entry ) ? ! empty( $entry['dismissible'] ) : ( isset( $args['dismissible'] ) ? ! empty( $args['dismissible'] ) : true ),
         array(
            'class'       => isset( $entry['class'] ) ? $entry['class'] : ( isset( $args['class'] ) ? $args['class'] : '' ),
            'message_tag' => isset( $entry['message_tag'] ) ? $entry['message_tag'] : ( isset( $args['message_tag'] ) ? $args['message_tag'] : 'p' ),
         )
      );

      return array(
         'message'     => $message,
         'notice_args' => $notice_args,
      );
   }
}

//===========================================
// Вивести універсальний admin notice за notice code із reusable map
//===========================================
if ( ! function_exists( 'wpaf_render_admin_notice_by_code' ) ) {
   /**
    * Render a standard admin notice by notice code through a reusable map.
    *
    * This helper keeps shared branching/render glue in one place while callers
    * continue to own the notice map, translations, routing, and all business
    * semantics.
    *
    * @param string|mixed $notice_code Notice code from the current request.
    * @param array        $notice_map  Caller-owned notice map.
    * @param array        $args        Default rendering options.
    *
    * @return bool True when a matching notice was rendered.
    */
   function wpaf_render_admin_notice_by_code( $notice_code, array $notice_map = array(), array $args = array() ) {
      $notice_entry = wpaf_get_notice_entry_by_code( $notice_code, $notice_map, $args );

      if ( empty( $notice_entry ) ) {
         return false;
      }

      wpaf_render_admin_notice(
         $notice_entry['message'],
         isset( $notice_entry['notice_args'] ) && is_array( $notice_entry['notice_args'] ) ? $notice_entry['notice_args'] : array()
      );

      return true;
   }
}

//===========================================
// Вивести універсальний admin notice для сторінок/форм у плагінах
//===========================================
if ( ! function_exists( 'wpaf_render_admin_notice' ) ) {
   /**
    * Render a standard WordPress-style admin notice.
    *
    * Supported args:
    * - type: success|error|warning|info
    * - dismissible
    * - class
    * - message_tag
    *
    * @param string|mixed $message Notice text.
    * @param array        $args    Notice rendering options.
    *
    * @return void
    */
   function wpaf_render_admin_notice( $message, array $args = array() ) {
      $message      = is_scalar( $message ) ? (string) $message : '';
      $type         = isset( $args['type'] ) ? sanitize_key( (string) $args['type'] ) : 'info';
      $dismissible  = ! empty( $args['dismissible'] );
      $class        = isset( $args['class'] ) ? (string) $args['class'] : '';
      $message_tag  = isset( $args['message_tag'] ) ? sanitize_key( (string) $args['message_tag'] ) : 'p';

      if ( $message === '' ) {
         return;
      }

      if ( ! in_array( $type, array( 'success', 'error', 'warning', 'info' ), true ) ) {
         $type = 'info';
      }

      if ( ! in_array( $message_tag, array( 'p', 'div' ), true ) ) {
         $message_tag = 'p';
      }

      $classes = array( 'notice', 'notice-' . $type );

      if ( $dismissible ) {
         $classes[] = 'is-dismissible';
      }

      if ( $class !== '' ) {
         $classes[] = $class;
      }

      echo '<div class="' . esc_attr( implode( ' ', $classes ) ) . '"><' . $message_tag . '>' . esc_html( $message ) . '</' . $message_tag . '></div>';
   }
}

//===========================================
// Вивести універсальний admin notice разом з action-links під повідомленням
//===========================================
if ( ! function_exists( 'wpaf_render_admin_notice_with_actions' ) ) {
   /**
    * Render a standard admin notice together with a compact action-link block.
    *
    * This helper stays render-only: callers still own message texts, URLs,
    * permissions, routing, and all business semantics. The helper only combines
    * shared notice rendering with shared notice/fallback action links.
    *
    * Supported args:
    * - notice_args (array)
    * - action_links_args (array)
    *
    * @param string|mixed $message Notice text.
    * @param array        $items   Action-link item definitions.
    * @param array        $args    Rendering options.
    *
    * @return void
    */
   function wpaf_render_admin_notice_with_actions( $message, array $items = array(), array $args = array() ) {
      $notice_args       = isset( $args['notice_args'] ) && is_array( $args['notice_args'] )
         ? $args['notice_args']
         : ( function_exists( 'wpaf_get_admin_notice_args' )
            ? wpaf_get_admin_notice_args(
               isset( $args['type'] ) ? $args['type'] : 'info',
               ! empty( $args['dismissible'] ),
               array(
                  'class'       => isset( $args['class'] ) ? $args['class'] : '',
                  'message_tag' => isset( $args['message_tag'] ) ? $args['message_tag'] : 'p',
               )
            )
            : array()
         );
      $action_links_args = isset( $args['action_links_args'] ) && is_array( $args['action_links_args'] ) ? $args['action_links_args'] : array();

      wpaf_render_admin_notice( $message, $notice_args );

      if ( empty( $items ) ) {
         return;
      }

      echo wpaf_get_notice_action_links_html( $items, $action_links_args );
   }
}



//===========================================
// Вивести subtitle-блоки для стандартних списків/журналів
//===========================================
if ( ! function_exists( 'wpaf_render_list_subtitles' ) ) {
   /**
    * Render shared subtitle lines for list-like admin screens.
    *
    * Supported args:
    * - plugin_name
    * - action
    * - search_results
    * - filter
    * - hide_filter_deletion_subtitle
    *
    * @param array $args Subtitle rendering options.
    *
    * @return void
    */
   function wpaf_render_list_subtitles( array $args = array() ) {
      $plugin_name                   = isset( $args['plugin_name'] ) ? (string) $args['plugin_name'] : 'wp-add-function';
      $action                        = isset( $args['action'] ) ? (string) $args['action'] : '';
      $search_results                = isset( $args['search_results'] ) ? (string) $args['search_results'] : '';
      $filter                        = isset( $args['filter'] ) ? $args['filter'] : '';
      $hide_filter_deletion_subtitle = ! empty( $args['hide_filter_deletion_subtitle'] );

      if ( 'filter-deletion' === $action && ! $hide_filter_deletion_subtitle ) {
         printf( '<span class="subtitle" style="color: #ce181e">' . __( 'Marked for deletion', $plugin_name ) . '</span>' );
      }

      if ( '' !== $search_results ) {
         /* translators: %s: search keywords */
         printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_results ) );
      }

      if ( ! empty( $filter ) ) {
         $filter_value = $filter;

         if ( function_exists( 'filter_str' ) ) {
            $filter_value = filter_str( $filter );
         } elseif ( is_array( $filter ) ) {
            $filter_value = implode( ', ', array_map( 'strval', $filter ) );
         }

         /* translators: %s: search keywords */
         printf( '<span class="subtitle" style="color: #336699;font-weight:bold">' . __( 'Filter by &#8220;%s&#8221;', $plugin_name ) . '</span>', esc_html( (string) $filter_value ) );
      }
   }
}


//===========================================
// Вивести універсальний search-row shell для стандартних списків/журналів
//===========================================
if ( ! function_exists( 'wpaf_render_list_search_row' ) ) {
   /**
    * Render a shared search row for list-like admin screens.
    *
    * Supported args:
    * - class_table
    * - search_box_name
    * - plugin_name
    * - wrapper_style
    * - period_wrapper_style
    *
    * @param array $args Search-row rendering options.
    *
    * @return void
    */
   function wpaf_render_list_search_row( array $args = array() ) {
      $class_table          = isset( $args['class_table'] ) ? $args['class_table'] : null;
      $search_box_name      = isset( $args['search_box_name'] ) ? (string) $args['search_box_name'] : '';
      $plugin_name          = isset( $args['plugin_name'] ) ? (string) $args['plugin_name'] : 'wp-add-function';
      $wrapper_style        = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : 'overflow:hidden; margin: 10px 0 4px;';
      $period_wrapper_style = isset( $args['period_wrapper_style'] ) ? (string) $args['period_wrapper_style'] : 'float:left; margin-right: 12px;';

      if ( ! is_object( $class_table ) || ! method_exists( $class_table, 'search_box' ) ) {
         return;
      }

      $has_period = method_exists( $class_table, 'render_period_controls' );

      if ( $has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
         $class_table->period_in_search_row = true;
      }

      echo '<div style="' . esc_attr( $wrapper_style ) . '">';

      if ( $has_period ) {
         echo '<div style="' . esc_attr( $period_wrapper_style ) . '">';
         $class_table->render_period_controls();
         echo '</div>';
      }

      $class_table->search_box( $search_box_name, $plugin_name );
      echo '</div>';
   }
}


//===========================================
// Вивести універсальний wrapper admin screen
//===========================================
if ( ! function_exists( 'wpaf_render_admin_wrap_start' ) ) {
   /**
    * Echo the opening wrapper for a standard WordPress admin screen.
    *
    * Supported args:
    * - class
    * - id
    *
    * @param array $args Wrapper options.
    *
    * @return void
    */
   function wpaf_render_admin_wrap_start( array $args = array() ) {
      $class = isset( $args['class'] ) ? trim( (string) $args['class'] ) : 'wrap';
      $id    = isset( $args['id'] ) ? (string) $args['id'] : '';
      $attrs = '';

      if ( $class !== '' ) {
         $attrs .= ' class="' . esc_attr( $class ) . '"';
      }

      if ( $id !== '' ) {
         $attrs .= ' id="' . esc_attr( $id ) . '"';
      }

      echo '<div' . $attrs . '>';
   }
}

if ( ! function_exists( 'wpaf_render_admin_wrap_end' ) ) {
   /**
    * Echo the closing wrapper for a standard WordPress admin screen.
    *
    * @return void
    */
   function wpaf_render_admin_wrap_end() {
      echo '</div>';
   }
}

//===========================================
// Побудувати args для універсальної шапки стандартного admin screen
//===========================================


//===========================================
// Підготувати args для універсального shell стандартного admin screen
//===========================================
if ( ! function_exists( 'wpaf_get_admin_screen_start_args' ) ) {
   /**
    * Build a reusable argument array for wpaf_render_admin_screen_start().
    *
    * This helper keeps the standard admin screen shell configuration in one
    * place so modules can pass only title text, header args, wrapper options,
    * and whether the standard display_message() block should be rendered.
    *
    * Supported args:
    * - header_args
    * - wrap_args
    * - render_messages
    *
    * @param string|mixed $title Screen title.
    * @param array        $args  Screen shell options.
    *
    * @return array
    */
   function wpaf_get_admin_screen_start_args( $title, array $args = array() ) {
      $screen_args = array(
         'title'           => is_scalar( $title ) ? (string) $title : '',
         'header_args'     => array(),
         'wrap_args'       => array(),
         'render_messages' => true,
      );

      if ( isset( $args['header_args'] ) && ! is_array( $args['header_args'] ) ) {
         $args['header_args'] = array();
      }

      if ( isset( $args['wrap_args'] ) && ! is_array( $args['wrap_args'] ) ) {
         $args['wrap_args'] = array();
      }

      return array_merge( $screen_args, $args );
   }
}

//===========================================
// Вивести початок універсального shell стандартного admin screen
//===========================================

if ( ! function_exists( 'wpaf_get_admin_screen_header_args_by_key' ) ) {
   /**
    * Build admin screen header args from a screen-key => metadata map.
    *
    * This keeps the shared map lookup and header-argument composition in one
    * place. Modules keep ownership of their screen map contents, while the
    * shared layer owns the standard shape and fallback defaults.
    *
    * Supported args:
    * - default_title
    * - default_description
    * - default_description2
    * - header_args
    *
    * Each map entry may contain:
    * - title
    * - description
    * - description1
    * - description2
    * - header_args
    *
    * @param string|mixed $screen_key  Screen key to resolve.
    * @param array        $screen_map  Map of screen metadata.
    * @param array        $args        Additional shared defaults/overrides.
    *
    * @return array
    */
   function wpaf_get_admin_screen_header_args_by_key( $screen_key, array $screen_map = array(), array $args = array() ) {
      $screen_key            = is_scalar( $screen_key ) ? sanitize_key( (string) $screen_key ) : '';
      $default_title         = isset( $args['default_title'] ) && is_scalar( $args['default_title'] ) ? (string) $args['default_title'] : '';
      $default_description   = isset( $args['default_description'] ) && is_scalar( $args['default_description'] ) ? (string) $args['default_description'] : '';
      $default_description2  = isset( $args['default_description2'] ) && is_scalar( $args['default_description2'] ) ? (string) $args['default_description2'] : '';
      $base_header_args      = isset( $args['header_args'] ) && is_array( $args['header_args'] ) ? $args['header_args'] : array();
      $entry                 = isset( $screen_map[ $screen_key ] ) && is_array( $screen_map[ $screen_key ] ) ? $screen_map[ $screen_key ] : array();
      $entry_header_args     = isset( $entry['header_args'] ) && is_array( $entry['header_args'] ) ? $entry['header_args'] : array();
      $title                 = isset( $entry['title'] ) && is_scalar( $entry['title'] ) ? (string) $entry['title'] : $default_title;
      $description1          = isset( $entry['description1'] ) && is_scalar( $entry['description1'] )
         ? (string) $entry['description1']
         : ( isset( $entry['description'] ) && is_scalar( $entry['description'] ) ? (string) $entry['description'] : $default_description );
      $description2          = isset( $entry['description2'] ) && is_scalar( $entry['description2'] ) ? (string) $entry['description2'] : $default_description2;
      $resolved_header_args  = array_merge( $base_header_args, $entry_header_args );

      if ( ! array_key_exists( 'description1', $resolved_header_args ) && '' !== $description1 ) {
         $resolved_header_args['description1'] = $description1;
      }

      if ( ! array_key_exists( 'description2', $resolved_header_args ) && '' !== $description2 ) {
         $resolved_header_args['description2'] = $description2;
      }

      return function_exists( 'wpaf_get_admin_screen_header_args' )
         ? wpaf_get_admin_screen_header_args( $title, $resolved_header_args )
         : array_merge(
            array(
               'title'        => $title,
               'title_tag'    => 'h2',
               'title_is_html'=> false,
               'show_icon'    => true,
               'picture_url'  => '',
               'description1' => isset( $resolved_header_args['description1'] ) ? (string) $resolved_header_args['description1'] : '',
               'description2' => isset( $resolved_header_args['description2'] ) ? (string) $resolved_header_args['description2'] : '',
               'intro_args'   => isset( $resolved_header_args['intro_args'] ) && is_array( $resolved_header_args['intro_args'] ) ? $resolved_header_args['intro_args'] : array(),
            ),
            $resolved_header_args
         );
   }
}

if ( ! function_exists( 'wpaf_get_admin_screen_start_args_by_key' ) ) {
   /**
    * Build admin screen start args from a screen-key => metadata map.
    *
    * Supported args:
    * - default_title
    * - default_description
    * - default_description2
    * - header_args
    * - wrap_args
    * - render_messages
    *
    * @param string|mixed $screen_key Screen key to resolve.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared shell options and defaults.
    *
    * @return array
    */
   function wpaf_get_admin_screen_start_args_by_key( $screen_key, array $screen_map = array(), array $args = array() ) {
      $header_args = function_exists( 'wpaf_get_admin_screen_header_args_by_key' )
         ? wpaf_get_admin_screen_header_args_by_key( $screen_key, $screen_map, $args )
         : array();
      $title       = isset( $header_args['title'] ) ? (string) $header_args['title'] : '';

      return function_exists( 'wpaf_get_admin_screen_start_args' )
         ? wpaf_get_admin_screen_start_args(
            $title,
            array(
               'header_args'     => $header_args,
               'wrap_args'       => isset( $args['wrap_args'] ) && is_array( $args['wrap_args'] ) ? $args['wrap_args'] : array(),
               'render_messages' => ! array_key_exists( 'render_messages', $args ) || ! empty( $args['render_messages'] ),
            )
         )
         : array(
            'title'           => $title,
            'header_args'     => $header_args,
            'wrap_args'       => isset( $args['wrap_args'] ) && is_array( $args['wrap_args'] ) ? $args['wrap_args'] : array(),
            'render_messages' => ! array_key_exists( 'render_messages', $args ) || ! empty( $args['render_messages'] ),
         );
   }
}

//===========================================
// Вивести початок універсального shell стандартного admin screen за screen key
//===========================================
if ( ! function_exists( 'wpaf_render_admin_screen_start_by_key' ) ) {
   /**
    * Render a standard admin screen shell from a screen-key => metadata map.
    *
    * Modules keep their own map with titles/descriptions, while the shared
    * layer resolves the map entry, composes header args, and renders the
    * common wrap + header + optional display_message() shell.
    *
    * Supported args:
    * - default_title
    * - default_description
    * - default_description2
    * - header_args
    * - wrap_args
    * - render_messages
    *
    * @param string|mixed $screen_key Screen key to resolve.
    * @param array        $screen_map Map of screen metadata.
    * @param array        $args       Shared shell options and defaults.
    *
    * @return void
    */
   function wpaf_render_admin_screen_start_by_key( $screen_key, array $screen_map = array(), array $args = array() ) {
      $screen_args = function_exists( 'wpaf_get_admin_screen_start_args_by_key' )
         ? wpaf_get_admin_screen_start_args_by_key( $screen_key, $screen_map, $args )
         : array(
            'title'           => '',
            'header_args'     => array(),
            'wrap_args'       => array(),
            'render_messages' => true,
         );

      $title       = isset( $screen_args['title'] ) ? (string) $screen_args['title'] : '';
      $header_args = isset( $screen_args['header_args'] ) && is_array( $screen_args['header_args'] ) ? $screen_args['header_args'] : array();

      if ( function_exists( 'wpaf_render_admin_screen_start' ) ) {
         wpaf_render_admin_screen_start( $title, $header_args, $screen_args );
         return;
      }

      wpaf_render_admin_wrap_start( isset( $screen_args['wrap_args'] ) && is_array( $screen_args['wrap_args'] ) ? $screen_args['wrap_args'] : array() );
      wpaf_render_admin_screen_header_with_fallback( $title, $header_args );

      if ( ( ! array_key_exists( 'render_messages', $screen_args ) || ! empty( $screen_args['render_messages'] ) ) && function_exists( 'display_message' ) ) {
         display_message();
      }
   }
}

if ( ! function_exists( 'wpaf_render_admin_screen_start' ) ) {
   /**
    * Render the opening shell of a standard WordPress admin screen.
    *
    * The helper owns only the shared wrapper/header/message bootstrap. The
    * caller still owns the actual title, descriptions, images, permissions,
    * and any module-specific content that follows the header block.
    *
    * Supported args:
    * - wrap_args
    * - render_messages
    *
    * Header args are passed directly to wpaf_render_admin_screen_header_with_fallback().
    *
    * @param string|mixed $title       Screen title.
    * @param array        $header_args Header rendering options.
    * @param array        $args        Screen shell options.
    *
    * @return void
    */
   function wpaf_render_admin_screen_start( $title, array $header_args = array(), array $args = array() ) {
      $screen_args = function_exists( 'wpaf_get_admin_screen_start_args' )
         ? wpaf_get_admin_screen_start_args(
            $title,
            array_merge(
               array(
                  'header_args' => $header_args,
               ),
               $args
            )
         )
         : array_merge(
            array(
               'title'           => is_scalar( $title ) ? (string) $title : '',
               'header_args'     => is_array( $header_args ) ? $header_args : array(),
               'wrap_args'       => array(),
               'render_messages' => true,
            ),
            $args
         );

      $plain_title     = isset( $screen_args['title'] ) ? $screen_args['title'] : '';
      $resolved_header = isset( $screen_args['header_args'] ) && is_array( $screen_args['header_args'] ) ? $screen_args['header_args'] : array();
      $wrap_args       = isset( $screen_args['wrap_args'] ) && is_array( $screen_args['wrap_args'] ) ? $screen_args['wrap_args'] : array();
      $render_messages = ! array_key_exists( 'render_messages', $screen_args ) || ! empty( $screen_args['render_messages'] );

      wpaf_render_admin_wrap_start( $wrap_args );
      wpaf_render_admin_screen_header_with_fallback( $plain_title, $resolved_header );

      if ( $render_messages && function_exists( 'display_message' ) ) {
         display_message();
      }
   }
}

//===========================================
// Завершити універсальний shell стандартного admin screen
//===========================================
if ( ! function_exists( 'wpaf_render_admin_screen_end' ) ) {
   /**
    * Render the closing shell of a standard WordPress admin screen.
    *
    * @param array $args Reserved for future shared wrapper options.
    *
    * @return void
    */
   function wpaf_render_admin_screen_end( array $args = array() ) {
      unset( $args );
      wpaf_render_admin_wrap_end();
   }
}

if ( ! function_exists( 'wpaf_get_admin_screen_header_args' ) ) {
   /**
    * Build a reusable argument array for wpaf_render_admin_screen_header().
    *
    * This helper keeps shared header-argument composition in one place so
    * modules can pass only their title, descriptions, picture URL, and small
    * layout overrides without duplicating the same array shape.
    *
    * Supported args:
    * - title_tag
    * - title_is_html
    * - show_icon
    * - picture_url
    * - description1
    * - description2
    * - intro_args
    *
    * @param string|mixed $title Header title.
    * @param array        $args  Additional header options.
    *
    * @return array
    */
   function wpaf_get_admin_screen_header_args( $title, array $args = array() ) {
      $header_args = array(
         'title'        => is_scalar( $title ) ? (string) $title : '',
         'title_tag'    => 'h2',
         'title_is_html'=> false,
         'show_icon'    => true,
         'picture_url'  => '',
         'description1' => '',
         'description2' => '',
         'intro_args'   => array(),
      );

      if ( isset( $args['intro_args'] ) && ! is_array( $args['intro_args'] ) ) {
         $args['intro_args'] = array();
      }

      return array_merge( $header_args, $args );
   }
}


//===========================================
// Вивести універсальну шапку admin screen з shared fallback-ланцюжком
//===========================================
if ( ! function_exists( 'wpaf_render_admin_screen_header_with_fallback' ) ) {
   /**
    * Render a standard admin screen header with shared modern/legacy fallback.
    *
    * This helper lets modules keep only title text, picture URL, descriptions,
    * and small header overrides while the shared layer owns the fallback chain:
    * modern wpaf header -> legacy html_title() -> raw h1/intro markup.
    *
    * Supported args:
    * - title_tag
    * - title_is_html
    * - show_icon
    * - picture_url
    * - description1
    * - description2
    * - intro_args
    *
    * @param string|mixed $title Screen title.
    * @param array        $args  Header rendering options.
    *
    * @return void
    */
   function wpaf_render_admin_screen_header_with_fallback( $title, array $args = array() ) {
      $header_args = function_exists( 'wpaf_get_admin_screen_header_args' )
         ? wpaf_get_admin_screen_header_args( $title, $args )
         : array_merge(
            array(
               'title'        => is_scalar( $title ) ? (string) $title : '',
               'title_tag'    => 'h2',
               'title_is_html'=> false,
               'show_icon'    => false,
               'picture_url'  => '',
               'description1' => '',
               'description2' => '',
               'intro_args'   => array(),
            ),
            $args
         );

      if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
         wpaf_render_admin_screen_header( $header_args );
         return;
      }

      $plain_title   = isset( $header_args['title'] ) && is_scalar( $header_args['title'] ) ? (string) $header_args['title'] : '';
      $picture_url   = isset( $header_args['picture_url'] ) ? (string) $header_args['picture_url'] : '';
      $description1  = isset( $header_args['description1'] ) ? (string) $header_args['description1'] : '';
      $description2  = isset( $header_args['description2'] ) ? (string) $header_args['description2'] : '';
      $intro_args    = isset( $header_args['intro_args'] ) && is_array( $header_args['intro_args'] ) ? $header_args['intro_args'] : array();
      $wrapper_style = isset( $intro_args['wrapper_style'] ) ? (string) $intro_args['wrapper_style'] : 'background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;';

      if ( function_exists( 'html_title' ) ) {
         html_title( $plain_title, $picture_url, $description1 );
         return;
      }

      echo '<h1>' . esc_html( $plain_title ) . '</h1>';

      if ( $picture_url === '' && $description1 === '' && $description2 === '' ) {
         return;
      }

      echo '<div style="' . esc_attr( $wrapper_style ) . '">';

      if ( $description1 !== '' ) {
         echo '<p>' . esc_html( $description1 ) . '</p>';
      }

      if ( $description2 !== '' ) {
         echo '<p>' . esc_html( $description2 ) . '</p>';
      }

      echo '</div>';
   }
}

//===========================================
// Вивести універсальну шапку стандартного admin screen
//===========================================
if ( ! function_exists( 'wpaf_render_admin_screen_header' ) ) {
   /**
    * Render a reusable standard admin screen header.
    *
    * This helper combines the legacy icon block, title, and optional intro box
    * into one render-only shell. The caller still owns the actual title text,
    * action links HTML, descriptions, image selection, and all business rules.
    *
    * Supported args:
    * - title
    * - title_tag
    * - title_is_html
    * - show_icon
    * - picture_url
    * - description1
    * - description2
    * - intro_args
    *
    * @param array $args Header rendering options.
    *
    * @return void
    */
   function wpaf_render_admin_screen_header( array $args = array() ) {
      $title         = isset( $args['title'] ) ? $args['title'] : '';
      $title_tag     = isset( $args['title_tag'] ) ? sanitize_key( (string) $args['title_tag'] ) : 'h2';
      $title_is_html = ! empty( $args['title_is_html'] );
      $show_icon     = ! array_key_exists( 'show_icon', $args ) || ! empty( $args['show_icon'] );
      $picture_url   = isset( $args['picture_url'] ) ? (string) $args['picture_url'] : '';
      $description1  = isset( $args['description1'] ) ? (string) $args['description1'] : '';
      $description2  = isset( $args['description2'] ) ? (string) $args['description2'] : '';
      $intro_args    = isset( $args['intro_args'] ) && is_array( $args['intro_args'] ) ? $args['intro_args'] : array();

      if ( ! in_array( $title_tag, array( 'h1', 'h2', 'h3' ), true ) ) {
         $title_tag = 'h2';
      }

      if ( $show_icon ) {
         echo '<div id="icon-users" class="icon32"><br/></div>';
      }

      echo '<' . $title_tag . '>';

      if ( $title_is_html ) {
         echo is_scalar( $title ) ? (string) $title : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      } else {
         echo esc_html( is_scalar( $title ) ? (string) $title : '' );
      }

      echo '</' . $title_tag . '>';

      if ( $picture_url === '' && $description1 === '' && $description2 === '' ) {
         return;
      }

      $intro_args = array_merge(
         array(
            'picture_url'  => $picture_url,
            'description1' => $description1,
            'description2' => $description2,
         ),
         $intro_args
      );

      wpaf_render_admin_intro_box( $intro_args );
   }
}

//===========================================
// Вивести універсальний intro-box для admin screen
//===========================================
if ( ! function_exists( 'wpaf_render_admin_intro_box' ) ) {
   /**
    * Render a reusable icon + description intro box for admin screens.
    *
    * This helper is intentionally render-only. The caller still owns the
    * title text, descriptions, image selection, permissions, and all business
    * semantics. Descriptions are rendered as-is to preserve the behavior of
    * existing screens that may pass translated text or lightweight HTML.
    *
    * Supported args:
    * - picture_url
    * - description1
    * - description2
    * - wrapper_style
    * - table_class
    * - picture_width
    * - picture_height
    *
    * @param array $args Intro-box options.
    *
    * @return void
    */
   function wpaf_render_admin_intro_box( array $args = array() ) {
      $picture_url    = isset( $args['picture_url'] ) ? (string) $args['picture_url'] : '';
      $description1   = isset( $args['description1'] ) ? (string) $args['description1'] : '';
      $description2   = isset( $args['description2'] ) ? (string) $args['description2'] : '';
      $wrapper_style  = isset( $args['wrapper_style'] ) ? (string) $args['wrapper_style'] : 'background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;';
      $table_class    = isset( $args['table_class'] ) ? (string) $args['table_class'] : 'wpuf-table';
      $picture_width  = isset( $args['picture_width'] ) ? (int) $args['picture_width'] : 48;
      $picture_height = isset( $args['picture_height'] ) ? (int) $args['picture_height'] : 48;

      echo '<div style="' . esc_attr( $wrapper_style ) . '">';
      echo '<p>';
      echo '<table class="' . esc_attr( $table_class ) . '">';
      echo '<th>';

      if ( $picture_url !== '' ) {
         echo '<img src="' . esc_url( $picture_url ) . '" name="picture_title" align="top" hspace="2" width="' . esc_attr( (string) $picture_width ) . '" height="' . esc_attr( (string) $picture_height ) . '" border="2"/>';
      }

      echo '</th>';
      echo '<td>';
      echo $description1; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

      if ( $description2 !== '' ) {
         echo '<p>' . $description2 . '</p>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
      }

      echo '</td>';
      echo '</table>';
      echo '</p>';
      echo '</div>';
   }
}


//===========================================
// Matrix/display utility helpers
//===========================================
if ( ! function_exists( 'wpaf_get_matrix_colored_text_html' ) ) {
   /**
    * Build Matrix-safe colored HTML text using data-mx-color.
    *
    * Inline CSS colors inside Matrix formatted_body are not reliable across
    * clients, so this helper uses the recommended data-mx-color attribute.
    *
    * @param string $text  Plain text content.
    * @param string $color Hex color in #RRGGBB format.
    *
    * @return string
    */
   function wpaf_get_matrix_colored_text_html( $text, $color ) {
      $text  = trim( wp_strip_all_tags( (string) $text ) );
      $color = strtoupper( trim( (string) $color ) );

      if ( $text === '' ) {
         return '';
      }

      if ( ! preg_match( '/^#[0-9A-F]{6}$/', $color ) ) {
         return '<strong>' . esc_html( $text ) . '</strong>';
      }

      return '<span data-mx-color="' . esc_attr( $color ) . '"><strong>' . esc_html( $text ) . '</strong></span>';
   }
}

if ( ! function_exists( 'wpaf_format_number_trimmed' ) ) {
   /**
    * Format a numeric value with locale support and trim trailing zeros.
    *
    * Examples: 5 -> 5, 5.500 -> 5.5, 5.125 -> 5.125.
    *
    * @param float|int|string $number
    * @param int              $decimals
    *
    * @return string
    */
   function wpaf_format_number_trimmed( $number, $decimals = 3 ) {
      $number   = (float) $number;
      $decimals = max( 0, (int) $decimals );

      if ( function_exists( 'number_format_i18n' ) ) {
         $formatted = number_format_i18n( $number, $decimals );
      } else {
         $formatted = number_format( $number, $decimals, '.', '' );
      }

      return rtrim( rtrim( (string) $formatted, '0' ), ',.' );
   }
}

//===========================================
// Надіслати універсальне Matrix-повідомлення через Client-Server API
//===========================================
if ( ! function_exists( 'wpaf_send_matrix_message' ) ) {
   /**
    * Send a Matrix room message through the Matrix Client-Server API.
    *
    * General transport-only helper. It does not know anything about module
    * business events, recipients, or templates.
    *
    * Supported args:
    * - homeserver (string) Base homeserver URL, for example https://matrix.example.com
    * - room_id (string)
    * - access_token (string)
    * - body (string)
    * - formatted_body (string)
    * - msgtype (string) Defaults to m.notice
    * - event_type (string) Defaults to m.room.message
    * - txn_id (string) Optional custom transaction id
    * - timeout (int) Optional request timeout in seconds
    * - thread_root_event_id (string) Optional Matrix root event id for m.thread
    *
    * @param array $args Matrix message arguments.
    *
    * @return array{success:bool,status_code:int,event_id:string,error_message:string,response_body:mixed}
    */
   function wpaf_send_matrix_message( array $args = array() ) {
      $homeserver     = isset( $args['homeserver'] ) ? untrailingslashit( esc_url_raw( (string) $args['homeserver'] ) ) : '';
      $room_id        = isset( $args['room_id'] ) ? trim( preg_replace( '/[\r\n]+/', '', (string) $args['room_id'] ) ) : '';
      $access_token   = isset( $args['access_token'] ) ? trim( preg_replace( '/[\r\n]+/', '', (string) $args['access_token'] ) ) : '';
      $body           = isset( $args['body'] ) ? trim( (string) $args['body'] ) : '';
      $formatted_body = isset( $args['formatted_body'] ) ? trim( (string) $args['formatted_body'] ) : '';
      $msgtype        = isset( $args['msgtype'] ) ? trim( (string) $args['msgtype'] ) : 'm.notice';
      $event_type     = isset( $args['event_type'] ) ? trim( (string) $args['event_type'] ) : 'm.room.message';
      $txn_id              = isset( $args['txn_id'] ) ? trim( (string) $args['txn_id'] ) : '';
      $timeout             = isset( $args['timeout'] ) ? (int) $args['timeout'] : 15;
      $thread_root_event_id = isset( $args['thread_root_event_id'] ) ? trim( preg_replace( '/[\r\n]+/', '', (string) $args['thread_root_event_id'] ) ) : '';

      if ( $homeserver === '' || $room_id === '' || $access_token === '' ) {
         return array(
            'success'       => false,
            'status_code'   => 0,
            'event_id'      => '',
            'error_message' => 'Missing Matrix connection settings.',
            'response_body' => null,
         );
      }

      if ( $body === '' && $formatted_body !== '' ) {
         $body = trim( wp_strip_all_tags( preg_replace( '/<br\s*\/?\>/i', "\n", $formatted_body ) ) );
      }

      if ( $body === '' ) {
         return array(
            'success'       => false,
            'status_code'   => 0,
            'event_id'      => '',
            'error_message' => 'Matrix message body is empty.',
            'response_body' => null,
         );
      }

      if ( $msgtype === '' ) {
         $msgtype = 'm.notice';
      }

      if ( $event_type === '' ) {
         $event_type = 'm.room.message';
      }

      if ( $txn_id === '' ) {
         if ( function_exists( 'wp_generate_uuid4' ) ) {
            $txn_id = wp_generate_uuid4();
         } else {
            $txn_id = md5( uniqid( 'matrix-', true ) );
         }
      }

      if ( $timeout <= 0 ) {
         $timeout = 15;
      }

      $url = $homeserver . '/_matrix/client/v3/rooms/' . rawurlencode( $room_id ) . '/send/' . rawurlencode( $event_type ) . '/' . rawurlencode( $txn_id );

      $content = array(
         'body'    => $body,
         'msgtype' => $msgtype,
      );

      if ( $formatted_body !== '' ) {
         $content['format']         = 'org.matrix.custom.html';
         $content['formatted_body'] = $formatted_body;
      }

      if ( $thread_root_event_id !== '' ) {
         $content['m.relates_to'] = array(
            'rel_type'        => 'm.thread',
            'event_id'        => $thread_root_event_id,
            'is_falling_back' => true,
            'm.in_reply_to'   => array(
               'event_id' => $thread_root_event_id,
            ),
         );
      }

      $response = wp_remote_request(
         $url,
         array(
            'method'  => 'PUT',
            'timeout' => $timeout,
            'headers' => array(
               'Authorization' => 'Bearer ' . $access_token,
               'Content-Type'  => 'application/json; charset=UTF-8',
               'Accept'        => 'application/json',
            ),
            'body'    => wp_json_encode( $content ),
         )
      );

      if ( is_wp_error( $response ) ) {
         return array(
            'success'       => false,
            'status_code'   => 0,
            'event_id'      => '',
            'error_message' => $response->get_error_message(),
            'response_body' => null,
         );
      }

      $status_code   = (int) wp_remote_retrieve_response_code( $response );
      $response_body = wp_remote_retrieve_body( $response );
      $decoded_body  = json_decode( (string) $response_body, true );
      $event_id      = '';
      $error_message = '';

      if ( is_array( $decoded_body ) ) {
         $event_id = isset( $decoded_body['event_id'] ) ? (string) $decoded_body['event_id'] : '';

         if ( isset( $decoded_body['error'] ) ) {
            $error_message = (string) $decoded_body['error'];
         } elseif ( isset( $decoded_body['errcode'] ) ) {
            $error_message = (string) $decoded_body['errcode'];
         }
      }

      if ( $error_message === '' && $status_code >= 400 ) {
         $error_message = 'Matrix API request failed.';
      }

      return array(
         'success'       => ( $status_code >= 200 && $status_code < 300 && $event_id !== '' ),
         'status_code'   => $status_code,
         'event_id'      => $event_id,
         'error_message' => $error_message,
         'response_body' => $decoded_body,
      );
   }
}

//===========================================
// Надіслати універсальний HTML email через wp_mail
//===========================================
if ( ! function_exists( 'wpaf_send_html_email' ) ) {
   /**
    * Send an HTML email with optional custom headers, attachments, and
    * transport-level threading metadata.
    *
    * General transport-only helper. It does not know anything about module
    * business events, recipients, or email templates.
    *
    * Supported args:
    * - to (string|array)
    * - subject (string)
    * - message (string)
    * - headers (array|string)
    * - attachments (array|string)
    * - message_id (string)
    * - in_reply_to (string)
    * - references (string|array)
    *
    * @param array $args Email arguments.
    *
    * @return bool
    */
   function wpaf_send_html_email( array $args = array() ) {
      $to          = isset( $args['to'] ) ? $args['to'] : '';
      $subject     = isset( $args['subject'] ) ? (string) $args['subject'] : '';
      $message     = isset( $args['message'] ) ? (string) $args['message'] : '';
      $headers     = isset( $args['headers'] ) ? $args['headers'] : array();
      $attachments = isset( $args['attachments'] ) ? $args['attachments'] : array();
      $message_id  = isset( $args['message_id'] ) ? trim( (string) $args['message_id'] ) : '';
      $in_reply_to = isset( $args['in_reply_to'] ) ? trim( (string) $args['in_reply_to'] ) : '';
      $references  = isset( $args['references'] ) ? $args['references'] : array();

      if ( is_array( $to ) ) {
         $to = array_values(
            array_filter(
               array_map( 'sanitize_email', $to )
            )
         );
      } else {
         $to = sanitize_email( (string) $to );
      }

      if ( empty( $to ) || $subject === '' || $message === '' ) {
         return false;
      }

      if ( ! is_array( $headers ) ) {
         $headers = array_filter( array_map( 'trim', explode( "\n", (string) $headers ) ) );
      }

      $filtered_headers = array();
      $has_content_type = false;

      foreach ( $headers as $header_line ) {
         if ( ! is_string( $header_line ) ) {
            continue;
         }

         $header_line = trim( $header_line );

         if ( $header_line === '' ) {
            continue;
         }

         if ( stripos( $header_line, 'Content-Type:' ) === 0 ) {
            $has_content_type = true;
         }

         if ( stripos( $header_line, 'Message-ID:' ) === 0 || stripos( $header_line, 'In-Reply-To:' ) === 0 || stripos( $header_line, 'References:' ) === 0 ) {
            continue;
         }

         $filtered_headers[] = $header_line;
      }

      $headers = $filtered_headers;

      if ( ! $has_content_type ) {
         $headers[] = 'Content-Type: text/html; charset=UTF-8';
      }

      if ( ! is_array( $attachments ) ) {
         $attachments = array_filter( array_map( 'trim', explode( "\n", (string) $attachments ) ) );
      }

      if ( ! is_array( $references ) ) {
         $references = array_filter( array_map( 'trim', preg_split( '/[\r\n,]+/', (string) $references ) ) );
      }

      $references = array_values(
         array_filter(
            array_map(
               static function( $value ) {
                  return trim( (string) $value );
               },
               $references
            )
         )
      );

      $phpmailer_init = null;

      if ( $message_id !== '' || $in_reply_to !== '' || ! empty( $references ) ) {
         $phpmailer_init = static function( $phpmailer ) use ( $message_id, $in_reply_to, $references ) {
            if ( $message_id !== '' ) {
               $phpmailer->MessageID = $message_id;
            }

            if ( $in_reply_to !== '' ) {
               $phpmailer->addCustomHeader( 'In-Reply-To', $in_reply_to );
            }

            if ( ! empty( $references ) ) {
               $phpmailer->addCustomHeader( 'References', implode( ' ', $references ) );
            }
         };

         add_action( 'phpmailer_init', $phpmailer_init );
      }

      try {
         $result = (bool) wp_mail( $to, $subject, $message, $headers, $attachments );
      } finally {
         if ( null !== $phpmailer_init ) {
            remove_action( 'phpmailer_init', $phpmailer_init );
         }
      }

      return $result;
   }
}

?>
