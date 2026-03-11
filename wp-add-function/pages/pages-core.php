<?php
// Базові функції для роботи зі сторінками

//===================================================
// У WordPress роботу із сесіями замінили на механізм https://developer.wordpress.org/apis/transients/

// Створюємо екземпляр для обробки помилок
global $form_message;
$form_message = new WP_Error;

// Змінні, що використовуються при роботі зі сторінками:
// $action - варіант відкритої форми (виконуваної дії):
//           new, new1        - форма створення нового елемента або документа
//           edit             - форма редагування вибраного елемента або документа
//           delete           - позначка на видалення вибраного елемента або документа
//           cancel-deletion  - скасування позначки на видалення вибраного елемента або документа
//           history          - форма відображення історії вибраного елемента або документа
//           filter-deletion  - форма для відображення позначених для видалення елементів або документів
//
// $page   - сторінка, яку потрібно відкрити
// $paged  - (paginated) номер сторінки пагінації $page
//
// $p (parent) - сторінка-батько, використовується для подальшого повернення
// $n (numbered) - це paged для $p (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
//
// $search_results (s) - відображення пошуку ? потрібно переглянути!!!
//
// $f (filter або field) - ім'я поля таблиці $page або кількох полів через роздільник |, для поля на формі або запиту фільтрації
// $v (filter value)     - значення для поля ($filter) або кількох полів через роздільник |, для поля на формі або запиту фільтрації

//===========================================
// Функція готує частину URL-запиту для передачі параметрів у відкриту форму сторінки (аналог http_build_query)
// parameters   - масив з параметрами та значеннями, що передаються.
//                приклад: array('id'=>1, 'name'=> 'test', 'description' => '')
// return_value - параметр або параметри, які форма (сторінка) має повернути (не обов'язково).
//                приклад: array('id', 'name')
// key_name     - ім'я масиву, яке потім можна відкрити за допомогою $_GET[$array_name] (не обов'язково, за замовчуванням field).
// first_char   - перший знак у запиті (за замовчуванням &)
function http_values_query( $parameters, $return_value = '', $key_name = 'field', $first_char = '&'){
  if ( ! empty( $return_value )){
    $data = array($key_name => $parameters, 'return_value' => $return_value);
  } else
    $data = array($key_name => $parameters);

  return empty(http_build_query($data)) ? '' : $first_char . http_build_query($data);
}

//===========================================
// Функція повертає масив значень, створених за допомогою http_values_query.
// field    - повернути певне значення (якщо не вказано, повертає всі значення у вигляді масиву)
// key_name - ім'я масиву, яке потім можна відкрити за допомогою $_GET[$key_name] (не обов'язково, за замовчуванням field).
function get_http_values( $field = '', $key_name = 'field'){
  $array_fields = isset( $_GET[$key_name] ) ? $_GET[$key_name] : array();
  // якщо потрібно повернути лише одне значення
  if ( ! empty( $field )){
    if ( array_key_exists( $field, $array_fields ))
      return $array_fields[$field];
    else
      return '';
  } else
    return $array_fields;
}

//=============================================
// Функція відображає масив фільтра у вигляді рядка
function filter_str( $array_filter ) {

  $str_filter = "";
  foreach ( $array_filter as $field => $value ) {
    if (! empty( $value )) {
      // якщо в імені поля перший знак *, то не використовуємо таблицю
      if ( $field[0] == "*"){
        if ( !empty($str_filter))
          $str_filter =  $str_filter . " ";
        $str_filter =  $str_filter . substr($field, 1 ) . " = " . $value;
        // якщо є крапка, значить з полем вказана таблиця
      } elseif ( strpos($field, ".") != false ){
        if ( !empty($str_filter))
          $str_filter =  $str_filter . " ";
        $str_filter =  $str_filter . $field . " = " . $value;
      } else {
        if ( !empty($str_filter))
          $str_filter =  $str_filter . " ";

        $str_filter = $str_filter . " " . $field . " = " . $value;
      }
    }
  }
  return $str_filter;
}

