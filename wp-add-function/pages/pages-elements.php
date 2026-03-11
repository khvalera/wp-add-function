<?php
// HTML-елементи для форм (input, select, textarea тощо)

//===================================================
// Функція відображення кнопки
// display_name - Відображуване ім'я кнопки на формі
// link_page    - Посилання на сторінку
// style        - Стиль
// class        - Клас
function button_html($display_name, $link_page, $style = '', $class = 'page-title-action' ){
  ?>
  <a href="<?php echo $link_page;?>" class="<?php echo $class;?>">
  <?php echo $display_name;?>
  </a>
  <?php
}

//===================================================
// Служить для відображення одиночного поля input (якщо потрібно вивести кілька значень input використовуйте html_input_multi).
// $display_name  - Відображуване ім'я реквізиту на формі
// $type          - Тип реквізиту (number, text, date, time...)
// $name          - Ім'я input
// $value         - Значення
// $extra_options - Додаткові параметри, стиль тут теж вказується style="width:352px;"
// $onchange      - Назва функції, виконується після зміни значення елемента форми, коли ця зміна зафіксована.
// $field         - Якщо дорівнює true не використовувати field, приклад: date1 )
function html_input( $display_name, $type, $name, $value='', $extra_options = '', $onchange = '', $field = '' ) {
  if ( ! empty( $onchange ) ){
    $onchange = 'onchange="' . $onchange.'"';
    // Додамо javascript
    javascript_arithmetic_input();
  }
  ?>
  <tr class="rich-editing-wrap">
  <th scope="row"><?php echo $display_name; ?></th>
  <td>
  <?php

  if (! empty( $extra_options )) {
    // Якщо не знайдено style і size додамо style="width:350px; min-width: 100px;"
    if (( strrpos($extra_options, "style=") === false ) and (strrpos($extra_options, "size=") === false))
      $extra_options='style="width:350px; min-width: 100px;"' . $extra_options;
  } else
    // Якщо порожній extra_options використовуємо style="width:350px; min-width: 100px;"
    $extra_options='style="width:350px; min-width: 100px;"';

  // якщо $_field не дорівнює true
  if ( $field !== true ){
    // якщо $field не порожнє
    if ( ! empty( $field ))
      $name = $field . '-' . $name;
    else
      $name = 'field-' . $name;
  }

  // 🔴 Додаємо клас wpaf-input для уніфікації вигляду
  $extra_options = str_replace('class="', 'class="wpaf-input ', $extra_options);
  if (strpos($extra_options, 'class=') === false) {
    $extra_options = 'class="wpaf-input" ' . $extra_options;
  }
  ?>
  <input type="<?php echo $type ?>" name="<?php echo $name ?>" id="<?php echo $name ?>" value="<?php echo $value ?>" <?php echo $extra_options ?> <?php echo $onchange ?> >
  <?php
  ?>
  </td>
  </tr>
  <?php
}

