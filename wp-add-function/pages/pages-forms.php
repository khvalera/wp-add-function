<?php
// Функції для рендерингу форм (списки, журнали, довідники)

//====================================
// Форма звіту
// $name            - Ім'я форми (приклад: balances)
// $title           - Заголовок
// $description1    - Опис 1
// $description2    - Опис 2
// $search_box_name - Ім'я кнопки пошуку
function form_report( $name, $title, $description1, $description2 = '', $search_box_name = '' ) {

  $gl_ = gl_form_array::get();
  //print_r($gl_); exit;

  if ( $search_box_name == '' ) {
    $search_box_name = __( "Search", "wp-add-function" );
  }

  $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';
  $page   = get_page_name();

  $class_table = $gl_['class-table'];
  $class_table -> prepare_items();
  ?>
  <div class="wrap">
  <div id="icon-users" class="icon32"><br/></div>
  <h2>
  <?php echo $title; ?>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png' . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php echo $description1; ?>
  <?php
  if ( ! empty( $description2 ))
    echo '<p>' . $description2 . '</p>' ;
  ?>
  </td>
  </table>
  </p>
  </div>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( $action == 'filter-deletion' ){
    printf( '<span class="subtitle" style="color: #ce181e">' . __( 'Marked for deletion' , $gl_['plugin_name']) . '</span>' );
  }
  if ( strlen( $search_results )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_results ) );
  }
  // якщо використовується фільтр
  if ( ! empty( $class_table -> filter )){
    $filter_str = filter_str( $class_table -> filter );
    /* translators: %s: search keywords */
    printf( '<span class="subtitle" style="color: #336699;font-weight:bold">' . __( 'Filter by &#8220;%s&#8221;', $gl_['plugin_name'] ) . '</span>', esc_html( $filter_str ) );
  }
  ?>
  <?php
  $ccards_has_period = method_exists( $class_table, 'render_period_controls' );
  if ( $ccards_has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
    $class_table->period_in_search_row = true;
  }
  ?>
  <div style="overflow:hidden; margin: 10px 0 4px;">
  <?php if ( $ccards_has_period ) : ?>
  <div style="float:left; margin-right: 12px;">
  <?php $class_table->render_period_controls(); ?>
  </div>
  <?php endif; ?>

  <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
  </div>
  <?php $class_table -> display() ?>
  </form>
  </p>
  </div>
  <?php
}

//====================================
// Форма журналу документів
// $name            - Ім'я форми (приклад: journal)
// $perm_button     - Права на кнопки
// $title           - Заголовок
// $description1    - Опис 1
// $description2    - Опис 2
// $button1         - Своє ім'я для кнопки 1 (задається у вигляді масиву, приклад: array('new', 'New item'))
// $button2         - Своє ім'я для кнопки 2 (задається у вигляді масиву, приклад: array('new', 'New item'))
// $search_box_name - Ім'я кнопки пошуку
function form_journal( $name, $perm_button, $title, $description1, $description2 = '', $button1 = array(), $button2 = array(), $search_box_name = '' ) {
  $gl_ = gl_form_array::get();

  if ( $search_box_name == '' ) {
    $search_box_name = __( "Search", "wp-add-function" );
  }

  $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

  // Зафіксуємо поточний paged, (номер сторінки пагінації), для подальшого повернення
  $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;
  $page   = get_page_name();

  $class_table = $gl_['class-table'];
  $class_table -> prepare_items();

  // якщо пусте значення $button1
  if (empty($button1)){
    $button1_action = 'new';
    $button1_name   = __('New item', "wp-add-function" );
  } else {
    $button1_action = $button1[0];
    $button1_name   = $button1[1];
  }

  ?>
  <div class="wrap">
  <div id="icon-users" class="icon32"><br/></div>
  <h2>
  <?php echo $title; ?>
  <?php if ( current_user_can( $perm_button )){
    ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button1_action);?>" class="page-title-action wpaf-button wpaf-button-primary">
    <?php echo _e($button1_name, $gl_['plugin_name'] );?>
    </a>
    <?php
    // якщо не пусте значення $button2
    if (! empty($button2)){
      ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button2[0]);?>" class="page-title-action wpaf-button wpaf-button-secondary">
      <?php echo _e($button2[1], $gl_['plugin_name'] );?>
      </a>
      <?php
    }
  } ?>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png' . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php echo $description1; ?>
  <?php
  if ( ! empty( $description2 ))
    echo '<p>' . $description2 . '</p>' ;
  ?>
  </td>
  </table>
  </p>
  </div>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( $action == 'filter-deletion' ){
    printf( '<span class="subtitle" style="color: #ce181e">' . __( 'Marked for deletion' , $gl_['plugin_name']) . '</span>' );
  }
  if ( strlen( $search_results )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_results ) );
  }
  ?>
  <?php
  $ccards_has_period = method_exists( $class_table, 'render_period_controls' );
  if ( $ccards_has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
    $class_table->period_in_search_row = true;
  }
  ?>
  <div style="overflow:hidden; margin: 10px 0 4px;">
  <?php if ( $ccards_has_period ) : ?>
  <div style="float:left; margin-right: 12px;">
  <?php $class_table->render_period_controls(); ?>
  </div>
  <?php endif; ?>

  <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
  </div>
  <?php $class_table -> display() ?>
  </form>
  </p>
  </div>
  <?php
}