//===========================================
// Функція повертає значення, яке має повернути форма.
// Попередньо масив створюється за допомогою http_values_query.
function get_http_return_value(){
  $array_return_value = isset( $_GET["return_value"] ) ? $_GET["return_value"] : array();
  return $array_return_value;
}

//===========================================
// Запис налаштувань користувача на сторінці (кількість рядків на сторінці тощо.)
// Також зберігає кастомні прапорці Screen Options для приховування кнопок експорту.
add_filter( 'set-screen-option', function( $status, $option, $value ){

  // ------------------------------------------------------------
  // Screen Options: hide export buttons (CSV / HTML / PDF)
  // NOTE: WordPress core зберігає тільки wp_screen_options[option]/[value].
  // Наші чекбокси (ccards_hide_export_csv / ccards_hide_export_html / ccards_hide_export_pdf)
  // ядро не зберігає, тому робимо це тут — у тому ж циклі збереження Screen Options,
  // до редіректу.
  // ------------------------------------------------------------
  try {
    // Спрацьовує тільки при сабміті Screen Options.
    // $option тут — ключ user_meta, який ми задали в add_screen_option(... 'option' => ...).
    // Hidden marker додається через filter 'screen_settings' у pages-classes.php.
    if ( empty( $_POST['ccards_screen_export_flags'] ) ) {
      return $value;
    }

    if ( is_string( $option ) && strpos( $option, '_per_page' ) !== false ) {

      // Визначаємо базову частину ключів за патерном: <prefix>_<item>_per_page
      // Напр.: ccards_checks_data_per_page
      if ( preg_match( '/^([a-z0-9_-]+)_(.+)_per_page$/i', $option, $m ) ) {
        $prefix = $m[1];
        $item   = $m[2];

        $export_csv_meta_key  = $prefix . '_' . $item . '_hide_export_csv';
        $export_html_meta_key = $prefix . '_' . $item . '_hide_export_html';
        $export_pdf_meta_key  = $prefix . '_' . $item . '_hide_export_pdf';

        $hide_csv  = isset( $_POST['ccards_hide_export_csv'] ) ? 1 : 0;
        $hide_html = isset( $_POST['ccards_hide_export_html'] ) ? 1 : 0;
        $hide_pdf  = isset( $_POST['ccards_hide_export_pdf'] ) ? 1 : 0;

        $user_id = get_current_user_id();
        if ( $user_id ) {
          $r1 = update_user_meta( $user_id, $export_csv_meta_key,  $hide_csv );
          $r2 = update_user_meta( $user_id, $export_html_meta_key, $hide_html );
          $r3 = update_user_meta( $user_id, $export_pdf_meta_key,  $hide_pdf );

          error_log(
            '[CCARDS SCREEN OPTIONS] save export flags: option=' . $option
            . ' user_id=' . $user_id
            . ' csv_key=' . $export_csv_meta_key . ' csv=' . $hide_csv . ' result=' . print_r( $r1, true )
            . ' html_key=' . $export_html_meta_key . ' html=' . $hide_html . ' result=' . print_r( $r2, true )
            . ' pdf_key=' . $export_pdf_meta_key . ' pdf=' . $hide_pdf . ' result=' . print_r( $r3, true )
          );
        } else {
          error_log( '[CCARDS SCREEN OPTIONS] cannot save export flags: empty user_id; option=' . $option );
        }
      } else {
        // Якщо ключ не відповідає очікуваному патерну — лог для діагностики.
        if (
          isset( $_POST['ccards_hide_export_csv'] ) ||
          isset( $_POST['ccards_hide_export_html'] ) ||
          isset( $_POST['ccards_hide_export_pdf'] )
        ) {
          error_log( '[CCARDS SCREEN OPTIONS] export flags posted but cannot parse per_page option key: ' . $option );
        }
      }
    }
  } catch ( Throwable $e ) {
    error_log( '[CCARDS SCREEN OPTIONS] exception: ' . $e->getMessage() );
  }

  // Важливо: повертаємо $value, щоб WordPress коректно зберіг per_page
  // (та інші стандартні опції).
  return $value;
}, 10, 3 );

