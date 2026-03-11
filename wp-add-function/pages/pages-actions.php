<?php
// Обробка POST-запитів та додаткові функції

//====================================
// Обробка дій POST форми
function post_form_actions() {
  // Виконуємо тільки в адмінці й тільки на POST-запити
  if ( ! is_admin() || $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
    return;
  }

  // Якщо немає жодної нашої кнопки — виходимо
  if (
    empty( $_POST['button_filter'] ) &&
    empty( $_POST['button_save'] ) &&
    empty( $_POST['button_new_save'] ) &&
    empty( $_POST['button_cancel'] ) &&
    empty( $_POST['button_delete'] ) &&
    empty( $_POST['button_apply'] ) &&
    empty( $_POST['button_period'] )
  ) {
    return;
  }

  // gl_ може бути потрібен нижче
  $gl_ = gl_form_array::get();

  // отримаємо $action
  $action = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] ) ) : '';

  // отримаємо поточну сторінку (разом з префіксом)
  $page  = get_page_name();
  // отримаємо номер сторінки пагінації
  $paged = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] ) ) : 1;

  // parent - сторінка батько для подальшого повернення
  $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] ) ) : '';

  // це paged для $parent (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $parent_n = isset( $_REQUEST['n'] ) ? max( 0, intval( $_REQUEST['n'] ) ) : 1;

  // 🔴 Функція для побудови URL повернення зі збереженими параметрами
  function build_return_url_with_state($page, $parent = '', $parent_n = 1) {
    $params = [];

    // Базові параметри
    if (!empty($parent)) {
      $params['page'] = $parent;
      $params['paged'] = $parent_n;
    } else {
      $params['page'] = $page;
    }

    // 🔴 Додаємо параметри пошуку
    if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
      $params['s'] = sanitize_text_field($_REQUEST['s']);
    }

    // 🔴 Додаємо сортування
    if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
      $params['orderby'] = sanitize_text_field($_REQUEST['orderby']);
    }
    if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
      $params['order'] = sanitize_text_field($_REQUEST['order']);
    }

    // 🔴 Додаємо пагінацію (тільки якщо не використовуємо parent)
    if (empty($parent) && isset($_REQUEST['paged']) && $_REQUEST['paged'] > 0) {
      $params['paged'] = (int) $_REQUEST['paged'];
    }

    // 🔴 Додаємо фільтри з URL (f[...], t[...])
    if (isset($_REQUEST['f']) && is_array($_REQUEST['f'])) {
      foreach ($_REQUEST['f'] as $key => $value) {
        if ($value !== '') {
          $params['f[' . $key . ']'] = sanitize_text_field($value);
        }
      }
    }

    if (isset($_REQUEST['t']) && is_array($_REQUEST['t'])) {
      foreach ($_REQUEST['t'] as $key => $value) {
        if ($value !== '') {
          $params['t[' . $key . ']'] = sanitize_text_field($value);
        }
      }
    }

    // 🔴 Додаємо фільтри з POST (filter-*, field-*)
    foreach ($_POST as $key => $value) {
      if ((str_starts_with($key, 'filter-') || str_starts_with($key, 'field-')) && $value !== '') {
        $params[$key] = sanitize_text_field($value);
      }
    }

    // 🔴 Додаємо дію якщо вона є
    if (isset($_REQUEST['a']) && $_REQUEST['a'] !== '') {
      $params['action'] = sanitize_text_field($_REQUEST['a']);
    }

    // 🔴 Додаємо значення повернення якщо є
    $fields_values = get_http_values();
    if (!empty($fields_values)) {
      foreach ($fields_values as $key => $value) {
        if ($value !== '') {
          $params['field[' . $key . ']'] = sanitize_text_field($value);
        }
      }
    }

    $url = admin_url('admin.php?' . http_build_query($params));
    error_log('[POST_FORM_ACTIONS] Built return URL: ' . $url);

    return $url;
  }

  // 🔴 обробимо натискання кнопки Filter для довідника
  if ( ! empty( $_POST['button_filter'] ) ) {
    // Заповнимо в масив дані значень полів форми
    list( $data_field, $data_table ) = post_array();

    // створимо частину посилання
    $link_field = http_values_query( $data_field, '', 'f' );
    $link_table = http_values_query( $data_table, '', 't' );

    // 🔴 Зберігаємо параметри пошуку та сортування
    $base_url = 'admin.php?page=' . $page;

    if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
      $base_url .= '&s=' . urlencode(sanitize_text_field($_REQUEST['s']));
    }
    if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
      $base_url .= '&orderby=' . urlencode(sanitize_text_field($_REQUEST['orderby']));
    }
    if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
      $base_url .= '&order=' . urlencode(sanitize_text_field($_REQUEST['order']));
    }
    if ($paged > 1) {
      $base_url .= '&paged=' . $paged;
    }

    wp_redirect( get_admin_url( null, $base_url . $link_field . $link_table ) );
    exit;
  }

  // 🔴 обробимо натискання кнопки Save (збереження редагування)
  if ( ! empty( $_POST['button_save'] ) ) {
    // Зберігаємо дані (помилки пишемо через add_message / WP_Error)
    save_edit_data();

    // 🔴 ВАЖЛИВО: тут НЕ викликаємо display_message() перед редіректом,
    // щоб не було виводу до заголовків.

    // 🔴 Будуємо URL повернення зі збереженими параметрами
    $return_url = build_return_url_with_state($page, $parent, $parent_n);

    wp_redirect( $return_url );
    exit;
  }

  // 🔴 обробимо натискання кнопки Save New (збереження нового)
  if ( ! empty( $_POST['button_new_save'] ) ) {

    // 🔴 Будуємо URL повернення зі збереженими параметрами
    $return_url = '';

    if ( empty( $parent ) ) {
      // Якщо немає батьківської сторінки, повертаємося на ту ж сторінку
      $return_url = build_return_url_with_state($page, '', $paged);
    } else {
      // Якщо є батьківська сторінка, повертаємося на неї
      $return_url = build_return_url_with_state($page, $parent, $parent_n);

      // 🔴 Додаємо дію якщо вона є
      if (isset($_REQUEST['a']) && $_REQUEST['a'] !== '') {
        $return_url .= (strpos($return_url, '?') !== false ? '&' : '?') .
        'action=' . urlencode(sanitize_text_field($_REQUEST['a']));
      }

      // 🔴 Додаємо значення для повернення
      $fields_values = get_http_values();
      $return_field = get_http_return_value();

      if ( ! empty( $return_field ) && ! empty( $gl_[ $return_field ] ) ) {
        $fields_values[ $return_field ] = $gl_[ $return_field ];
        $link_values = http_values_query( $fields_values );
        $return_url .= $link_values;
      } elseif ( ! empty( $fields_values ) ) {
        $link_values = http_values_query( $fields_values );
        $return_url .= $link_values;
      }
    }

    // Якщо збереження нового невдале — показуємо повідомлення і НЕ робимо редірект
    if ( function_exists( 'save_new_data' ) && save_new_data() != 1 ) {
      // Тут вже можна вивести повідомлення, тому що редіректа не буде
      display_message();
      return;
    }

    wp_redirect( $return_url );
    exit;
  }

  // 🔴 обробимо натискання кнопки Cancel (скасування)
  if ( ! empty( $_POST['button_cancel'] ) ) {
    // 🔴 Будуємо URL повернення зі збереженими параметрами
    $return_url = '';

    if ( empty( $parent ) ) {
      // Повертаємося на ту ж сторінку зі збереженими параметрами
      $return_url = build_return_url_with_state($page, '', $paged);
    } else {
      // Повертаємося на батьківську сторінку зі збереженими параметрами
      $return_url = build_return_url_with_state($page, $parent, $parent_n);

      // 🔴 Додаємо дію якщо вона є
      if (isset($_REQUEST['a']) && $_REQUEST['a'] !== '') {
        $return_url .= (strpos($return_url, '?') !== false ? '&' : '?') .
        'action=' . urlencode(sanitize_text_field($_REQUEST['a']));
      }

      // 🔴 Додаємо значення для повернення
      $fields_values = get_http_values();
      if (!empty($fields_values)) {
        $link_values = http_values_query($fields_values);
        $return_url .= $link_values;
      }
    }

    wp_redirect( $return_url );
    exit;
  }

  // 🔴 обробимо натискання кнопки Delete (видалення)
  if ( ! empty( $_POST['button_delete'] ) ) {
    delete_form_data();

    // 🔴 Будуємо URL повернення зі збереженими параметрами
    $return_url = build_return_url_with_state($page, $parent, empty($parent) ? $paged : $parent_n);

    wp_redirect( $return_url );
    exit;
  }

  // 🔴 Обробимо натискання кнопки Cancel Delete (скасування видалення)
  if ( ! empty( $_POST['button_apply'] ) ) {
    delete_form_data();

    // 🔴 Будуємо URL повернення зі збереженими параметрами
    $return_url = build_return_url_with_state($page, $parent, empty($parent) ? $paged : $parent_n);

    wp_redirect( $return_url );
    exit;
  }

  // 🔴 Обробимо натискання кнопки Apply (застосування періоду)
  if ( ! empty( $_POST['button_period'] ) ) {
    // 🔴 Період вже зберігається в State Manager
    // Просто перенаправляємо зі збереженими параметрами

    $return_url = build_return_url_with_state($page, $parent, empty($parent) ? $paged : $parent_n);

    wp_redirect( $return_url );
    exit;
  }

  // Якщо жоден редірект не був виконаний, можна безпечно вивести повідомлення
  display_message();
}