//===================================================
// $display_name  - Відображуване ім'я реквізиту на формі, масив (можна вказувати кілька значень), приклад: array( date1 => "Період з:", date2 => "по:" )
// $type          - Тип реквізиту (number, text, date, time...), масив (можна вказувати кілька значень), приклад: array( date1 => "date", date2 => "date" )
// $value         - Значення (можна вказувати кілька, через |)
// $extra_options - Додаткові параметри, стиль тут теж вказується style="width:352px;" (можна вказувати кілька, через |)
// $onchange      - Назва функції, виконується після зміни значення елемента форми, коли ця зміна зафіксована.
// $field         - Якщо дорівнює true не використовувати field, приклад: array( date1 => false, date2 => true )
function html_input_multi( $display_name, $type, $value=array(), $extra_options = array(), $onchange = '', $field = array()) {

  // Перевіримо щоб передана кількість значень співпадала
  if ( count ( $display_name ) <> count ( $type ) or count ( $display_name ) <> count ( $type )) {
    display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'wp-add-function'  ), 'error');
  }
  if ( ! empty( $onchange ) ){
    $onchange = 'onchange="' . $onchange.'"';
    // Додамо javascript
    javascript_arithmetic_input();
  }
  ?>
  <tr class="rich-editing-wrap">
  <th scope="row"><?php echo $display_name[array_key_first($display_name)]; ?></th>
  <td>
  <?php
  $nom=0;
  foreach ($display_name as $key => $val) {
    $_name          = $key;
    $_display_name  = $val;
    $_type          = $type[$key];
    if (array_key_exists($key, $value))
      $_value = $value[$key];
    else
      $_value = '';
    if (array_key_exists($key, $extra_options))
      $_extra_options = $extra_options[$key];
    else
      $_extra_options = '';
    if (array_key_exists($key, $field))
      $_field = $field[$key];
    else
      $_field = false;
    if (! empty( $_extra_options )) {
      // Якщо не знайдено style і size додамо style="width:350px; min-width: 100px;"
      if (( strrpos($_extra_options, "style=") === false ) and (strrpos($_extra_options, "size=") === false))
        $_extra_options='style="width:350px; min-width: 100px;"' . $_extra_options;
    } else
      // Якщо порожній extra_options використовуємо style="width:350px; min-width: 100px;"
      $_extra_options='style="width:350px; min-width: 100px;"';

    // якщо $_field не дорівнює true
    if ( $_field !== true ){
      // якщо $field не порожнє
      if ( ! empty( $_field ))
        $_name = $_field . '-' . $_name;
      else
        $_name = 'field-' . $_name;
    }
    if ( $nom == 0 ){
      ?>
      <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> <?php echo $onchange ?> >
      <?php
    } else{
      ?> <span style="font-weight: normal"><?php echo $_display_name ?></span>
      <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> <?php echo $onchange ?> >
      <?php
    }
    $nom++;
  }
  ?>
  </td>
  </tr>
  <?php
}

//===================================================
// sign - знак *, - , + тощо.
function javascript_arithmetic_input(){
  ?>
  <script type="text/javascript">
  function arithmetic_input(input1_name, input2_name, input3_name, sign_str ){
    var n1 = document.getElementById(input1_name).value;
    var n2 = document.getElementById(input2_name).value;
    var n3 = eval(n1 + sign_str + n2);
    document.getElementById(input3_name).value = n3.toFixed(3);
  }
  </script>
  <?php
}

//===================================================
// Багаторядковий текст
// $display_name  - Відображуване ім'я реквізиту на формі
// $name          - Ім'я поля, призначене для того, щоб обробник форми міг його ідентифікувати.
// $cols          - Ширина поля в символах.
// $rows          - Висота поля в рядках тексту.
// $value         - Значення
function html_textarea( $display_name, $name, $cols = '', $rows = '', $value='' ) {
  ?>
  <tr class="rich-editing-wrap">
  <th scope="row"><?php echo $display_name; ?></th>
  <td>
  <?php
  // Додамо 'field-' якщо в імені його немає
  if ( strpos( $name, 'field-' ) === false )
    $name = 'field-' . $name;
  ?>
  <textarea name="<?php echo $name ?>" id="<?php echo $name ?>" class="wpaf-textarea" cols="<?php echo $cols ?>" rows="<?php echo $rows ?>"><?php echo $value ?></textarea>
  <?php
  ?>
  </td>
  </tr>
  <?php
}