//====================================
// Функція відображення простої кнопки
function button_action( ?string $text = null, string $name = 'submit', string $type = 'submit' ){
  ?>
  <button type="<?php echo $type;?>" id="<?php echo $name;?>" name="<?php echo $name;?>" class="page-title-action button"><?php echo $text;?></button>
  <?php
}

/**
* Кнопка для відкриття експорту в новій вкладці (GET запит)
*/
function button_export_new_tab( ?string $text = null, string $format = 'html' ) {
  $url = wpaf_get_export_url_from_request( $format );
  $text = $text ?? ( $format === 'html' ? 'HTML' : ( $format === 'pdf' ? 'PDF' : 'CSV' ) );
  ?>
  <a href="<?php echo esc_url( $url ); ?>" target="_blank" class="page-title-action button"><?php echo esc_html( $text ); ?></a>
  <?php
}


/**
 * Функція для створення URL зі збереженням поточного стану таблиці
 * @param array $additional_params Додаткові параметри для URL
 * @param bool $include_filters Чи включати параметри фільтрації
 * @return string Побудований URL
 */
function get_stateful_table_url($additional_params = [], $include_filters = true) {
  $params = [];

  // Базові параметри
  if (isset($_REQUEST['page']) && $_REQUEST['page'] !== '') {
    $params['page'] = sanitize_key($_REQUEST['page']);
  }

  // Пагінація
  if (isset($_REQUEST['paged']) && $_REQUEST['paged'] > 1) {
    $params['paged'] = (int) $_REQUEST['paged'];
  }

  // 🔴 ВАЖЛИВО: Параметри сортування
  if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
    $params['orderby'] = sanitize_text_field($_REQUEST['orderby']);
  }
  if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
    $params['order'] = sanitize_text_field($_REQUEST['order']);
  }

  // Пошук
  if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
    $params['s'] = sanitize_text_field($_REQUEST['s']);
  }

  // Фільтри
  if ($include_filters) {
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

    // Інші формати фільтрів
    foreach ($_REQUEST as $key => $value) {
      if ((str_starts_with($key, 'filter-') || str_starts_with($key, 'field-')) && $value !== '') {
        $params[$key] = sanitize_text_field($value);
      }
    }
  }

  // Додаємо додаткові параметри
  if (!empty($additional_params)) {
    $params = array_merge($params, $additional_params);
  }

  return 'admin.php?' . http_build_query($params);
}

//====================================
// Відобразити кнопки на формі
// $buttons - задається у вигляді масиву, приклад: array('new' => 'New item', 'new1' => 'New item 1')
function display_form_buttons($buttons, $perm_button, $page ){

  $gl_ = gl_form_array::get();

  // Зафіксуємо поточний paged, (номер сторінки пагінації)
  $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

  // parent - сторінка-батько для подальшого повернення
  $parent    = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // це paged для $p (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  // 🔴 Створюємо URL зі збереженими параметрами
  $url_params = ['page' => $page];

  // Додаємо параметри пошуку, сортування, пагінації
  if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
    $url_params['s'] = urlencode($_REQUEST['s']);
  }
  if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
    $url_params['orderby'] = urlencode($_REQUEST['orderby']);
  }
  if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
    $url_params['order'] = urlencode($_REQUEST['order']);
  }
  if ($paged > 1) {
    $url_params['paged'] = $paged;
  }

  // 🔴 Додаємо параметри фільтрації
  if (isset($_REQUEST['f']) && is_array($_REQUEST['f'])) {
    foreach ($_REQUEST['f'] as $key => $value) {
      if ($value !== '') {
        $url_params['f[' . $key . ']'] = urlencode($value);
      }
    }
  }

  if (isset($_REQUEST['t']) && is_array($_REQUEST['t'])) {
    foreach ($_REQUEST['t'] as $key => $value) {
      if ($value !== '') {
        $url_params['t[' . $key . ']'] = urlencode($value);
      }
    }
  }

  $n = 0;
  if ( current_user_can( $perm_button )){
    foreach ($buttons as $button_action => $button_name) {
      $button_params = $url_params;
      $button_params['action'] = $button_action;
      $button_url = '?' . http_build_query($button_params);
      ?>
      <a href="<?php echo $button_url; ?>" class="page-title-action">
      <?php echo _e($button_name, $gl_['plugin_name'] );?>
      </a>
      <?php
      $n++;
    }
    // якщо пусте значення $buttons
    if (empty($buttons)){
      $button_action = 'new';
      $button_name   = __('New item', "wp-add-function" );
      $button_params = $url_params;
      $button_params['action'] = $button_action;
      $button_url = '?' . http_build_query($button_params);
      ?> <a href="<?php echo $button_url; ?>" class="page-title-action">
      <?php echo _e($button_name, $gl_['plugin_name'] );?>
      </a>
      <?php
    }
  }
  // якщо є сторінка-батько, виводимо кнопку для повернення
  if (! empty($parent)){
    $return_params = ['page' => $parent];
    if ($numbered > 1) {
      $return_params['paged'] = $numbered;
    }
    $return_url = '?' . http_build_query($return_params);
    ?> <a href="<?php echo $return_url; ?>" class="page-title-action">
    <?php echo _e( 'Return', 'wp-add-function' ); ?>
    </a>
    <?php
  }
}