add_action( 'admin_init', 'post_form_actions' , 20);

//===========================================
// Функція перебирає всі поля на формі з префіксами і записує в два масиви для подальшого запису в базу даних
function post_array($prefix_field = 'field', $prefix_table = 'table'){
  $data_field = array();
  $data_table = array();
  foreach ($_POST as $key => $value) {
    if ( stristr($key, $prefix_field )) {
      $field = str_to_value($key, $prefix_field);
      $table = str_to_value($key, $prefix_table);
      $data_field[$field] = $value;
      if ( ! empty($table))
        $data_table[$field] = $table;
    }
  }
  return [$data_field, $data_table];
}

//===================================================
function post_get_str($par) {
  return isset( $_POST[ $par ] ) ? wp_unslash( trim( $_POST[ $par ] )) : '';
}

//===================================================
// Додати повідомлення або помилку для подальшого відображення за допомогою display_message
// $str_code - код помилки або повідомлення у вигляді рядка
// $view     - детальний опис повідомлення або помилки, для відображення на сторінці
// $type     - тип повідомлення, для помилок має бути error
// add_message('filed_to_get_data', __( "Failed to get data!", 'wp-add-function' ), 'error' );
function add_message( $str_code = '', $view = '', $type = '' ) {
  global $form_message;

  if ( !empty( $str_code ))
    $form_message -> add ($str_code, $view, $type );
}