//===================================================
// $display_name  - відображуване ім'я реквізиту
// $name          - ім'я (для авто збереження має відповідати назві поля в таблиці)
// $array_data    - масив даних
// $value_id      - id обраної позиції
// $value_name    - ім'я обраної позиції
// $extra_options - додаткові параметри (стиль тут теж вказується style="width:352px;")
// $not_field     - якщо дорівнює true не використовувати field
function html_select($display_name, $name, $array_data, $extra_options = '', $value_id = '', $id_field = '', $value_field = '', $not_field = '' ){
  // Додамо 'field-' якщо в імені його немає
  if ( $not_field != true )
    if ( strpos( $name, 'field-' ) === false )
      $name = 'field-' . $name;
  // якщо стиль не вказано використовуємо width:352px;
  if ( stripos($extra_options, 'style') == false )
    $extra_options = $extra_options . ' style="width:352px;" ';

  // Додаємо клас wpaf-select для уніфікації вигляду
  $extra_options = str_replace('class="', 'class="wpaf-select ', $extra_options);
  if (strpos($extra_options, 'class=') === false) {
    $extra_options = 'class="wpaf-select" ' . $extra_options;
  }
  ?>
  <tr class="rich-editing-wrap">
  <th scope="row"><?php echo $display_name; ?></th>
  <td>
  <select name="<?php echo $name ?>" id="<?php echo $name ?>" <?php echo $extra_options; ?> >
  <option value="">-<?php _e('Select value', 'wp-add-function' ); ?>-</option>
  <?php
  if ( $array_data ) {
    foreach ( $array_data as $in ) {
      $selected = "";
      if ( $value_id == ($in -> $id_field ))
        $selected = "selected";
      ?><option <?php echo $selected ?> value="<?php echo $in -> $id_field ?>"><?php echo $in -> $value_field ?></option> <?php
    }
  }
  ?>
  </select>
  </td>
  </tr>
  <?php
}

//===================================================
function html_title($title, $picture, $description1 = '', $description2 = '' ){
  ?>
  <h2>
  <?php echo $title; ?>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . plugins_url( $picture ) . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php
  echo $description1;
  if ( ! empty( $description2 ))
    echo '<p>' . $description2 . '</p>' ;
  ?>
  </td>
  </table>
  </p>
  </div>
  <?php
}

