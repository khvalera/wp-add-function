<?php

// функции для работы с страницами

// Создаем экземпляр для оброботки ошибок
global $form_message;
$form_message = new WP_Error;

//====================================
// Форма отчета
// $name            - Имя формы (пример: balances)
// $title           - Заглавие
// $description1    - Описание 1
// $description2    - Описание 2
// $search_box_name - Имя кнопки поиска
function form_report( $name, $title, $description1, $description2 = '', $search_box_name = '' ) {
   global $gl_;

   if ( $search_box_name == '' ) {
      $search_box_name = __( "Search", "wp-add-function" );
   }

   $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
   $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

   $page   = get_page_name();

   $gl_['class-table'] -> prepare_items();
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
             ?>
             <?php $gl_['class-table'] -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
             <?php $gl_['class-table'] -> display() ?>
          </form>
       </p>
   </div>
   <?php
}

//====================================
// Форма журнала документов
// $name            - Имя формы (пример: journal)
// $perm_button     - Права на кнопки
// $title           - Заглавие
// $description1    - Описание 1
// $description2    - Описание 2
// $button1         - Свое имя для кнопки 1 (задается в виде массива, пример: array('new', 'New item'))
// $button2         - Свое имя для кнопки 2 (задается в виде массива, пример: array('new', 'New item'))
// $search_box_name - Имя кнопки поиска
function form_journal( $name, $perm_button, $title, $description1, $description2 = '', $button1 = array(), $button2 = array(), $search_box_name = '' ) {
   global $gl_;

   if ( $search_box_name == '' ) {
      $search_box_name = __( "Search", "wp-add-function" );
   }

   $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
   $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

   // Зафиксируем текущий paged, для дальнейшего возврата
   $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

   $page   = get_page_name();

   $gl_['class-table'] -> prepare_items();
   // если пустое значение $button1
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
             ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button1_action);?>" class="page-title-action">
                   <?php echo _e($button1_name, $gl_['plugin_name'] );?>
                </a>
             <?php
            // если не пустое значение $button2
            if (! empty($button2)){
               ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button2[0]);?>" class="page-title-action">
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
             <?php $gl_['class-table'] -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
             <?php $gl_['class-table'] -> display() ?>
          </form>
       </p>
   </div>
   <?php
}

//====================================
// Форма списка справочника
// $name            - Имя формы (пример: users)
// $gl_['class-table']     - Имя класса таблицы
// $perm_button     - Права на кнопки
// $title           - Заглавие
// $description1    - Описание 1
// $description2    - Описание 2
// $button1         - Свое имя для кнопки 1 (задается в виде массива, пример: array('new', 'New item'))
// $button2         - Свое имя для кнопки 2 (задается в виде массива, пример: array('new', 'New item'))
// $search_box_name - Имя кнопки поиска
function form_directory( $name, $class_table, $perm_button, $title, $description1, $description2 = '', $button1 = array(), $button2 = array(), $search_box_name = '' ) {
   global $gl_;

   if ( $search_box_name == '' ) {
      $search_box_name = __( "Search", "wp-add-function" );
   }

   $search_results = isset( $_REQUEST['s'] )      ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

   // получим значение фильтра (передается имя поля таблицы)
   $filter = isset( $_REQUEST['f'] ) ? wp_unslash( trim( $_REQUEST['f'] )) : '';

   // для фильтра получим значение value
   $filter_value = isset( $_REQUEST['v'] ) ? wp_unslash( trim( $_REQUEST['v'] )) : '';

   $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

   // Зафиксируем текущий paged, для дальнейшего возврата
   $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

   $page   = get_page_name();

   $class_table -> prepare_items();
   // если пустое значение $button1
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
             ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button1_action);?>" class="page-title-action">
                   <?php echo _e($button1_name, $gl_['plugin_name'] );?>
                </a>
             <?php
            // если не пустое значение $button2
            if (! empty($button2)){
               ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button2[0]);?>" class="page-title-action">
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
                // если используется фильтр
                if ( ! empty( $filter ) and ! empty( $filter_value )){
                   // преобразуем filter в массив
                  $array_filter = explode( "|", $filter );
                  $array_value  = explode( "|", $filter_value );
                  $filter_str = "";
                  foreach ( $array_filter as $index => $f ) {
                     if ( ! empty( $f ) and ! empty( $array_value[$index] ) ) {
                        // если первый знак *, то не используем таблицу
                        if ( $f[0] == "*"){
                           if ( !empty($filter_str))
                              $filter_str =  $filter_str . " , ";
                              $filter_str =  $filter_str . substr($f, 1 ) . " = " . $array_value[$index];
                        } else {
                           if ( !empty($query_filter))
                              $filter_str =  $filter_str . " , ";
                              $filter_str = $filter_str . $f . " = " . $array_value[$index];
                        }
                     }
                   }
                   /* translators: %s: search keywords */
                   printf( '<span class="subtitle" style="color: #336699;font-weight:bold">' . __( 'Filter by &#8220;%s&#8221;', $gl_['plugin_name'] ) . '</span>', esc_html( $filter_str ) );
                }
             ?>
             <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
             <?php $class_table -> display() ?>
          </form>
       </p>
   </div>
   <?php
}