//===================================================
// Вивести на сторінці повідомлення або помилку
// якщо в display_message передано $str_code, повідомлення або помилка буде відображена одразу ж
// $str_code - код помилки або повідомлення у вигляді рядка
// $view     - детальний опис повідомлення або помилки, для відображення на сторінці
// $type     - тип повідомлення, для помилок має бути error
// display_message ('filed_to_get_data', __( "Failed to get data!", 'wp-add-function' ), 'error' );
// display_message() - щоб відобразити всі помилки, які додані
function display_message( $str_code = '', $view = '', $type = '' ) {
  global $form_message;

  // якщо хочемо одразу додати повідомлення
  if ( ! empty( $str_code ) ) {
    add_message( $str_code, $view, $type );
  }

  // якщо немає повідомлень — виходимо
  if ( ! $form_message->get_error_code() ) {
    return;
  }

  // ВАЖЛИВО: НІЯКОГО exit, НІЯКОГО ВИВОДУ ПЕРЕД REDIRECT!!!
  // показуємо повідомлення лише коли сторінка повністю завантажена після redirect

  echo '<div class="notice notice-error is-dismissible" style="padding:12px;">';

  foreach ( $form_message->get_error_codes() as $code ) {
    $msg  = $form_message->get_error_message( $code );
    $type = $form_message->get_error_data( $code );

    if ( $type === 'error' ) {
      echo '<p><strong>' . esc_html( $msg ) . '</strong></p>';
    } else {
      echo '<p>' . esc_html( $msg ) . '</p>';
    }
  }

  echo '</div>';
}

