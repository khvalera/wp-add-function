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

?>