//===================================================
// Функція для побудови посилань зі збереженням стану
//===================================================
function get_stateful_link( string $target_page, array $extra_params = [], string $current_page = '' ) {

  if (empty($current_page)) {
    $current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
  }

  $params = $extra_params;

  // Додаємо параметр для повернення
  $params['_return_page'] = $current_page;

  // Автоматично додаємо поточний стан
  $state_params = ['paged', 's', 'orderby', 'order'];

  foreach ($state_params as $param) {
    if (isset($_GET[$param]) && $_GET[$param] !== '') {
      $params[$param] = sanitize_text_field(wp_unslash($_GET[$param]));
    }
  }

  // Додаємо фільтри
  foreach ($_GET as $key => $value) {
    if (str_starts_with($key, 'filter-') && $value !== '') {
      $params[$key] = sanitize_text_field(wp_unslash($value));
    }
  }

  // Формуємо URL
  $url = admin_url('admin.php?page=' . $target_page);

  if (!empty($params)) {
    $url = add_query_arg($params, $url);
  }

  return $url;
}

//===================================================
// Функція для обробки повернення на сторінках форм
//===================================================
function handle_return_to_journal() {
  // Якщо є параметр повернення, перенаправляємо назад
  if (isset($_GET['_return_page']) && !empty($_GET['_return_page'])) {
    $return_page = sanitize_key($_GET['_return_page']);
    $return_params = [];

    // Збираємо всі параметри для повернення
    foreach ($_GET as $key => $value) {
      if ($key !== '_return_page' && $key !== 'page' && $key !== 'action') {
        $return_params[$key] = sanitize_text_field(wp_unslash($value));
      }
    }

    $return_url = admin_url('admin.php?page=' . $return_page);

    if (!empty($return_params)) {
      $return_url = add_query_arg($return_params, $return_url);
    }

    // Перенаправляємо при натисканні кнопки "Скасувати"
    if (isset($_POST['button_cancel'])) {
      wp_safe_redirect($return_url);
      exit;
    }
  }
}

// Додаємо обробку на admin_init
add_action('admin_init', 'handle_return_to_journal', 5);

/**
 * Функція для побудови URL зі збереженими параметрами таблиці
 * Використовується при переході з таблиці на форму редагування/створення
 */