//====================================
// Форма списка истории
// $name            - Имя формы (пример: users)
// $class_table     - Имя класса таблицы
// $search_box_name - Имя кнопки поиска
function form_directory_history( $class_table, $title, $description, $search_box_name = '' ) {
   global $gl_;

   // поиск для истории пока не реализован
   $search_value = '';

   if ( $search_box_name == '' ) {
      $search_box_name = __( "Search", 'wp-add-function' );
   }
   // Для возврата на прежде открытый номер родительской страницы получим pagep
   $pagep = isset($_REQUEST['pagep']) ? max(0, intval($_REQUEST['pagep'] )) : 1;

   $page  = get_page_name( $gl_['prefix'] );

   $class_table -> prepare_items();
   ?>
    <div class="wrap">
    <div id="icon-users" class="icon32"><br/></div>
       <h2>
           <?php echo $title ?>
           <a href="<?php echo sprintf('?page=%s&paged=%s', get_page_name(), $pagep );?>" class="page-title-action">
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
          <form id="form-filter" action="" method="get">
             <?php
                if ( strlen( $search_value )) {
                   /* translators: %s: search keywords */
                   printf( '<span class="subtitle">' . __( 'Search results for &#8220;%s&#8221;' ) . '</span>', esc_html( $search_value ) );
                }
             ?>
             <?php $class_table -> search_box( $search_box_name, $gl_['plugin_name'] ); ?>
             <?php $class_table -> display() ?>
          </form>
       </p>
    </div>
   <?php
}

