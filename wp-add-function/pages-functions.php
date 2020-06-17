<?php

// функции для работы с страницами

//====================================
// Форма журнала документов
// $name            - Имя формы (пример: journal)
// $class_table     - Имя класса таблицы
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
      elseif ( $action == 'new' or $action == 'new1' ){
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
   global $gl_, $new_objectid;

   // Получим текущую страницу (вместе с префиксом)
   $page    = get_page_name();

   // Зафиксируем текущий paged, для дальнейшего возврата
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
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }
   // ->>> на скорую руку, нужно исправить
   // Обработаем нажатие Save New
   $POST_SAVE_NEW = isset( $_POST['button_new_save'] );
   if ( $pages == 'issuing-discount-cards' ){
      if ( ! empty( $POST_SAVE_NEW )) {
         if ( save_new_data() != 1 )
            if ( ! empty( $pages ))
               wp_redirect( get_admin_url( null, 'admin.php?page=' . $pages . '&holder-objectId=' . $new_objectid ));
            else
               wp_redirect( esc_url( get_admin_url( null, 'admin.php?page=' . $page )));
      }
   } else{
      if ( ! empty( $POST_SAVE_NEW )) {
         $pagep  =  isset( $_REQUEST['pagep'] ) ? wp_unslash( trim( $_REQUEST['pagep'] )) : '';
         save_new_data();
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
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // Обработаем нажатие кнопки Cancel Delete
   $POST_CANCEL_DELETE = isset( $_POST['button_apply'] );
   if ( ! empty( $POST_CANCEL_DELETE )) {
      delete_form_data();
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
// $display_name  - Отображаемое имя реквизита на форме (можно указывать несколько, через запятую)
// $type          - Тип реквизита (number, text, date, time...) (можно указывать несколько, через запятую)
// $name          - Имя input (можно указывать несколько, через запятую)
// $value         - Значение (можно указывать несколько, через запятую)
// $extra_options - Дополнительные параметры, стиль тут тоже указывается style="width:352px;" (можно указывать несколько, через запятую)
// $not_tfield    - Если равно true не использовать tfield
function html_input( $display_name, $type, $name, $value='', $extra_options = '', $not_tfield = '' ) {
   // Преобразуем строку с пробелами в массив
   $array_display_name  = explode( ",", $display_name );
   $array_type          = explode( ",", $type );
   $array_name          = explode( ",", $name );
   $array_value         = explode( ",", $value );
   $array_extra_options = explode( ",", $extra_options );

   // Проверим что бы переданное количество значений совпадало 
   if ( count ( $array_type ) <> count ( $array_name ) or count ( $array_name ) <> count ( $array_display_name )) {
      display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
      exit;
   }
   if ( ! empty( $value ) )
      if ( count ( $array_name ) <> count ( $array_value )) {
         display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
         exit;
   }
   if ( ! empty( $style ) )
      if ( count ( $array_name ) <> count ( $extra_options )) {
         display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'operative-accounting' ), 'error');
         exit;
   }

   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $array_display_name[0]; ?></th>
         <td>
            <?php
               foreach ( $array_name as $key => $_name ) {
                  $_display_name = $array_display_name[$key];
                  $_type         = $array_type[$key];
                  // если стиль не указан
                  if ( ! empty( $extra_options ) )
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
                        <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> >
                     <?php
                  } else{
                    ?>
                       <b style="margin-left: 6px;"><?php echo $_display_name ?></b>
                       <input type="<?php echo $_type ?>" name="<?php echo $_name ?>" id="<?php echo $_name ?>" value="<?php echo $_value ?>" <?php echo $_extra_options ?> >
                     <?php
                  }
               }
            ?>
         </td>
      </tr>
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
// $item_name     - имя класса к которому привязываются данные из mysql (таблица базы данных)
// $name          - имя объекта на форме
// $extra_options - дополнительные параметры (тут указывается стиль тоже: style="width:352px;")
// $value_id      - id выбранной позиции
// $value_name    - имя поля из таблицы базы данных для добавления как name (по умолчанию name)
function html_select2( $display_name, $item_name, $name, $extra_options = '', $value_id = '', $value_name = '', $php_file = '' ) {
   global $gl_;

   // Добавим 'tfield-' если в имени его нет
   if ( strpos( $name, 'tfield-' ) === false )
      $name = 'tfield-' . $name;

   if ( ! empty( $value_id )) {
      $data = get_row_table_id($item_name,'', $value_id);
      if ( empty( $value_name ))
         $value_name = 'name';
      //print_r($data); exit;
   }
   // если стиль не указан используем width:352px;
   if ( stripos($extra_options, 'style') == false )
      $extra_options = $extra_options . ' style="width:352px;" ';
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $display_name; ?></th>
            <td>
               <select class="<?php echo 'item_' . $item_name; ?> form-control" name="<?php echo $name; ?>" <?php echo $extra_options ; ?> >
                  <?php
                    // Получим нужную строку таблицы по $id в виде массива
                    if ( ! empty( $data )) {
                       echo "<option selected value=" . $data['id'] . ">" . $data[$value_name] . "</option>";
                    }
                  ?>
               </select>
            </td>
       </tr>
    <?php

   // если $php_file не указан используем ajax.php
   if ( empty( $php_file ) )
      $ajax_php   = WP_PLUGIN_URL . '/'. $gl_['plugin_name'] . '/includes/' . $item_name . '/ajax.php';
   else
      $ajax_php   = WP_PLUGIN_URL . '/'. $gl_['plugin_name'] . '/includes/' . $php_file;
   //exit(_e($ajax_php));
   java_item($item_name, $ajax_php);
}
//===================================================
function java_item($item_name, $ajax_php = ''  ){

   $place_item = __( 'Select value', 'wp-add-function' );
   $class_name = '.item_' . $item_name;
   ?>
   <script type="text/javascript">

      var class_name = '<?php echo $class_name; ?>';
      var place_item = '<?php echo $place_item; ?>';
      var ajax_php   = '<?php echo $ajax_php; ?>';

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
                 var data1 = data

               return {
                 results: data
               };
             },
             cache: true
           }
      });
      // обработка выбора значения
      $(class_name).on("select2:select", function(e) {
         //console.log(e); // весь объект
         //console.log(e.params.data); // вот тут обычно полезные данные
      });

      // Если нужно выбрать значение (пока не разобрался)
      //$(class_name).select2("trigger", "select", { data: { id: "5", text: '!!!' }});
      // var newOption = new Option(text, 0, false, false);
      //$(class_name).append(newOption).trigger('change');
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
       // Дабавим пункт меню стравочника в верхнюю панель
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
// Вывести сообщения
// для ошибок data должно быть error
// $form_message -> add ('filed_to_get_data', __( "Failed to get data!", 'wp-add-function' ), 'error' );
function display_message( $str_code = '', $view = '', $type = '' ) {
   global $form_message;

   if ( !empty( $str_code ))
      $form_message -> add ($str_code, $view, $type );

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
// Заменена на display_column_button (СТАТАЯ)
function column_button_filter( $this_column, $item, $column_name ){
   global $color;

   if ( $item[ $column_name ] = '')
      return '';
   $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
   $actions = array('filter_s' => sprintf('<a href="?page=%s&s=%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $_REQUEST['page'], $item[ $column_name ] ));
   return sprintf('%1$s %2$s', $column_value, $this_column -> row_actions($actions) );
}

//===================================================
// Функция отображения поля default в class-wp-list-table
function display_column_default( $item, $column_name ){
   global $color;

   if ( stripos ( $column_name, 'mail') != false )
        $column_value = ' <em><a href="mailto:' . $item[ $column_name ] . '"> '. $item[ $column_name ] . ' </a></em>';
   else
        $column_value = '<font color="'. $color .'">' . $item[ $column_name ] . '</font>';
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

?>
