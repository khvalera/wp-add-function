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

  if ( function_exists( 'wpaf_get_search_button_label' ) ) {
    $search_box_name = wpaf_get_search_button_label( $search_box_name, 'wp-add-function' );
  }

  $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';
  $page   = get_page_name();

  $class_table = $gl_['class-table'];
  $class_table -> prepare_items();
  $picture_url = WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png';

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
    wpaf_render_admin_screen_header(
      array(
        'title'         => $title,
        'title_is_html' => true,
        'picture_url'   => $picture_url,
        'description1'  => $description1,
        'description2'  => $description2,
      )
    );
  } else {
    ?>
    <div id="icon-users" class="icon32"><br/></div>
    <h2>
    <?php echo $title; ?>
    </h2>
    <?php
    if ( function_exists( 'wpaf_render_admin_intro_box' ) ) {
      wpaf_render_admin_intro_box(
        array(
          'picture_url'  => $picture_url,
          'description1' => $description1,
          'description2' => $description2,
        )
      );
    } else {
      ?>
      <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
      <p>
      <table class="wpuf-table">
      <th>
      <?php echo '<img src="' . $picture_url . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
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
      <?php
    }
  }
  ?>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( function_exists( 'wpaf_render_list_subtitles' ) ) {
    wpaf_render_list_subtitles(
      array(
        'plugin_name'                   => $gl_['plugin_name'],
        'action'                        => $action,
        'search_results'                => $search_results,
        'filter'                        => ! empty( $class_table->filter ) ? $class_table->filter : '',
        'hide_filter_deletion_subtitle' => ! empty( $gl_['hide_filter_deletion_subtitle'] ),
      )
    );
  } else {
    if ( $action == 'filter-deletion' && empty( $gl_['hide_filter_deletion_subtitle'] ) ){
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
  }
  ?>
  <?php
  if ( function_exists( 'wpaf_render_list_search_row' ) ) {
    wpaf_render_list_search_row(
      array(
        'class_table'      => $class_table,
        'search_box_name'  => $search_box_name,
        'plugin_name'      => $gl_['plugin_name'],
        'wrapper_style'    => 'overflow:hidden; margin: 10px 0 4px;',
      )
    );
  } else {
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
    <?php
  }
  ?>
  <?php $class_table -> display() ?>
  </form>
  </p>
  <?php
  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
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

  if ( function_exists( 'wpaf_get_search_button_label' ) ) {
    $search_box_name = wpaf_get_search_button_label( $search_box_name, 'wp-add-function' );
  }

  $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

  // Зафіксуємо поточний paged, (номер сторінки пагінації), для подальшого повернення
  $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;
  $page   = get_page_name();

  $class_table = $gl_['class-table'];
  $class_table -> prepare_items();

  // якщо пусте значення $button1
  if ( empty( $button1 ) ) {
    $button1_action = 'new';
    $button1_name   = __( 'New item', 'wp-add-function' );
  } else {
    $button1_action = $button1[0];
    $button1_name   = $button1[1];
  }

  $picture_url   = WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png';
  $title_actions = array();

  if ( current_user_can( $perm_button ) ) {
    $title_actions[] = array(
      'url'   => sprintf( '?page=%s&paged=%s&action=%s', $page, $paged, $button1_action ),
      'label' => __( $button1_name, $gl_['plugin_name'] ),
      'class' => 'page-title-action wpaf-button wpaf-button-primary',
    );

    if ( ! empty( $button2 ) ) {
      $title_actions[] = array(
        'url'   => sprintf( '?page=%s&paged=%s&action=%s', $page, $paged, $button2[0] ),
        'label' => __( $button2[1], $gl_['plugin_name'] ),
        'class' => 'page-title-action wpaf-button wpaf-button-secondary',
      );
    }
  }

  $title_html = $title;

  if ( function_exists( 'wpaf_get_title_with_actions_html' ) ) {
    $title_html = wpaf_get_title_with_actions_html( $title, $title_actions );
  }

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
    wpaf_render_admin_screen_header(
      array(
        'title'         => $title_html,
        'title_is_html' => true,
        'picture_url'   => $picture_url,
        'description1'  => $description1,
        'description2'  => $description2,
      )
    );
  } else {
    ?>
    <div id="icon-users" class="icon32"><br/></div>
    <h2>
    <?php echo $title; ?>
    <?php if ( current_user_can( $perm_button ) ) {
      ?> <a href="<?php echo sprintf( '?page=%s&paged=%s&action=%s', $page, $paged, $button1_action ); ?>" class="page-title-action wpaf-button wpaf-button-primary">
      <?php echo _e( $button1_name, $gl_['plugin_name'] ); ?>
      </a>
      <?php
      // якщо не пусте значення $button2
      if ( ! empty( $button2 ) ) {
        ?> <a href="<?php echo sprintf( '?page=%s&paged=%s&action=%s', $page, $paged, $button2[0] ); ?>" class="page-title-action wpaf-button wpaf-button-secondary">
        <?php echo _e( $button2[1], $gl_['plugin_name'] ); ?>
        </a>
        <?php
      }
    } ?>
    </h2>
    <?php
    if ( function_exists( 'wpaf_render_admin_intro_box' ) ) {
      wpaf_render_admin_intro_box(
        array(
          'picture_url'  => $picture_url,
          'description1' => $description1,
          'description2' => $description2,
        )
      );
    } else {
      ?>
      <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
      <p>
      <table class="wpuf-table">
      <th>
      <?php echo '<img src="' . $picture_url . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
      </th>
      <td>
      <?php echo $description1; ?>
      <?php
      if ( ! empty( $description2 ) )
        echo '<p>' . $description2 . '</p>' ;
      ?>
      </td>
      </table>
      </p>
      </div>
      <?php
    }
  }
  ?>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( function_exists( 'wpaf_render_list_subtitles' ) ) {
    wpaf_render_list_subtitles(
      array(
        'plugin_name'                   => $gl_['plugin_name'],
        'action'                        => $action,
        'search_results'                => $search_results,
        'hide_filter_deletion_subtitle' => ! empty( $gl_['hide_filter_deletion_subtitle'] ),
      )
    );
  } else {
    if ( $action == 'filter-deletion' && empty( $gl_['hide_filter_deletion_subtitle'] ) ){
      printf( '<span class="subtitle" style="color: #ce181e">' . __( 'Marked for deletion' , $gl_['plugin_name']) . '</span>' );
    }
    if ( strlen( $search_results )) {
      /* translators: %s: search keywords */
      printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_results ) );
    }
  }
  ?>
  <?php
  if ( function_exists( 'wpaf_render_list_search_row' ) ) {
    wpaf_render_list_search_row(
      array(
        'class_table'      => $class_table,
        'search_box_name'  => $search_box_name,
        'plugin_name'      => $gl_['plugin_name'],
        'wrapper_style'    => 'overflow:hidden; margin: 10px 0 4px;',
      )
    );
  } else {
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
    <?php
  }
  ?>
  <?php $class_table -> display() ?>
  </form>
  </p>
  <?php
  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
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

  if ( function_exists( 'wpaf_get_search_button_label' ) ) {
    $search_box_name = wpaf_get_search_button_label( $search_box_name, 'wp-add-function' );
  }

  $search_results = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

  $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

  // Отримаємо $page з $class_table
  $page   = $class_table -> page;

  $class_table -> prepare_items();
  $picture_url = WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $name . '-64x64.png';
  $title_html  = $title;

  ob_start();

  // Використовуємо функцію для відображення кнопок з класами.
  if ( function_exists( 'display_form_buttons' ) ) {
    display_form_buttons( $buttons, $perm_button, $page );
    $buttons_html = ob_get_clean();
    $buttons_html = str_replace( 'class="page-title-action"', 'class="page-title-action wpaf-button wpaf-button-primary"', $buttons_html );
  } else {
    if ( ! empty( $buttons ) && current_user_can( $perm_button ) ) {
      foreach ( $buttons as $button_action => $button_name ) {
        ?> <a href="<?php echo sprintf( '?page=%s&action=%s', $page, $button_action ); ?>" class="page-title-action wpaf-button wpaf-button-primary">
        <?php echo _e( $button_name, 'wp-add-function' ); ?>
        </a>
        <?php
      }
    }

    $buttons_html = ob_get_clean();
  }

  if ( $buttons_html !== '' ) {
    $title_html .= ' ' . $buttons_html;
  }

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_admin_screen_header' ) ) {
    wpaf_render_admin_screen_header(
      array(
        'title'         => $title_html,
        'title_is_html' => true,
        'picture_url'   => $picture_url,
        'description1'  => $description1,
        'description2'  => $description2,
      )
    );
  } else {
    ?>
    <div id="icon-users" class="icon32"><br/></div>
    <h2>
    <?php echo $title_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </h2>
    <?php
    if ( function_exists( 'wpaf_render_admin_intro_box' ) ) {
      wpaf_render_admin_intro_box(
        array(
          'picture_url'  => $picture_url,
          'description1' => $description1,
          'description2' => $description2,
        )
      );
    } else {
      ?>
      <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:2px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
      <p>
      <table class="wpuf-table">
      <th>
      <?php echo '<img src="' . $picture_url . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
      </th>
      <td>
      <?php echo $description1; ?>
      <?php
      if ( ! empty( $description2 ) ) {
        echo '<p>' . $description2 . '</p>';
      }
      ?>
      </td>
      </table>
      </p>
      </div>
      <?php
    }
  }
  ?>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( function_exists( 'wpaf_render_list_subtitles' ) ) {
    wpaf_render_list_subtitles(
      array(
        'plugin_name'                   => $gl_['plugin_name'],
        'action'                        => $action,
        'search_results'                => $search_results,
        'filter'                        => ! empty( $class_table->filter ) ? $class_table->filter : '',
        'hide_filter_deletion_subtitle' => ! empty( $gl_['hide_filter_deletion_subtitle'] ),
      )
    );
  } else {
    if ($action == 'filter-deletion' && empty( $gl_['hide_filter_deletion_subtitle'] )) {
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
  }
  ?>
  <?php
  if ( function_exists( 'wpaf_render_list_search_row' ) ) {
    wpaf_render_list_search_row(
      array(
        'class_table'      => $class_table,
        'search_box_name'  => $search_box_name,
        'plugin_name'      => $gl_['plugin_name'],
        'wrapper_style'    => 'overflow:hidden; margin: 10px 0;',
      )
    );
  } else {
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
    <?php
  }
  ?>
  <?php $class_table->display() ?>

  </form>
  </p>
  <?php
  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
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

  if ( function_exists( 'wpaf_get_search_button_label' ) ) {
    $search_box_name = wpaf_get_search_button_label( $search_box_name, 'wp-add-function' );
  }

  // поточна сторінка
  $page  = get_page_name( $gl_['prefix'] );

  // сторінка-батько, на яку повертаємося
  $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // це paged для $parent (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  $class_table -> prepare_items();

  $picture_url   = WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png';
  $return_url    = sprintf( '?page=%s&paged=%s', $parent, $numbered );

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_return_list_header' ) ) {
    wpaf_render_return_list_header(
      array(
        'title'       => $title,
        'return_url'  => $return_url,
        'picture_url' => $picture_url,
        'description1'=> $description,
      )
    );
  } else {
    ?>
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
    <?php echo '<img src="' . $picture_url . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
    </th>
    <td>
    <?php echo $description ?>
    </td>
    </table>
    </p>
    </div>
    <?php
  }
  ?>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( strlen( $search_value )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_value ) );
  }
  ?>
  <?php
  if ( function_exists( 'wpaf_render_list_search_row' ) ) {
    wpaf_render_list_search_row(
      array(
        'class_table'      => $class_table,
        'search_box_name'  => $search_box_name,
        'plugin_name'      => $gl_['plugin_name'],
        'wrapper_style'    => 'overflow:hidden; margin: 10px 0;',
      )
    );
  } else {
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
    <?php
  }
  ?>
  <?php $class_table -> display() ?>
  </form>
  </p>
  <?php
  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
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

  if ( function_exists( 'wpaf_get_search_button_label' ) ) {
    $search_box_name = wpaf_get_search_button_label( $search_box_name, 'wp-add-function' );
  }

  // поточна сторінка
  $page  = get_page_name( $gl_['prefix'] );

  // сторінка-батько, на яку повертаємося
  $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // це paged для $parent (номер сторінки пагінації, використовується для подальшого повернення на батьківську сторінку)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  $class_table -> prepare_items();

  $picture_url   = WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png';
  $return_url    = sprintf( '?page=%s&paged=%s', $parent, $numbered );

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_return_list_header' ) ) {
    wpaf_render_return_list_header(
      array(
        'title'       => $title,
        'return_url'  => $return_url,
        'picture_url' => $picture_url,
        'description1'=> $description,
      )
    );
  } else {
    ?>
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
    <?php echo '<img src="' . $picture_url . '"name="picture_title" align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
    </th>
    <td>
    <?php echo $description ?>
    </td>
    </table>
    </p>
    </div>
    <?php
  }
  ?>
  <p>
  <form id="form-filter" action="" method="post">
  <?php
  if ( strlen( $search_value )) {
    /* translators: %s: search keywords */
    printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_value ) );
  }
  ?>
  <?php
  if ( function_exists( 'wpaf_render_list_search_row' ) ) {
    wpaf_render_list_search_row(
      array(
        'class_table'      => $class_table,
        'search_box_name'  => $search_box_name,
        'plugin_name'      => $gl_['plugin_name'],
        'wrapper_style'    => 'overflow:hidden; margin: 10px 0;',
      )
    );
  } else {
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
    <?php
  }
  ?>
  <?php $class_table -> display() ?>
  </form>
  </p>
  <?php
  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
}