//====================================
// Форма списку довідника
// $name            - Ім'я форми (приклад: users)
// $class_table     - Ім'я класу таблиці
// $perm_button     - Права на кнопки
// $title           - Заголовок
// $description1    - Опис 1
// $description2    - Опис 2
// $buttons         - Задається у вигляді масиву, приклад: array('new' => 'New item', 'new1' => 'New item 1')
// $search_box_name - Ім'я кнопки пошуку
function form_directory( $name, $class_table, $perm_button, $title, $description1, $description2 = '', $buttons = array(), $search_box_name = '' ) {
  $gl_ = gl_form_array::get();

  if ( $search_box_name == '' ) {
    $search_box_name = __( "Search", "wp-add-function" );
  }

  $search_results = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

  // Отримаємо $page з $class_table
  $page   = $class_table -> page;

  $class_table -> prepare_items();

  ?>
  <div class="wrap">
  <div id="icon-users" class="icon32"><br/></div>
  <h2>
  <?php echo $title; ?>
  <?php
  // Використовуємо функцію для відображення кнопок з класами
  if (function_exists('display_form_buttons')) {
    // Якщо функція існує, виводимо кнопки з нашими класами
    ob_start();
    display_form_buttons($buttons, $perm_button, $page);
    $buttons_html = ob_get_clean();

    // Додаємо наші класи до кнопок
    $buttons_html = str_replace('class="page-title-action"', 'class="page-title-action wpaf-button wpaf-button-primary"', $buttons_html);
    echo $buttons_html;
  } else {
    // Якщо функції немає, виводимо кнопки вручну
    if (!empty($buttons) && current_user_can($perm_button)) {
      foreach ($buttons as $action => $button_name) {
        ?> <a href="<?php echo sprintf('?page=%s&action=%s', $page, $action);?>" class="page-title-action wpaf-button wpaf-button-primary">
        <?php echo _e($button_name, 'wp-add-function'); ?>
        </a>
        <?php
      }
    }
  }
  ?>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png' . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php echo $description1; ?>
  <?php
  if (!empty($description2))
    echo '<p>' . $description2 . '</p>';
  ?>
  </td>
  </table>
  </p>
  </div>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ($action == 'filter-deletion') {
    printf('<span class="subtitle" style="color: #ce181e">' . __('Marked for deletion', $gl_['plugin_name']) . '</span>');
  }
  if (strlen($search_results)) {
    /* translators: %s: search keywords */
    printf('<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', esc_html($search_results));
  }
  // якщо використовується фільтр
  if (!empty($class_table->filter)) {
    $filter_str = filter_str($class_table->filter);
    /* translators: %s: search keywords */
    printf('<span class="subtitle" style="color: #336699;font-weight:bold">' . __('Filter by &#8220;%s&#8221;', $gl_['plugin_name']) . '</span>', esc_html($filter_str));
  }
  ?>
  <?php
  $ccards_has_period = method_exists( $class_table, 'render_period_controls' );
  if ( $ccards_has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
    $class_table->period_in_search_row = true;
  }
  ?>
  <div style="overflow:hidden; margin: 10px 0;">
  <?php if ( $ccards_has_period ) : ?>
  <div style="float:left; margin-right: 12px;">
  <?php $class_table->render_period_controls(); ?>
  </div>
  <?php endif; ?>

  <?php $class_table->search_box($search_box_name, $gl_['plugin_name']); ?>
  </div>
  <?php $class_table->display() ?>

  </form>
  </p>
  </div>
  <?php
}