//===================================================
// Опис параметрів $args:
// plugin_name   - ім'я плагіна (обов'язково)
// display_name  - ім'я реквізиту, що відображається
// table_name    - ім'я таблиці бази даних
// name          - ім'я об'єкта, рядок або масив, що складається з імені поля та таблиці бази даних. приклад: array("objectId", "table")
// extra_options - додаткові параметри (тут також вказується стиль: style="width:352px;")
// select_id     - id обраної позиції
// select_name   - рядок або масив з іменами полів із таблиці бази даних для додавання як name (за замовчуванням name). приклад: array("objectId", "holderName")
// php_file      - шлях до файлу ajax (не обов'язково)
// if_select     - ім'я поля для відбору, якщо не вказано, використовується objectId
// params        - параметри для передачі до ajax_php (наприклад: "?f=objectId&v=1")
// name_function - якщо потрібно вказати свою функцію
// prefix        - префікс для імені поля: 'field' (для форм) або 'filter' (для журналів) (за замовчуванням 'field')
// not_field     - якщо true, не додавати префікс до імені
function html_select2( array $args ) {
  $defaults = [
    'plugin_name'   => '',
    'display_name'  => '',
    'table_name'    => '',
    'name'          => '',
    'extra_options' => '',
    'select_id'     => '',
    'select_name'   => '',
    'php_file'      => '',
    'if_select'     => '',
    'params'        => '',
    'name_function' => null,
    'not_field'     => false,
    'prefix'        => 'field',
  ];

  $args = wp_parse_args( $args, $defaults );

  if ( empty( $args['plugin_name'] ) || empty( $args['table_name'] ) || empty( $args['name'] ) ) {
    return;
  }

  // --- формування item_name з урахуванням префікса ---
  $item_name = '';

  if ( is_array( $args['name'] ) ) {
    // Якщо name передано як масив [field_name, table_name]
    $item_name = array_to_string( [
      $args['prefix'] => $args['name'][0],
      'table' => $args['name'][1] ?? null,
    ] );
  } elseif ( $args['not_field'] ) {
    // Якщо not_field = true, не додаємо префікс
    $item_name = array_to_string( [ 'not_field' => $args['name'] ] );
  } else {
    // Якщо name - простий рядок
    $has_field_prefix = strpos( $args['name'], 'field-' ) === 0;
    $has_filter_prefix = strpos( $args['name'], 'filter-' ) === 0;

    if ( $has_field_prefix || $has_filter_prefix ) {
      $item_name = $args['name'];
    } else {
      $item_name = array_to_string( [ $args['prefix'] => $args['name'] ] );
    }
  }

  // Для журналів (filter- префікс)
  if ($args['prefix'] === 'filter' && strpos($item_name, 'filter-') !== 0) {
    $item_name = str_replace('field-', 'filter-', $item_name);
  }

  // --- завантаження даних для select_id ---
  $data = [];
  $select_name = '';

  if ( ! empty( $args['select_id'] ) && $args['select_id'] !== '' && $args['select_id'] !== '0' ) {
    if ( ! empty( $args['name_function'] ) && is_callable( $args['name_function'] ) ) {
      $data = call_user_func(
        $args['name_function'],
        $args['table_name'],
        ARRAY_A,
        $args['select_id'],
        $args['if_select']
      );
    } else {
      $data = get_row_table_id(
        $args['table_name'],
        ARRAY_A,
        $args['select_id']
      );
    }
  }

  // Формування тексту для відображення
  if ( ! empty( $data ) ) {
    if ( empty( $args['select_name'] ) ) {
      $select_name = $data['name'] ?? '';
    } elseif ( is_array( $args['select_name'] ) ) {
      $parts = [];
      foreach ( $args['select_name'] as $n ) {
        if ( ! empty( $data[ $n ] ) ) {
          $parts[] = $data[ $n ];
        }
      }
      $select_name = implode( ' - ', $parts );
    } else {
      $select_name = $data[ $args['select_name'] ] ?? '';
    }
  }

  // Визначаємо поле ID
  $name_id = ! empty( $data['objectId'] ) ? 'objectId' : 'id';
  if (!empty($data) && !isset($data[$name_id])) {
    $name_id = 'id';
  }

  // --- ajax файл ---
  $php_file = $args['php_file'] ?: 'includes/' . $args['table_name'] . '/ajax.php';
  $path = WP_PLUGIN_DIR . '/' . $args['plugin_name'] . '/' . $php_file;
  $url = plugins_url(
    $php_file,
    WP_PLUGIN_DIR . '/' . $args['plugin_name'] . '/' . $args['plugin_name'] . '.php'
  );

  if ( ! file_exists( $path ) ) {
    display_message(
      'file_not_found',
      sprintf( __( 'File not found "%s"', 'wp-add-function' ), $php_file ),
                    'error'
    );
    $ajax_php = '';
  } else {
    $ajax_php = $url;
  }

  // Додаємо параметри до AJAX URL
  if (!empty($args['params'])) {
    $ajax_php .= $args['params'];
  }

  // Додаємо клас wpaf-select2 для уніфікованих стилів
  $extra_options = $args['extra_options'];
  $extra_options = str_replace('class="', 'class="wpaf-select2 ', $extra_options);
  if (stripos($extra_options, 'style') === false) {
    $extra_options = $extra_options . ' style="width:352px;" ';
  }
  if (strpos($extra_options, 'class=') === false) {
    $extra_options = 'class="wpaf-select2" ' . $extra_options;
  }
  ?>
  <tr class="rich-editing-wrap">
  <th scope="row"><?php echo esc_html( $args['display_name'] ); ?></th>
  <td>
  <select
  class="item_<?php echo esc_attr( $item_name ); ?> wpaf-select2"
  name="<?php echo esc_attr( $item_name ); ?>"
  data-selected="<?php echo esc_attr( $args['select_id'] ); ?>"
  <?php echo $extra_options; ?>
  >
  <?php
  // Якщо є дані — одразу ставимо selected-опцію
  if ( ! empty( $data ) && isset($data[$name_id]) ) :
    $selected_id = $data[$name_id];
  $display_text = $select_name ?: $selected_id;
  ?>
  <option selected value="<?php echo esc_attr( $selected_id ); ?>">
  <?php echo esc_html( $display_text ); ?>
  </option>
  <?php elseif ( ! empty( $args['select_id'] ) && $args['select_id'] !== '' ) : ?>
  <!-- Якщо є select_id, але даних не завантажено -->
  <option selected value="<?php echo esc_attr( $args['select_id'] ); ?>">
  <?php echo esc_html( sprintf( __( 'Loading ID %s...', 'wp-add-function' ), $args['select_id'] ) ); ?>
  </option>
  <?php endif; ?>
  </select>
  </td>
  </tr>
  <?php

  // Перевіряємо, чи потрібно передавати параметри до AJAX
  $ajax_params = $args['params'] ?? '';

  // Для журналів додаємо параметр для фільтрації по firmId
  if ($args['prefix'] === 'filter' && empty($ajax_params)) {
    $ajax_params = '';
  }

  java_item( $item_name, esc_url( $ajax_php ), $ajax_params );
}