//====================================
// Функція виведення діалогу з питанням позначки
// на видалення елемента довідника
function form_delete( $plural_name_lang, $name_id ) {
  $gl_ = gl_form_array::get();

  // Якщо є то отримаємо значення ID
  $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
  $page    = get_page_name( $gl_['prefix'] );
  $picture_url = '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png';
  $confirm_message = sprintf( __( "Do you want to delete the directory entry '%s' with the number '%s'?", 'wp-add-function' ), $plural_name_lang, $item_id );

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_directory_confirm_header' ) ) {
    wpaf_render_directory_confirm_header(
      array(
        'title'        => __( 'Delete item', 'wp-add-function' ),
        'picture_url'  => $picture_url,
        'description1' => __( 'In this dialog, you can mark the selected directory entry for deletion.', 'wp-add-function' ),
        'description2' => __( 'The directory element is not deleted completely, but only marked for deletion.', 'wp-add-function' ),
      )
    );
  } else {
    html_title(
      __( 'Delete item', 'wp-add-function' ),
      $picture_url,
      __( 'In this dialog, you can mark the selected directory entry for deletion.', 'wp-add-function' ),
      __( 'The directory element is not deleted completely, but only marked for deletion.', 'wp-add-function' )
    );
  }

  if ( function_exists( 'wpaf_render_confirm_form' ) ) {
    $confirm_message_html = function_exists( 'wpaf_get_confirm_message_html' )
      ? wpaf_get_confirm_message_html( $confirm_message, array( 'tag' => 'h4', 'color' => '#ce181e' ) )
      : '<h4><span style="color:#ce181e">' . esc_html( $confirm_message ) . '</span></h4>';

    $actions_args = function_exists( 'wpaf_get_confirm_actions_args' )
      ? wpaf_get_confirm_actions_args(
          'button_delete',
          function_exists( 'wpaf_get_delete_button_label' ) ? wpaf_get_delete_button_label( '', 'wp-add-function' ) : __( 'Delete', 'wp-add-function' ),
          array(
            'submit_class' => 'button',
            'cancel_label' => function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ),
          )
        )
      : array(
          'submit_name'   => 'button_delete',
          'submit_label'  => function_exists( 'wpaf_get_delete_button_label' ) ? wpaf_get_delete_button_label( '', 'wp-add-function' ) : __( 'Delete', 'wp-add-function' ),
          'submit_class'  => 'button',
          'extra_actions' => function_exists( 'wpaf_get_submit_button_html' )
            ? wpaf_get_submit_button_html( function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false )
            : '',
        );

    wpaf_render_confirm_form(
      array(
        'before_form_html' => $confirm_message_html,
        'actions_args'     => $actions_args,
      )
    );
  } else {
    ?>
    <h4>
    <font color="#ce181e">
    <?php echo $confirm_message; ?>
    </font>
    </h4>
    <form action="" method="post">
    <p>
    <?php submit_button( function_exists( 'wpaf_get_delete_button_label' ) ? wpaf_get_delete_button_label( '', 'wp-add-function' ) : __( 'Delete', 'wp-add-function' ), 'button', 'button_delete', false ); ?>
    <?php submit_button( function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false ); ?>
    </p>
    </form>
    <?php
  }

  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
}