//====================================
// Форма списку з історією
// $name            - Ім'я форми (приклад: users)
// $class_table     - Ім'я класу таблиці
// $search_box_name - Ім'я кнопки пошуку
function form_directory_history( $class_table, $title, $description, $search_box_name = '' ) {
  $gl_ = gl_form_array::get();

  // пошук для історії поки не реалізовано
  $search_value = '';

  if ( $search_box_name == '' ) {
    $search_box_name = __( "Search", 'wp-add-function' );
  }

  // поточна сторінка
  $page  = get_page_name( $gl_['prefix'] );

  // сторінка-батько, на яку повертаємося
  $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // це paged для $parent (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  $class_table -> prepare_items();
  ?>
  <div class="wrap">
  <div id="icon-users" class="icon32"><br/></div>
  <h2>
  <?php echo $title ?>
  <a href="<?php echo sprintf('?page=%s&paged=%s', $parent, $numbered );?>" class="page-title-action">
  <?php echo _e( 'Return', 'wp-add-function' ); ?>
  </a>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png' . '"name="picture_title"
  align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php echo $description ?>
  </td>
  </table>
  </p>
  </div>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( strlen( $search_value )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_value ) );
  }
  ?>
  <?php
  $ccards_has_period = method_exists( $class_table, 'render_period_controls' );
  if ( $ccards_has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
    $class_table->period_in_search_row = true;
  }
  ?>
  <div style="overflow:hidden; margin: 10px 0;">
  <?php if ( $ccards_has_period ) : ?>
  <div style="float:left; margin-right: 12px;">
  <?php $class_table->render_period_controls(); ?>
  </div>
  <?php endif; ?>

  <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
  </div>
  <?php $class_table -> display() ?>
  </form>
  </p>
  </div>
  <?php
}

//====================================
// Форма списку позначених на видалення елементів
// $class_table     - Ім'я класу таблиці
// $search_box_name - Ім'я кнопки пошуку
function form_deletion( $class_table, $title, $description, $search_box_name = '' ) {
  $gl_ = gl_form_array::get();

  // пошук для історії поки не реалізовано
  $search_value = '';

  if ( $search_box_name == '' ) {
    $search_box_name = __( "Search", 'wp-add-function' );
  }

  // поточна сторінка
  $page  = get_page_name( $gl_['prefix'] );

  // сторінка-батько, на яку повертаємося
  $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // це paged для $parent (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  $class_table -> prepare_items();

  ?>
  <div class="wrap">
  <div id="icon-users" class="icon32"><br/></div>
  <h2>
  <?php echo $title ?>
  <a href="<?php echo sprintf('?page=%s&paged=%s', $parent, $numbered );?>" class="page-title-action">
  <?php echo _e( 'Return', 'wp-add-function' ); ?>
  </a>
  </h2>
  <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
  <p>
  <table class="wpuf-table">
  <th>
  <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png' . '"name="picture_title"
  align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
  </th>
  <td>
  <?php echo $description ?>
  </td>
  </table>
  </p>
  </div>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( strlen( $search_value )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_value ) );
  }
  ?>
  <?php
  $ccards_has_period = method_exists( $class_table, 'render_period_controls' );
  if ( $ccards_has_period && property_exists( $class_table, 'period_in_search_row' ) ) {
    $class_table->period_in_search_row = true;
  }
  ?>
  <div style="overflow:hidden; margin: 10px 0;">
  <?php if ( $ccards_has_period ) : ?>
  <div style="float:left; margin-right: 12px;">
  <?php $class_table->render_period_controls(); ?>
  </div>
  <?php endif; ?>

  <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
  </div>
  <?php $class_table -> display() ?>
  </form>
  </p>
  </div>
  <?php
}