//===================================================
// Виводить різні типи кнопок у полі для class-wp-list-table
function display_column_button( $this_table, $item, $column_name, $buttons, $name_id, $perm = '' ){
  global $color;

  $gl_ = gl_form_array::get();

  // Якщо дозвіл не вказано використовуємо read
  if ( empty ( $perm )) {
    $perm = 'read';
  }

  $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
  $actions = array();

  // Використовуємо функцію з pages-core.php для створення URL
  if (!function_exists('get_stateful_table_url')) {
    // Запасний варіант, якщо функція не визначена
    function fallback_table_url($params = []) {
      $base_params = ['page' => $_REQUEST['page'] ?? ''];

      if (isset($_REQUEST['paged']) && $_REQUEST['paged'] > 1) {
        $base_params['paged'] = (int) $_REQUEST['paged'];
      }
      if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
        $base_params['orderby'] = sanitize_text_field($_REQUEST['orderby']);
      }
      if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
        $base_params['order'] = sanitize_text_field($_REQUEST['order']);
      }
      if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
        $base_params['s'] = sanitize_text_field($_REQUEST['s']);
      }

      $all_params = array_merge($base_params, $params);
      return 'admin.php?' . http_build_query($all_params);
    }

    $get_url_func = 'fallback_table_url';
  } else {
    $get_url_func = 'get_stateful_table_url';
  }

  if ( count($buttons) > 0 ) {
    foreach ($buttons as $name ) {
      if ( $name == 'filter_s' ) {
        // Фільтр по значенню
        $filter_url = call_user_func($get_url_func, [
          's' => $item[$column_name],
          $name_id => !empty($item[$name_id]) ? $item[$name_id] : ''
        ]);
      } elseif ( $name == 'history' ) {
        // Історія
        $history_url = call_user_func($get_url_func, [
          'action' => 'history',
          'p' => $_REQUEST['page'] ?? '',
          'n' => isset($_REQUEST['paged']) ? $_REQUEST['paged'] : 1,
                                      $name_id => $item[$name_id]
        ]);
      } else {
        if ( current_user_can( $perm )) {
          // Інші дії
          $action_url = call_user_func($get_url_func, [
            'action' => $name,
            $name_id => $item[$name_id]
          ]);
        }
      }
    }
  }

  return sprintf('%1$s %2$s', $column_value, $this_table -> row_actions($actions) );
}