//====================================
// Функція для виведення діалогу з питанням скасування позначки
// на видалення елемента довідника
function form_cancel_deletion( $plural_name_lang, $name_id ) {
  $gl_ = gl_form_array::get();

  // Якщо є то отримаємо значення ID
  $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
  $page    = get_page_name( $gl_['prefix'] );
  $picture_url = '/' . $gl_['plugin_name'] . '/images/' . $page . '-64x64.png';
  $confirm_message = sprintf( __( "Do you want to cancel the deletion of the directory entry '%s' with the number '%s'?", 'wp-add-function' ), $plural_name_lang, $item_id );

  if ( function_exists( 'wpaf_render_admin_wrap_start' ) ) {
    wpaf_render_admin_wrap_start();
  } else {
    echo '<div class="wrap">';
  }

  if ( function_exists( 'wpaf_render_directory_confirm_header' ) ) {
    wpaf_render_directory_confirm_header(
      array(
        'title'        => __( 'Cancel deletion item', 'wp-add-function' ),
        'picture_url'  => $picture_url,
        'description1' => __( 'In this dialog box you can remove the mark for deletion from the selected directory entry.', 'wp-add-function' ),
      )
    );
  } else {
    html_title(
      __( 'Cancel deletion item', 'wp-add-function' ),
      $picture_url,
      __( 'In this dialog box you can remove the mark for deletion from the selected directory entry.', 'wp-add-function' )
    );
  }

  if ( function_exists( 'wpaf_render_confirm_form' ) ) {
    $confirm_message_html = function_exists( 'wpaf_get_confirm_message_html' )
      ? wpaf_get_confirm_message_html( $confirm_message, array( 'tag' => 'h4' ) )
      : '<h4>' . esc_html( $confirm_message ) . '</h4>';

    $actions_args = function_exists( 'wpaf_get_confirm_actions_args' )
      ? wpaf_get_confirm_actions_args(
          'button_apply',
          __( 'Apply', 'wp-add-function' ),
          array(
            'submit_class' => 'button',
            'cancel_label' => function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ),
          )
        )
      : array(
          'submit_name'   => 'button_apply',
          'submit_label'  => __( 'Apply', 'wp-add-function' ),
          'submit_class'  => 'button',
          'extra_actions' => function_exists( 'wpaf_get_submit_button_html' )
            ? wpaf_get_submit_button_html( function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false )
            : '',
        );

    wpaf_render_confirm_form(
      array(
        'before_form_html' => $confirm_message_html,
        'actions_args'     => $actions_args,
      )
    );
  } else {
    ?>
    <h4>
    <?php echo $confirm_message; ?>
    </h4>
    <form action="" method="post">
    <p>
    <?php submit_button(__( 'Apply', 'wp-add-function' ),  'button',  'button_apply',  false); ?>
    <?php submit_button( function_exists( 'wpaf_get_cancel_button_label' ) ? wpaf_get_cancel_button_label( '', 'wp-add-function' ) : __( 'Cancel', 'wp-add-function' ), 'primary', 'button_cancel', false ); ?>
    </p>
    </form>
    <?php
  }

  if ( function_exists( 'wpaf_render_admin_wrap_end' ) ) {
    wpaf_render_admin_wrap_end();
  } else {
    echo '</div>';
  }
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