//===================================================
// JS-частина html_select2
//===================================================
function java_item( $item_name, $ajax_php = '', $params = '' ) {
  $place_item = __( 'Select value', 'wp-add-function' );
  $class_name = '.item_' . $item_name;

  // Формуємо повний URL для AJAX
  $full_ajax_url = $ajax_php;
  if (!empty($params)) {
    if (strpos($full_ajax_url, '?') !== false) {
      $full_ajax_url .= '&' . ltrim($params, '?&');
    } else {
      $full_ajax_url .= '?' . ltrim($params, '?');
    }
  }
  ?>
  <script type="text/javascript">
  jQuery(function($) {
    const class_name = '<?php echo $class_name; ?>';
  const place_item = '<?php echo $place_item; ?>';
  const ajax_php   = '<?php echo $full_ajax_url; ?>';
  const $selectEl = $(class_name);

  if (!$selectEl.length) {
    return;
  }

  // Отримуємо selected ID з data-атрибута
  let selectedId = $selectEl.data('selected');

  if (!ajax_php || ajax_php === '') {
    // Ініціалізувати Select2 без AJAX
    $selectEl.select2({
      placeholder: {
        id: '-1',
        text: place_item
      },
      allowClear: true
    });
    return;
  }

  // Ініціалізація Select2 з AJAX-пошуком
  $selectEl.select2({
    placeholder: {
      id: '-1',
      text: place_item
    },
    allowClear: true,
    ajax: {
      url: ajax_php,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term || '',
          page: params.page || 1,
          _ajax_nonce: '<?php echo wp_create_nonce("select2_search"); ?>'
        };
      },
      processResults: function (data, params) {
        params.page = params.page || 1;
        return {
          results: data || [],
          pagination: {
            more: (data && data.length >= 30)
          }
        };
      },
      cache: true
    },
    minimumInputLength: 0
  });

  // Автозаповнення SELECTED VALUE при завантаженні
  if (selectedId && selectedId !== '' && selectedId !== '0' && selectedId !== '-1') {
    var existingOption = $selectEl.find('option[value="' + selectedId + '"]');
    if (existingOption.length > 0) {
      $selectEl.val(selectedId).trigger('change');
    } else {
      // Якщо опції немає, завантажуємо через AJAX
      $.ajax({
        url: ajax_php,
        data: {
          id: selectedId,
          _ajax_nonce: '<?php echo wp_create_nonce("select2_search"); ?>'
        },
        dataType: 'json',
        success: function (data) {
          if (data && data.id) {
            var option = new Option(data.text, data.id, true, true);
            $selectEl.append(option).trigger('change');
          }
        }
      });
    }
  }
  });
  </script>
  <?php
}

//===========================================
// Функція повертає значення параметра закодованого array_to_string
// $str   - рядок у якому шукаємо
// $param - параметр який шукаємо
function str_to_value($str, $param){
  foreach (explode('__', $str) as $chunk) {
    $arr = explode("-", $chunk);
    if ($arr) {
      if ( $arr[0] == $param)
        return $arr[1];
    }
  }
  return "";
}

//===================================================
// Перетворення масиву у строку (для унікального класу)
function array_to_string($arr) {
  return implode('__', array_map(
    fn($k, $v) => "$k-$v",
                                 array_keys($arr),
                                 $arr
  ));
}

//===================================================
// Функція для рендерингу контейнера форми
function wpuf_form_start() {
  echo '<div class="wpuf-form-container">';
}

//===================================================
function wpuf_form_end() {
  echo '</div>';
}

?>