//===================================================
// Функція формує для class-wp-list-table у вказаному полі кнопку фільтр
function column_button_filter( $this_table, $item, $column_name, $column_db, $tables_db = array(), $page = '' ){
  global $color;

  // Сторінка-батько, використовується для подальшого повернення
  $parent = isset( $_REQUEST['p'] )    ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  if ( empty( $page )) {
    $page = $this_table -> page;
  }

  // Отримаємо фільтр, який уже використовується
  $old_filter = get_http_values( '', 'f');

  // Додамо вибране значення до вже існуючого фільтра
  if (is_array($column_db)) {
    foreach ( $column_db as $field => $table ) {
      $old_filter[$field] = $item[ $field ];
    }
  } else {
    $old_filter[$column_db] = $item[ $column_db ];
  }

  $filter = http_values_query( $old_filter, '', 'f');
  $filter_tables = http_values_query( $tables_db, '', 't');

  $column_value = '<font color="'. $color . '">' . $item[ $column_name ] . '</font>';
  $actions      = array( 'filter' => sprintf('<a href="?page=%s%s%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $page, $filter, $filter_tables));

  return sprintf('%1$s %2$s', $column_value, $this_table -> row_actions($actions) );
}

// Функція для відображення уніфікованих кнопок
function wpaf_button($args = array()) {
  $defaults = array(
    'text' => '',
    'url' => '#',
    'type' => 'default', // default, primary, secondary, danger
    'size' => 'default', // default, small, large
    'icon' => '',
    'class' => '',
    'id' => '',
    'onclick' => '',
    'target' => '',
    'disabled' => false,
  );

  $args = wp_parse_args($args, $defaults);

  $classes = array('wpuf-button');

  // Додаємо тип кнопки
  if ($args['type'] != 'default') {
    $classes[] = 'wpuf-button-' . $args['type'];
  }

  // Додаємо розмір кнопки
  if ($args['size'] != 'default') {
    $classes[] = 'wpuf-button-' . $args['size'];
  }

  // Додаємо додаткові класи
  if (!empty($args['class'])) {
    $classes[] = $args['class'];
  }

  // Формуємо атрибути
  $attributes = array();
  $attributes[] = 'href="' . esc_url($args['url']) . '"';
  $attributes[] = 'class="' . esc_attr(implode(' ', $classes)) . '"';

  if (!empty($args['id'])) {
    $attributes[] = 'id="' . esc_attr($args['id']) . '"';
  }

  if (!empty($args['onclick'])) {
    $attributes[] = 'onclick="' . esc_attr($args['onclick']) . '"';
  }

  if (!empty($args['target'])) {
    $attributes[] = 'target="' . esc_attr($args['target']) . '"';
  }

  if ($args['disabled']) {
    $attributes[] = 'disabled="disabled"';
  }

  // Формуємо вміст кнопки
  $content = esc_html($args['text']);
  if (!empty($args['icon'])) {
    $content = '<span class="dashicons dashicons-' . esc_attr($args['icon']) . '"></span> ' . $content;
  }

  echo '<a ' . implode(' ', $attributes) . '>' . $content . '</a>';
}

//===================================================
// Функція відображення поля default у class-wp-list-table
function display_column_default( $item, $column_name ){
  global $color, $color_all;

  $color_old = $color;

  if ( !empty( $item[ $column_name ])) {
    if ( $item[ $column_name ][0] == '-' ) {
      $color = $color_all['red'];
    }
  }

  if ( stripos ( $column_name, 'mail') != false ) {
    $column_value = ' <em><a href="mailto:' . $item[ $column_name ] . '"> '. $item[ $column_name ] . ' </a></em>';
  } else {
    $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
  }

  $color = $color_old;

  switch( $column_name ) {
    default:
      return $column_value;
      // case 'id':
      //    return print_r( $item, true );
  }
}

//===================================================
// Функція відображення поля class-wp-list-table у вигляді картинки
function display_column_picture( $item, $column_name, $picture ){
  $gl_ = gl_form_array::get();

  $column_value = '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $picture . '-16x16.png' . '"name="picture_title" align="top" hspace="2" width="16" height="16" border="2"/>';

  return $column_value;
}

//===========================================
// Додамо підтримку select2
add_action( 'admin_enqueue_scripts', function() {
  // Перевіряємо, чи ми в адмінці
  if (!is_admin()) {
    return;
  }

  //Our own JS file
  wp_register_script( 'select_search', WPMU_PLUGIN_URL . '/wp-add-function/js/jquery-3.5.1.js', array( 'jquery' ), 3.5, false );
  wp_enqueue_script( 'select_search' );

  //Select2 JS
  wp_register_script( 'select2_js', WPMU_PLUGIN_URL . '/wp-add-function/js/select2.js', array( 'jquery' ), 4.0, false );
  wp_enqueue_script( 'select2_js' );

  //Select2 CSS
  wp_register_style( 'select2_css', WPMU_PLUGIN_URL . '/wp-add-function/css/select2-mod.css' );
  wp_enqueue_style( 'select2_css' );

  // language select2
  $user_lang = substr(get_user_locale(),0, 2);
  wp_register_script( 'select2_lang', WPMU_PLUGIN_URL . '/wp-add-function/js/i18n/' . $user_lang .'.js' );
  wp_enqueue_script( 'select2_lang' );

}, -100 );

//=============================================
// Змінимо стиль адмінки
add_action( 'admin_head', function() {
  if (!is_admin()) {
    return;
  }

  // Спершу існуючі стилі WordPress
  echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/forms.css' . '">';
  echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/common.css' . '">';
  echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/buttons.css' . '">';

});

?>
