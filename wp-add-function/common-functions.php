<?php

// Общие функции

global $color_all;

$color_all = array(
   'red' => '#ce181e',
   'light_brown' => '#b47804'
);

//===========================================
function current_date_time($format = '' ){
   if ( empty( $format ))
      $format = "Y/m/j H:i:s";
   // Получим время
   $timezone  = get_option('gmt_offset');
   $today = gmdate( $format, time() + 3600 * ($timezone + date("I")));
   return $today;
}

//===========================================
// Функция удаляет из admin_title "---Wordpress"
function admin_title($admin_title, $title) {
  return get_bloginfo('name').' &bull; '.$title;
}

//=============================================
// Закрыть сайт для незарегистрированных посетителей
function only_registered_func() {
   if ( ! is_user_logged_in()) {
     auth_redirect();
   }
}

//===========================================
// Нажата кнопка загрузить картинку
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

//===========================================
// Удаление метабоксов из консоли сайта
function clear_wp_dash(){
    $dash_side   = & $GLOBALS['wp_meta_boxes']['dashboard']['side']['core'];
    $dash_normal = & $GLOBALS['wp_meta_boxes']['dashboard']['normal']['core'];
    // Быстрая публикация
    unset( $dash_side['dashboard_quick_press'] );
    // Последние черновики
    unset( $dash_side['dashboard_recent_drafts'] );
    // Блог WordPress
    unset( $dash_side['dashboard_primary'] );
    // Другие Новости WordPress
    unset( $dash_side['dashboard_secondary'] );
    // Входящие ссылки
    unset( $dash_normal['dashboard_incoming_links'] );
    // Прямо сейчас
    unset( $dash_normal['dashboard_right_now'] );
    // Последние комментарии
    unset( $dash_normal['dashboard_recent_comments'] );
    // Последние Плагины
    unset( $dash_normal['dashboard_plugins'] );
    // Активность
    unset( $dash_normal['dashboard_activity'] );

    remove_action( 'welcome_panel', 'wp_welcome_panel' );
}

//===================================================
// Удаление пунктов меню из верхней панели
function wp_new_toolbar() {
   global $wp_admin_bar;

   // меню "комментарии"
   $wp_admin_bar -> remove_menu('comments');
   // меню "мой профиль"
   //$wp_admin_bar->remove_menu('my-account');
   $wp_admin_bar -> remove_menu('edit'); //меню "редактировать запись"
   //меню "добавить"
   $wp_admin_bar -> remove_menu('new-content');
   //меню "обновления"
   //$wp_admin_bar->remove_menu('updates');
   $wp_admin_bar -> remove_menu('wp-logo'); //меню "о wordpress"
   //меню "сайт"
   //$wp_admin_bar->remove_menu('site-name');
}

//=============================================
// Скрыть ненужные поля в профиле пользователя
function remove_user_profile_fields_with_css() {
   $fieldsToHide = [
       'rich-editing',
       //'admin-color',
       'comment-shortcuts',
       'admin-bar-front',
       'user-login',
       'role',
       'super-admin',
       //'first-name',
       //'last-name',
       'nickname',
       'display-name',
       //'email',
       'description',
       //'pass1',
       //'pass2',
       'sessions',
       'capabilities',
       'syntax-highlighting',
       'url'
       ];

   // add the CSS
   foreach ($fieldsToHide as $fieldToHide) {
      echo '<style>tr.user-' . $fieldToHide . '-wrap{ display: none; }</style>';
   }
  // fields that don't follow the wrapper naming convention
  echo '<style>tr.user-profile-picture{ display: none; }</style>';
  // all subheadings
  echo '<style>#your-profile h2{ display: none; }</style>';
}

//=============================================
// функция добавляет картинку в title меню
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

?>