//====================================
// Функція виведення діалогу з питанням позначки
// на видалення елемента довідника
function form_delete( $plural_name_lang, $name_id ) {
  $gl_ = gl_form_array::get();

  // Якщо є то отримаємо значення ID
  $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
  $page    = get_page_name( $gl_['prefix'] );
  ?>
  <div class="wrap">
  <?php
  // виведемо шапку
  html_title(__( 'Delete item', 'wp-add-function' ),
             '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png',
             __( 'In this dialog, you can mark the selected directory entry for deletion.', 'wp-add-function' ),
             __( 'The directory element is not deleted completely, but only marked for deletion.', 'wp-add-function' ));
  ?>
  <h4>
  <font color="#ce181e">
  <?php echo sprintf( __( "Do you want to delete the directory entry '%s' with the number '%s'?", 'wp-add-function' ), $plural_name_lang, $item_id );?>
  </font>
  </h4>
  <form action="" method="post">
  <p>
  <?php submit_button(__( 'Delete', 'wp-add-function' ), 'button',  'button_delete', false); ?>
  <?php submit_button(__( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false); ?>
  </p>
  </form>
  </div>
  <?php
}

//====================================
// Функція для виведення діалогу з питанням скасування позначки
// на видалення елемента довідника
function form_cancel_deletion( $plural_name_lang, $name_id ) {
  $gl_ = gl_form_array::get();

  // Якщо є то отримаємо значення ID
  $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
  $page    = get_page_name( $gl_['prefix'] );

  ?>
  <div class="wrap">
  <?php
  // виведемо шапку
  html_title(__( 'Cancel deletion item', 'wp-add-function' ),
             '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png',
             __( 'In this dialog box you can remove the mark for deletion from the selected directory entry.', 'wp-add-function' ));
  ?>
  <h4>
  <?php echo sprintf( __( "Do you want to cancel the deletion of the directory entry '%s' with the number '%s'?", 'wp-add-function' ), $plural_name_lang, $item_id );?>
  </h4>
  <form action="" method="post">
  <p>
  <?php submit_button(__( 'Apply', 'wp-add-function' ),  'button',  'button_apply',  false); ?>
  <?php submit_button(__( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false); ?>
  </p>
  </form>
  </div>
  <?php
}

//====================================
// Різні варіанти форм
function view_form( $plural_name_lang, $name_id ) {
  // отримаємо $action
  $action = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';
  if ( ! empty( $action )) {
    if ( $action == 'edit' )
      view_form_edit();
    elseif ( $action == 'delete' )
      form_delete( $plural_name_lang, $name_id );
    elseif ( $action == 'cancel-deletion' )
      form_cancel_deletion( $plural_name_lang, $name_id );
    elseif ( $action == 'history' )
      view_form_history();
    elseif ( $action == 'filter-deletion' )
      view_form_deletion();
    else {
      // Виконаємо функцію з префіксом $action
      $func = 'view_form_' . $action ;
      $func();
    }
  }
  else
    view_form_list();
}
?>