function build_table_state_url($base_params = []) {
  $params = $base_params;

  // 🔴 Додаємо параметри пошуку
  if (isset($_REQUEST['s']) && $_REQUEST['s'] !== '') {
    $params['s'] = sanitize_text_field($_REQUEST['s']);
  }

  // 🔴 Додаємо сортування - ВАЖЛИВО: перевіряємо обидва варіанти
  if (isset($_REQUEST['orderby']) && $_REQUEST['orderby'] !== '') {
    $params['orderby'] = sanitize_text_field($_REQUEST['orderby']);
  }
  if (isset($_REQUEST['order']) && $_REQUEST['order'] !== '') {
    $params['order'] = sanitize_text_field($_REQUEST['order']);
  }

  // 🔴 Додаємо пагінацію
  if (isset($_REQUEST['paged']) && $_REQUEST['paged'] > 1) {
    $params['paged'] = (int) $_REQUEST['paged'];
  }

  // 🔴 Додаємо фільтри (якщо вони є)
  // Перевіряємо всі можливі варіанти фільтрів
  $filter_keys = ['f', 't', 'filter-', 'field-'];

  foreach ($_REQUEST as $key => $value) {
    // Пропускаємо порожні значення
    if ($value === '' || $value === null) {
      continue;
    }

    // Перевіряємо чи це параметр фільтра
    $is_filter_param = false;
    foreach ($filter_keys as $filter_key) {
      if (strpos($key, $filter_key) === 0) {
        $is_filter_param = true;
        break;
      }
    }

    if ($is_filter_param) {
      $params[$key] = sanitize_text_field($value);
    }
  }

  // 🔴 Додаємо параметри фільтрації у форматі масиву (якщо вони є)
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

  return 'admin.php?' . http_build_query($params);
}

/**
 * Функція для отримання URL повернення з форми
 * Використовується в формах редагування/створення для кнопки Cancel
 */
function get_return_url_from_form() {
  $page = $_REQUEST['page'] ?? '';
  $return_url = admin_url('admin.php?page=' . $page);

  // 🔴 Відновлюємо всі параметри з сесії або POST
  $params = [];

  if (isset($_REQUEST['p'])) {
    $params['p'] = $_REQUEST['p'];
  }
  if (isset($_REQUEST['n'])) {
    $params['n'] = $_REQUEST['n'];
  }

  // Додаємо параметри таблиці
  $table_params = ['s', 'orderby', 'order', 'paged'];
  foreach ($table_params as $param) {
    if (isset($_REQUEST[$param]) && $_REQUEST[$param] !== '') {
      $params[$param] = $_REQUEST[$param];
    }
  }

  // Додаємо фільтри
  foreach ($_REQUEST as $key => $value) {
    if (str_starts_with($key, 'f[') || str_starts_with($key, 't[')) {
      $params[$key] = $value;
    }
  }

  if (!empty($params)) {
    $return_url .= '&' . http_build_query($params);
  }

  return $return_url;
}

//===================================================
// Функція повертає вказане значення фільтра за name
function request_filter_value( $name ){
  // отримаємо значення фільтра (передається ім'я поля таблиці)
  $filter = isset( $_REQUEST['f'] ) ? wp_unslash( trim( $_REQUEST['f'] )) : '';

  // для фільтра отримаємо значення value
  $filter_value = isset( $_REQUEST['v'] ) ? wp_unslash( trim( $_REQUEST['v'] )) : '';

  // якщо використовується фільтр
  if ( ! empty( $filter ) and ! empty( $filter_value )){
    // перетворимо filter в масив
    $array_filter = explode( "|", $filter );
    $array_value  = explode( "|", $filter_value );
    foreach ( $array_filter as $index => $f ) {
      if ( ! empty( $f ) and ! empty( $array_value[$index] ) ) {
        if ( $f == $name )
          return $array_value[$index];
      }
    }
  }
  return "";
}

//===================================================
// Отримати ім'я сторінки без префікса
// якщо $prefix не вказано, сторінка повернеться з префіксом
function get_page_name( $prefix = '' ){
  $page = isset( $_REQUEST['page'] ) ? wp_unslash( trim( $_REQUEST['page'] )) : '';
  if (! empty( $prefix ))
    $page = str_replace( $prefix . '-', '', $page );
  return $page;
}

//===================================================
// Функція додає пункти меню в admin_bar
// $image - вказується відносно каталога плагінів
function add_admin_bar_menu($wp_admin_bar, $id, $image, $page, $nama_lang, $parent = '' ) {
  if ( $parent == '' ) {
    $wp_admin_bar -> add_menu( array(
      'id'    => $id,
      'title' => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
                                     'href'  => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
    ));
  } else {
    $wp_admin_bar -> add_menu( array(
      'parent' => $parent,           // параметр id з першого посилання
      'id'     => $id,               // свій id, щоб можна було додати дочірні посилання
      'title'  => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
                                     'href'   => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
    ));
  }
}
?>