//====================================
// Функция вывода диалога с вопросом пометки
// на удаление элемента справочника
function form_delete( $plural_name_lang, $name_id ) {
   global $gl_;

   // Если есть то получим значение ID
   $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
   $page    = get_page_name( $gl_['prefix'] );
   ?>
    <div class="wrap">
       <?php
          // выведем шапку
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
// Функция для вывода диалога с вопросом отмены пометки
// на удаление элемента справочника
function form_cancel_deletion( $plural_name_lang, $name_id ) {
   global $gl_;

   // Если есть то получим значение ID
   $item_id = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
   $page    = get_page_name( $gl_['prefix'] );

   ?>
    <div class="wrap">
       <?php
          // выведем шапку
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
// Разные варианты форм
function view_form( $plural_name_lang, $name_id ) {
   // получим $action
   $action = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';
   if ( ! empty( $action )) {
      if ( $action == 'edit' )
         view_form_edit();
      elseif ( $action == 'delete' )
         form_delete( $plural_name_lang, $name_id );
      elseif ( $action == 'cancel-deletion' )
         form_cancel_deletion( $plural_name_lang, $name_id );
      elseif ( substr($action, 0, 3) == 'new' ){
         // Выполним функцию с префиксом $action
         $func = 'view_form_' . $action ;
         $func();
      }elseif ( $action == 'history' )
         view_form_history();
      else
         view_form_list();
    }
    else
       view_form_list();
}

//====================================
// Обработка действий POST формы
function post_form_actions(){
   global $gl_;

   // Получим текущую страницу (вместе с префиксом)
   $page    = get_page_name();

   // зафиксируем текущий paged (номер страницы), для дальнейшего возврата
   $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

   // pages - страница родитель для дальнейшего возврата
   $pages = isset( $_REQUEST['pages'] ) ? wp_unslash( trim( $_REQUEST['pages'] )) : '';

   // Кнопка применить для периода в журнале документов
   $POST_PERIOD = isset( $_POST['button_period'] );
   if ( ! empty( $POST_PERIOD )) {
      // Заполним в массив данные значений полей формы
      $data = post_array();

      // Запишем даты журнала
      // Получим id пользователя WP
      $user_id = get_current_user_id();
      if( ! update_user_meta( $user_id, str_replace('-','_', $page) . '_date1', $data['date1'] ) ){
        echo "Поле не обновлено";
      }
      if( ! update_user_meta( $user_id, str_replace('-','_', $page) . '_date2', $data['date2'] ) ){
        echo "Поле не обновлено";
      }
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // Обработаем нажатие кнопки Save
   $POST_SAVE = isset( $_POST['button_save'] );
   if ( ! empty( $POST_SAVE )) {
      save_edit_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // Обработаем нажатие Save New
   $POST_SAVE_NEW = isset( $_POST['button_new_save'] );
   // Если после записи нужно перенаправление на другую страницу
   if ( ! empty( $pages )) {
      if ( ! empty( $POST_SAVE_NEW )) {
         if ( save_new_data() != 1 )
            // Если есть ошибки или сообщения покажем все
            display_message();
         // Получим имя поля для возврата в pages
         $field = isset( $_REQUEST['f'] ) ? wp_unslash( trim( $_REQUEST['f'] )) : '';
         wp_redirect( get_admin_url( null, 'admin.php?page=' . $pages . '&' . $field . '=' . $gl_[$field] ));
      }
   // Обработка записи нового эелемента
   } else{
      if ( ! empty( $POST_SAVE_NEW )) {
         $pagep  =  isset( $_REQUEST['pagep'] ) ? wp_unslash( trim( $_REQUEST['pagep'] )) : '';
         save_new_data();
         // Если есть ошибки или сообщения покажем все
         display_message();
         if ( empty( $pagep ))
            wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
         else
            wp_redirect(get_admin_url(null, 'admin.php?page=card-holders&paged=' . $pagep ));
      }
   }

   // Обработаем нажатие кнопки Сancel
   $POST_CANCEL = isset( $_POST['button_cancel'] );
   if ( ! empty( $POST_CANCEL )) {
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // Обработаем нажатие кнопки Delete
   $POST_DELETE = isset( $_POST['button_delete'] );
   if ( ! empty( $POST_DELETE )) {
      delete_form_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // Обработаем нажатие кнопки Cancel Delete
   $POST_CANCEL_DELETE = isset( $_POST['button_apply'] );
   if ( ! empty( $POST_CANCEL_DELETE )) {
      delete_form_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }
}

//===========================================
// Функция перебирает все поля на форме с префиксом и записывает в массив
// для дальнейшей записи в базу данных
function post_array($prefix = 'tfield-'){
   $data = array();
   foreach ($_POST as $key => $value) {
      if ( stristr($key, $prefix )) {
          $key_str = str_replace($prefix, '', $key);
          $data[$key_str] = $value;
      }
   }

   return $data;
}

//===================================================
function post_get_str($par) {
   return isset( $_POST[ $par ] )  ? wp_unslash( trim( $_POST[ $par ] )) : '';
}

//===================================================
// $display_name  - Отображаемое имя реквизита на форме (можно указывать несколько, через |)
// $type          - Тип реквизита (number, text, date, time...) (можно указывать несколько, через |)
// $name          - Имя input (можно указывать несколько, через |)
// $value         - Значение (можно указывать несколько, через |)
// $extra_options - Дополнительные параметры, стиль тут тоже указывается style="width:352px;" (можно указывать несколько, через |)
// $onchange      - Название функции, выполняется после изменения значения элемента формы, когда это изменение зафиксировано.
// $not_tfield    - Если равно true не использовать tfield
function html_input( $display_name, $type, $name, $value='', $extra_options = '', $onchange = '', $not_tfield = '' ) {
   // Преобразуем строку с пробелами в массив
   $array_display_name  = explode( "|", $display_name );
   $array_type          = explode( "|", $type );
   $array_name          = explode( "|", $name );
   $array_value         = explode( "|", $value );
   $array_extra_options = explode( "|", $extra_options );
   // Проверим что бы переданное количество значений совпадало
   if ( count ( $array_type ) <> count ( $array_name ) or count ( $array_name ) <> count ( $array_display_name )) {
      display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
   }

   if ( ! empty( $value ) )
      if ( count ( $array_name ) <> count ( $array_value )) {
         display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
      }

   if ( ! empty( $extra_options ) )
      if ( count ( $array_name ) <> count ( $array_extra_options )) {
         display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
      }
   if ( ! empty( $onchange ) ){
      $onchange = 'onchange="' . $onchange.'"';
      // Добавим javascript
      javascript_arithmetic_input();
   }
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $array_display_name[0]; ?></th>
         <td>
            <?php
               foreach ( $array_name as $key => $_name ) {
                  $_display_name = $array_display_name[$key];
                  $_type         = $array_type[$key];
                  // Если пустой extra_options используем style="width:350px; min-width: 100px;"
                  if ( ! empty( $extra_options ) )
                     // Если не найдено style и size добавим style="width:350px; min-width: 100px;"
                     if (( strrpos($array_extra_options[$key], "style=") === false ) and (strrpos($array_extra_options[$key], "size") === false))
                        $_extra_options='style="width:350px; min-width: 100px;"' . $array_extra_options[$key];
                     else
                        $_extra_options= $array_extra_options[$key];
                  else
                     $_extra_options='style="width:350px; min-width: 100px;"';

                  if ( ! empty( $value ) )
                     $_value = $array_value[$key];
                  else
                     $_value = '';
                  // Добавим 'tfield-' если в имени его нет
                  if ( $not_tfield != true )
                     if ( strpos( $_name, 'tfield-' ) === false )
                        $_name = 'tfield-' . $_name;
                  if ( $key == 0 ){
                     ?>
                        <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> <?php echo $onchange ?> >
                     <?php
                  } else{
                    ?>
                       <b style="margin-left: 6px;"><?php echo $_display_name ?></b>
                       <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> <?php echo $onchange ?> >
                     <?php
                  }
               }
            ?>
         </td>
      </tr>
   <?php
}

//===================================================
// sign - знак *, - , + и тд.
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
// Многострочный текст
// $display_name  - Отображаемое имя реквизита на форме
// $name          - Имя поля, предназначено для того, чтобы обработчик формы мог его идентифицировать.
// $cols          - Ширина поля в символах.
// $rows          - Высота поля в строках текста.
// $value         - Значение
function html_textarea( $display_name, $name, $cols = '', $rows = '', $value='' ) {
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $display_name; ?></th>
         <td>
            <?php
               // Добавим 'tfield-' если в имени его нет
               if ( strpos( $name, 'tfield-' ) === false )
                  $name = 'tfield-' . $name;
                  ?>
                     <textarea name="<?php echo $name ?>" id="<?php echo $name ?>"  cols="<?php echo $cols ?>" rows="<?php echo $rows ?>"><?php echo $value ?></textarea>
                  <?php
            ?>
         </td>
      </tr>
   <?php
}

//===================================================
// $display_name  - отображаемое имя реквизита
// $name          - имя (для авто сохранения должно соответствовать названию поля в таблице)
// $array_data    - масив данных
// $value_id      - id выбранной позиции
// $value_name    - имя выбранной позиции
// $extra_options - дополнительные параметры (стиль тут тоже указывается style="width:352px;")
function html_select($display_name, $name, $array_data, $extra_options = '', $value_id = '', $id_field = '', $value_field = '' ){
   // Добавим 'tfield-' если в имени его нет
   if ( strpos( $name, 'tfield-' ) === false )
      $name = 'tfield-' . $name;
   // если стиль не указан используем width:352px;
   if ( stripos($extra_options, 'style') == false )
      $extra_options = $extra_options . ' style="width:352px;" ';
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
// $display_name  - отображаемое имя реквизита
// $table_name    - имя таблицы базы данных
// $name          - имя класса и имя объекта на форме
// $extra_options - дополнительные параметры (тут указывается стиль тоже: style="width:352px;")
// $select_id     - id выбранной позиции
// $select_name   - строка или масив с именами полей из таблицы базы данных для добавления как name (по умолчанию name). пример: array("objectId", "holderName")
// $php_file      - путь к ajax файлу (не обязательно)
// $if_select     - имя поля для отбора, если не указано то используется objectId
// $params        - параметры для передачи в ajax_php (пример: "?f=objectId&v=1")
function html_select2( $display_name, $table_name, $name, $extra_options = '', $select_id = '', $select_name = '', $php_file = '', $if_select = '', $params = '') {
   global $gl_;

   // Добавим 'tfield-' если в имени его нет
   if ( strpos( $name, 'tfield-' ) === false )
      $name = 'tfield-' . $name;
   // если указан $select_id
   if ( ! empty( $select_id )) {
      // если функция указана в $gl_ используем ее
      if ( ! empty( $gl_['name_function_get_table_row_id'] )){
         $func = $gl_['name_function_get_table_row_id'];
         $data = $func($table_name, ARRAY_A, $select_id, $if_select);
      } else
        $data = get_row_table_id($table_name, ARRAY_A, $select_id);
      // если не выбран $select_name
      if ( empty( $select_name ))
         $select_name = $data['name'];
      else {
        if ( is_array( $select_name )){
           // если выбран $select_name, разберем
           $data_names=""; $nom = 0;
           foreach ($select_name as $n) {
              $nom++;
              if ( $nom == 1 )
                 $data_names = $data_names . $data[$n];
              else
                 $data_names = $data_names . " - " . $data[$n];
           }
           $select_name = $data_names;
         } else
           $select_name = $data[$select_name];
      }
   }
   //print_r($data); exit;
   // если есть objectId будем использовать его
   if ( ! empty( $data['objectId'] ))
      $name_id = 'objectId';
   else
      $name_id = 'id';

   //print_r($data['id']);
   // если стиль не указан используем width:352px;
   if ( stripos($extra_options, 'style') == false )
      $extra_options = $extra_options . ' style="width:352px;" ';
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $display_name; ?></th>
            <td>
               <select class="<?php echo 'item_' . $name; ?> form-control" name="<?php echo $name; ?>" <?php echo $extra_options ; ?> >
                  <?php
                    // Выберем нужную строку таблицы по $id из массива
                    if ( ! empty( $data )) {
                       echo "<option selected value=" . $data[$name_id] . ">" . $select_name . "</option>";
                    }
                  ?>
               </select>
            </td>
       </tr>
    <?php
   // если $php_file не указан используем ajax.php
   if ( empty( $php_file ) ) {
      $ajax_php   = WP_PLUGIN_URL . '/'. $gl_['plugin_name'] . '/includes/' . $table_name . '/ajax.php';
      // проверим существует ли файл в локальной системе
      if ( ! file_exists( dirname( plugin_dir_path( __FILE__ ), 2) . '/plugins/' . $gl_['plugin_name'] . '/includes/' . $table_name . '/ajax.php' )){
         display_message('file not found', sprintf(__( "File not found '%s'", 'wp-add-function' ), $gl_['plugin_name'] . '/includes/' . $table_name . '/ajax.php'), 'error');
      }
   } else {
      $ajax_php   = WP_PLUGIN_URL . '/'. $gl_['plugin_name'] . '/includes/' . $php_file;
      // проверим существует ли файл в локальной системе
      if ( ! file_exists( dirname( plugin_dir_path( __FILE__ ), 2) . '/plugins/' . $gl_['plugin_name'] . '/includes/' . $php_file )){
         display_message('file not found', sprintf(__( "File not found '%s'", 'wp-add-function' ), $gl_['plugin_name'] . '/includes/' . $php_file), 'error');
      }
   }
   java_item($name, $ajax_php, $params);
}

//===================================================
// javascript select2
function java_item($item_name, $ajax_php = '', $params = '' ){

   $place_item = __( 'Select value', 'wp-add-function' );
   $class_name = '.item_' . $item_name;
   ?>
   <script type="text/javascript">

      var class_name = '<?php echo $class_name; ?>';
      var place_item = '<?php echo $place_item; ?>';
      var ajax_php   = '<?php echo $ajax_php . $params; ?>';

      $(class_name).select2({
           placeholder: {
              id: '-1',
              text: place_item
           },
           ajax: {
             url: ajax_php,
             dataType: 'json',
             delay: 200,
             processResults: function (data) {
                 var data = data

               return {
                 results: data
               };
             },
           },
           cache: true
      });
      // обработка выбора значения
      $(class_name).on("select2:select", function(e) {
         //console.log(e); // весь объект
         //console.log(e.params.data); // вот тут обычно полезные данные
      });

      // Если нужно выбрать значение (пока не разобрался)
      //$(class_name).select2("trigger", "select", { data: { id: "3", text: '!!!' }});
      //var newOption = new Option(text, 0, false, false);
      //$(class_name).append(newOption).trigger('change');
      //$(class_name).val(1);
      //$(class_name).select2().trigger('change');
   </script>
   <?php
}

//===========================================
// Функция заполняет масив gl_
// singular_name      - имя в единственном числе
// plural_name        - имя в множественном числе
// db_table_name      - имя таблицы базы данных
// search_box_name    - название кнопки поиска
function gl_form_array( $plugin_name, $prefix, $singular_name, $db_table_name = '', $plural_name = '', $search_box_name = '' ) {

   // Если plural_name не указано то просто к singular_name добавим s
   if ( empty( $plural_name ))
      $plural_name = $singular_name . 's';

   // $class_table создадим на основаниии plural_name
   // создадим класс по имени
   $className = $prefix .'_class_table_' . str_replace('-','_', $plural_name );
   global ${$className};
   $class_table = ${$className};

   // Если $db_table_name не указано то оно навно singular_name
   if ( empty( $db_table_name ))
      $db_table_name = $singular_name . 's';

   if ( empty( $search_box_name ))
      $search_box_name = __( "Search", 'wp-add-function' );

   // Глобальный массив для передачи значений внутри формы
   $gl_ = array( 'singular_name'         => $singular_name,
                           'singular_name_lang'    => __(str_replace('-',' ', $singular_name), $plugin_name ),
                           'singular_Name_lang'    => __(ucfirst( str_replace('-',' ', $singular_name)), $plugin_name ),
                           'plural_name'           => $plural_name,
                           'plural_name_lang'      => __(str_replace('-',' ', $plural_name), $plugin_name ),
                           'search_box_name'       => $search_box_name,
                           'picture_title'         => "/images/".$plural_name."-64x64.png",
                           'class-table'           => $class_table,
                           'db_table_name'         => $db_table_name,
                           'db_table_name_history' => 'history_' . $db_table_name,
                           'plugin_name'           => $plugin_name,
                           'prefix'                => $prefix
                         );
   return $gl_;
}

//===================================================
// Получить имя страницы без префикса
// если $prefix не указан, страница вернется с префиксом
function get_page_name( $prefix = '' ){
    $page = isset( $_REQUEST['page'] ) ? wp_unslash( trim( $_REQUEST['page'] )) : '';
    if (! empty( $prefix ))
        $page = str_replace( $prefix . '-', '', $page );
    return $page;
}

//===================================================
// Функция добавляет пункты меню в admin_bar
// $image - указывается относительно каталога плагинов
function add_menu_wp_admin_bar($wp_admin_bar, $id, $image, $page, $nama_lang, $parent_id = '' ) {
   if ( $parent_id == '' ) {
       $wp_admin_bar -> add_menu( array(
      'id'    => $id,
      'title' => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
      'href'  => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
       ));
   } else {
       $wp_admin_bar -> add_menu( array(
       'parent' => $parent_id,           // параметр id из первой ссылки
       'id'     => $id,                  // свой id, чтобы можно было добавить дочерние ссылки
       'title'  => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
       'href'   => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
       ));
   }
}

//===================================================
class add_admin_menu {
    // объявление свойства
    public $item_name_menu;
    public $item_Name_menu_lang;
    public $current_user_can;
    public $plugin_name;
    public $plugin_prefix;
    public $parent_id;
    public $page;

    function __construct($item_name_menu, $item_Name_menu_lang, $current_user_can, $plugin_name, $plugin_prefix, $parent_id ){
       $this -> item_name_menu      = $item_name_menu;
       $this -> item_Name_menu_lang = $item_Name_menu_lang;
       $this -> current_user_can    = $current_user_can;
       $this -> plugin_name         = $plugin_name;
       $this -> plugin_prefix       = $plugin_prefix;
       $this -> parent_id           = $parent_id;
       // Что бы не было конфликтов с другими плагинами к странице добавим префикс
       $this -> page = $plugin_prefix . '-' . $item_name_menu;
       $this -> add_menu();
    }

    public function add_menu(){
       add_action( 'admin_menu', array( $this, 'submenu_page'));
       //===================================================
       // Дабавим пункт меню справочника в верхнюю панель
       // привяжем функцию к хуку
       if ( current_user_can( $this->current_user_can )){
            add_action( 'admin_bar_menu', function ( $wp_admin_bar ){
                  add_menu_wp_admin_bar( $wp_admin_bar,
                                         $this->plugin_prefix . '-' . $this->item_name_menu . '-menu-id',
                                         $this->plugin_name . '/images/' . $this->item_name_menu . '-16x16.png',
                                         $this->page,
                                         __( $this->item_Name_menu_lang, $this->plugin_name ),
                                         $this->plugin_prefix . '-' . $this->parent_id . '-menu-id' );
            }, 90 );
       }
    }

    public function submenu_page(){
         // var_dump( $this);
         // Добавим страницу
         $hook_menu = add_submenu_page(null, $this->item_Name_menu_lang, $this->item_Name_menu_lang, $this->current_user_can, $this->page,
                      function(){
                         management_session($this->page);
                         require_once( WP_PLUGIN_DIR .'/'. $this->plugin_name . '/includes/' . $this->item_name_menu . '/page.php' );
                     });

        // подключаемся к событию, когда страница загружена, но еще ничего не выводится
        add_action( "load-$hook_menu", function() {

           $option = 'per_page';
           $args = array(
                    'label'   => __( 'Number of lines per page', 'wp-add-function' ),
                    'default' => 10,
                    // название опции, будет записано в метаполе юзера
                    'option'  => $this->plugin_prefix .'_' . $this->item_name_menu . '_per_page',
                    );
            add_screen_option( $option, $args );
            // создадим имя глобальной переменной
            $perName = $this->plugin_prefix . '_class_table_' . str_replace('-','_', $this->item_name_menu);
            global ${$perName};

            // создадим класс по имени
            $className = $this->plugin_prefix .'_class_table_' . str_replace('-','_', $this->item_name_menu);
            ${$perName} = new $className;
        });
    }
}

//===================================================
// Управление сессиями
function management_session($sess) {
   //обработка сессий
   session_start();
   if ( session_id() != $sess) {
      if (session_status() == PHP_SESSION_ACTIVE) {
          session_destroy();
      }
   }
   if (session_status() != PHP_SESSION_ACTIVE) {
      session_id($sess);
      session_start();
   }
}

//===================================================
// Добавить сообщение или ошибку для дальнейшего отображения с помощью display_message
// $str_code - код ошибки или сообщения в виде строки
// $view     - подробное описание сообщения или ошибки, для отображения на странице
// $type     - тип сообщения, для ошибок должен быть error
// add_message('filed_to_get_data', __( "Failed to get data!", 'wp-add-function' ), 'error' );
function add_message( $str_code = '', $view = '', $type = '' ) {
   global $form_message;

   if ( !empty( $str_code ))
      $form_message -> add ($str_code, $view, $type );
}

//===================================================
// Вывести на странице сообщение или ошибку
// если в display_message передан $str_code, сообщение или ошибка будет отображена сразу же
// $str_code - код ошибки или сообщения в виде строки
// $view     - подробное описание сообщения или ошибки, для отображения на странице
// $type     - тип сообщения, для ошибок должен быть error
// display_message ('filed_to_get_data', __( "Failed to get data!", 'wp-add-function' ), 'error' );
// display_message() - что бы отобразить все ошибки которые добавлены
function display_message( $str_code = '', $view = '', $type = '' ) {
   global $form_message;

   if ( !empty( $str_code ))
      add_message($str_code, $view, $type );

   if ( $form_message -> get_error_code() ) {
      foreach( $form_message -> get_error_codes() as $error_code ){
         $message = $form_message -> get_error_message($error_code);
         $data    = $form_message -> get_error_data($error_code);
         if ( $data == 'error' )
            echo '<p>
                     <font color=" $color_all["red"]">
                        <u><b><strong>'. __( "Error: ", 'wp-add-function' ) . '</strong>'. $message . '</b></u>
                     </font>
                  </p>';
         else
            echo '<div>'. $message .'</div>';
        }
        exit;
   }
}

//===================================================
// Выводит разные типы кнопок в поле для class-wp-list-table
// $name_id - Имя поля ID (пример: objectId)
// $perm    - Права
function display_column_button( $this_column, $item, $column_name, $buttons, $name_id, $perm = '' ){
   global $gl_, $action, $color, $paged;

   // Если разрешение не указано используем read
   if ( empty ( $perm ))
      $perm = 'read';

   // Если есть то получим значение ID
   $item_id      = isset( $_REQUEST[$name_id] ) ? wp_unslash( trim( $_REQUEST[$name_id] )) : '';
   $paged        = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;
   $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
   $actions = array();
   if ( count($buttons) > 0 )
      foreach ($buttons as $name ) {
         if ( $name == 'filter_s') {
            if (!empty( $item_id ))
               $actions[$name] = sprintf( '<a href="?page=%s&'.$name_id.'=%s&paged=%s&s=%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $_REQUEST['page'], $item_id, $paged, $item[ $column_name ] );
            else
               $actions[$name] = sprintf( '<a href="?page=%s&paged=%s&s=%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $_REQUEST['page'], $paged, $item[ $column_name ]);
         } elseif ( $name == 'history' )
               $actions[$name] = sprintf( '<a href="?page=%s&action=%s&paged=%s&'.$name_id.'=%s">' . __( 'History', 'wp-add-function' ) . '</a>', $_REQUEST['page'], 'history', $paged, $item[ $name_id ] );
         else
            if ( current_user_can( $perm ))
               if ( $name == 'cancel-deletion')
                  $actions[$name] = sprintf('<a href="?page=%s&paged=%s&action=%s&'.$name_id.'=%s">' . __( 'Cancel delete', 'wp-add-function' ) . '</a>', $_REQUEST['page'], $paged, $name, $item[$name_id]);
               else
                  $actions[$name] = sprintf('<a href="?page=%s&paged=%s&action=%s&'.$name_id.'=%s">' . __( str_replace('-', ' ', ucfirst($name)), $gl_['plugin_name'] ) . '</a>', $_REQUEST['page'], $paged, $name, $item[$name_id]);

         }
   return sprintf('%1$s %2$s', $column_value, $this_column -> row_actions($actions) );
}

//===================================================
// Функция формирующая в указанном поле кнопку фильтр для class-wp-list-table
// $this_table  - переменная с сылкой на объект class-wp-list-table
// $item        - массив с структурой и значениями выделенной строки строки таблицы
// $column_name - имя выбранного поля таблицы
// $column_db   - имя поля таблицы базы данных
// $page        - имя страницы на которую переходим (если не выбрано то текущая)
function column_button_filter( $this_table, $item, $column_name, $column_db, $page = '' ){
   global $color;

   $pagep        = isset( $_REQUEST['paged'] )    ? wp_unslash( trim( $_REQUEST['paged'] )) : '';

   if ( empty( $page ))
      $page = $this_table -> page;
// => [page] 
  // $column_name  = 'cardId';
   //$column_value = '<font color="'. $color .'">' . $item[ 'purseDiscountCount' ] . '</font>';
//       $actions = array(
//                'view'   => sprintf('<a href="?page=%s&pagep=%s&f=%s&v=%s">' . __( 'View', 'card-manager' ) . '</a>', 'cm-purses-discount', $pagep, $column_name, $item['objectId']),
//                'add'    => sprintf('<a href="?page=%s&pagep=%s&action=%s&f=%s&v=%s">' . __( 'Add', 'card-manager' ) . '</a>', 'cm-purses-discount', $pagep, 'new', $column_name, $item[ 'objectId' ] ),
//                );
//       return sprintf('%1$s %2$s', $column_value, $this -> row_actions( $actions ) );
   //print_r( $column_db ); exit;
   // если нет значения
   //if ( $item[ $column_name ] = '')
//      return '';
   $column_value = '<font color="'. $color . '">' . $item[ $column_name ] . '</font>';
   $actions      = array( 'filter' => sprintf('<a href="?page=%s&f=%s&v=%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $page, $column_db, $item[ $column_db ]));
   return sprintf('%1$s %2$s', $column_value, $this_table -> row_actions($actions) );
}

//===================================================
// Функция отображения поля default в class-wp-list-table
function display_column_default( $item, $column_name ){
   global $color, $color_all;

   $color_old = $color;

   if ( !empty( $item[ $column_name ]))
      if ( $item[ $column_name ][0] == '-' )
         $color = $color_all['red'];

   if ( stripos ( $column_name, 'mail') != false )
        $column_value = ' <em><a href="mailto:' . $item[ $column_name ] . '"> '. $item[ $column_name ] . ' </a></em>';
   else
        $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
        $color = $color_old;
        switch( $column_name ) {
        default:
        return $column_value;
   // case 'id':
   //    return print_r( $item, true );
   }
}

//===================================================
// Функция отображения поля class-wp-list-table в виде картинки
function display_column_picture( $item, $column_name, $picture ){
   global $gl_;

   $column_value = '<img src="' . WP_PLUGIN_URL . '/' . $gl_['plugin_name'] . '/images/' . $picture . '-16x16.png' . '"name="picture_title" align="top" hspace="2" width="16" height="16" border="2"/>';

   return $column_value;
}

//===========================================
// Добавим select2
add_action( 'admin_enqueue_scripts', function() {
   //Our own JS file
   wp_register_script( 'select_search', WPMU_PLUGIN_URL . '/wp-add-function/js/jquery.js', array( 'jquery' ), 1.9, false );
   wp_enqueue_script( 'select_search' );

   //Select2 JS
   wp_register_script( 'select2_js', WPMU_PLUGIN_URL . '/wp-add-function/js/select2.min.js', array( 'jquery' ), 4.0, false );
   wp_enqueue_script( 'select2_js' );

   //Select2 CSS
   wp_register_style( 'select2_css', WPMU_PLUGIN_URL . '/wp-add-function/css/select2.min.css' );
   wp_enqueue_style( 'select2_css' );

}, -100 );

//=============================================
// Изменим стиль админки
add_action( 'admin_head', function() {
   // внешний вид формы
   echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/forms.css' .  '">';
   // внешний вид таблицы
   echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/common.css' . '">';
});


//=============================================
// Скрыть уведомление об обновлении WordPress с панели администрирования для обычных пользователей.
add_action( 'admin_init', function () {
   //if ( !current_user_can('update_core') ) {
      remove_action( 'admin_notices',         'update_nag', 3 );
      remove_action( 'network_admin_notices', 'update_nag', 3 );
   //}
});

?>

