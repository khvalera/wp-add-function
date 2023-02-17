<?php

// функции для работы с страницами

// Создаем экземпляр для оброботки ошибок
global $form_message;
$form_message = new WP_Error;

// Переменные используемые при работе с страницами:
// $action - вариант открываемой формы (выполняемого действия):
//           new,new1        - форма создания нового элемента или документа
//           edit            - форма редактирования выбранного элемента или документа
//           delete          - пометка на удаление выбранного элемента или документа
//           cancel-deletion - отмена пометки на удаление выбранного элемента или документа
//           history         - форма отображения истории выбранного элемента или документа
//           filter-deletion - форма для отображения помеченных для удаления элементов или документов
//
// $page   - страница которую нужно открыть
// $paged  - (paginated) номер страницы пагинации $page
//
// $p (parent) - станица родитель, используется для дальнейшего возврата
// $n (numbered) - это paged для $p (номер страницы пагинации, используется для дальнейшего возврата на родительскую страницу)
//
// $search_results (s) - отображение поиска ? нужно пересмотреть!!!
//
// $f (filter или field) - имя поля таблицы $page или нескольких полей через разделитель |, для поля на форме или запроса фильтраци
// $v (filter value)     - значение для поля ($filter) или нескольких полей через разделитель |, для поля на форме или запроса фильтраци

//===========================================
// Функция подготавливает часть URL-запроса для передачи
// параметров в открываемую форму страницы. (аналог http_build_query)
// parameters   - массив с передаваемыми параметрами и значениями.
//                пример: array('id'=>1, 'name'=> 'test', 'description' => '')
// return_value - параметр или параметры которые форма (страница) должна вернуть (не обязателен).
//                пример: array('id', 'name')
// key_name     - имя массива которое потом можно открыть с помощью $_GET[$array_name](не обязателен, по умолчанию field).
// first_char   - первый знак в запросе (по умолчанию &)
function http_values_query( $parameters, $return_value = '', $key_name = 'field', $first_char = '&'){
    if ( ! empty( $return_value ))
       $data = array($key_name => $parameters, 'return_value' => $return_value);
    else
       $data = array($key_name => $parameters);

    return empty(http_build_query($data)) ? '' : $first_char . http_build_query($data);
}

//===========================================
// Функция возвращает массив значений созданных с помощью http_values_query.
// field    - вернуть определенное значение (если не указано возвращает все значения ввиде массива)
// key_name - имя массива которое потом можно открыть с помощью $_GET[$key_name](не обязателен, по умолчания field).
function get_http_values( $field = '', $key_name = 'field'){
   $array_fields = isset( $_GET[$key_name] ) ? $_GET[$key_name] : array();
   // если нужно вернуть только одно значение
   if ( ! empty( $field )){
      if ( array_key_exists( $field, $array_fields ))
         return $array_fields[$field];
      else
         return '';
   } else
      return $array_fields;
}

//=============================================
// Функция отображает массив фильтра в виде строки
function filter_str( $array_filter ) {

   $str_filter = "";
   foreach ( $array_filter as $field => $value ) {
      if (! empty( $value )) {
         // если в имени поля первый знак *, то не используем таблицу
         if ( $field[0] == "*"){
            if ( !empty($str_filter))
               $str_filter =  $str_filter . " ";
            $str_filter =  $str_filter . substr($field, 1 ) . " = " . $value;
         // если есть точка, значит с полем указана таблица
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
// Функция возвращает значение которое должна вернуть форма.
// Предварительно массив создается с помощью http_values_query.
function get_http_return_value(){
   $array_return_value = isset( $_GET["return_value"] ) ? $_GET["return_value"] : array();
   return $array_return_value;
}

//===========================================
// Запись настроек пользователя на странице (количество строк на странице и тд.)
add_filter( 'set-screen-option', function( $status, $option, $value ){
   return $value;
}, 10, 3 );

//====================================
// Функция отображения простой кнопки
function button_action( string $text = null, string $name = 'submit', string $type = 'submit' ){
  ?>
     <button type="<?php echo $type;?>" id="<?php echo $name;?>" name="<?php echo $name;?>" class="page-title-action"><?php echo $text;?></button>
  <?php
}

//====================================
// Класс для создания кнопки с текстом (используется совместно с class_dialogue_form)
// button_text      - текст на кнопке
// button_title     - всплывающая подсказка
// label_text       - текст метки перед кнопкой
// link_page        - адрес документа, на который следует перейти
// current_user_can - права пользователя
class class_href_text_button {
     public $button_text;
     public $button_title;
     public $link_page;
     public $label_text;
     public $current_user_can;

    //====================================
    function __construct( $button_text = '', $link_page = '', $button_title = '', $label_text = '', $current_user_can = '' ){
       $this -> button_text      = $button_text;
       $this -> button_title     = $button_title;
       $this -> link_page        = $link_page;
       $this -> current_user_can = $current_user_can;
       $this -> label_text       = $label_text;
       $this -> display();
    }

    //====================================
    // отображение
    public function display(){
       ?>
          <tr class="rich-editing-wrap">
             <th scope="row"><?php echo $this -> label_text; ?></th>
                <td>
                   <?php
                      $class_href_button = new class_href_button( $this -> button_text, $this -> link_page, $this -> button_title, $this -> current_user_can );
                   ?>
                </td>
          </tr>
      <?php
   }
}

//====================================
// Класс используется для создания кнопки с использованием href
// text             - текст на кнопке
// title            - всплывающая подсказка
// link_page        - адрес документа, на который следует перейти
// current_user_can - права пользователя
class class_href_button {
     public $text;
     public $title;
     public $link_page;
     public $current_user_can;

    //====================================
    function __construct( $text = '', $link_page = '', $title = '', $current_user_can = '' ){
       $this -> text             = $text;
       $this -> title            = $title;
       $this -> link_page        = $link_page;
       $this -> current_user_can = $current_user_can;
       $this -> display();
    }

    //====================================
    // отображение кнопки
    public function button(){
       ?>
           <div class="wrap">
              <a href="<?php
                         echo $this -> link_page;
                      ?>" title="<?php echo $this -> title; ?>" class="page-title-action">
                      <?php echo $this -> text; ?>
              </a>
        </div>
       <?php
    }

    //====================================
    public function display(){
       // если не установлены права пользователя
       if ( empty( $this -> current_user_can ))
          $this -> button();
       else
          if ( current_user_can( $this -> current_user_can ))
             $this -> button();
    }
}

//====================================
// Класс для создания диалоговой формы
// description1_font_size и description2_font_size - размер шрифта для текста описаний
class class_dialogue_form {
    // глобальные переменные
    // включить или отключить описания
    public $display_description = false;
    public $display_controls = false;
    // переменные описания
    public $plugin_name, $image_file, $description_text1, $description_text2, $description1_font_size, $description2_font_size ;
    // переменные элементы управления
    public $item_controls;

    //====================================
    function __construct(){
    }

    //====================================
    // plugin_name - имя плагина
    // image_file  - путь к файлу с изображением относительно каталога плагина
    // description_text1 - описание №1
    // description_text2 - описание №2
    public function description($plugin_name, $image_file, $description_text1 = '', $description_text2 = '' ){
       if ( empty( $this -> description1_font_size ))
          $this -> description1_font_size = 'h3';
       if ( empty( $this -> description2_font_size ))
          $this -> description2_font_size = 'h4';

       ?>
          <div class="wrap">
             <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px; width:550px;">
               <p>
                  <table class="wpuf-table">
                     <th>
                        <?php echo '<img src="' . WP_PLUGIN_URL . '/' . $plugin_name . $image_file . '"name="picture_title"
                        align="top" hspace="2" width="48" height="48" border="2"/>'; ?>
                     </th>
                     <td>
                        <p>
                           <?php echo '<' . $this -> description1_font_size . '>'; ?>
                              <?php echo $description_text1; ?>
                           <?php echo '</' . $this -> description1_font_size . '>'; ?>
                        </p>
                        <p>
                           <?php echo '<' . $this -> description2_font_size . '>'; ?>
                              <?php echo $description_text2; ?>
                           <?php echo '</' . $this -> description2_font_size . '>'; ?>
                        </p>
                     </td>
                  </table>
             </div>
          </div>
       <?php
    }

    //====================================
    public function controls(){
       ?>
          <div class="wrap">
             <div style="background:#ECECEC;border:1px solid #CCC;padding:0 10px;margin-top:5px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px; width:550px;">
               <p>
                   <table class="form-table">
                      <?php call_user_func( $this -> item_controls); ?>
                   </table>
                </p>
             </div>
          </div>
       <?php
    }

    //====================================
    // функция которая выполняется при создании класса
    // отображение формы
    public function form_display(){
       ?>
          <form action="" method="post">
             <div class="wrap">
                <h2>
                   <?php echo $this -> header_text; ?>
                </h2>
             </div>
             <?php
             // если нужно вывести description
             if ( $this -> display_description )
                $this -> description($this -> plugin_name, $this -> image_file, $this -> description_text1, $this -> description_text2);
             // если нужно вывести controls
             if ( $this -> display_controls )
                $this -> controls();
             ?>
             <!- это закрытие form из form_header: -->
             <p>
                <?php call_user_func( $this -> footer_button); ?>
             </p>
          </form>
          <!- почему то не закрыт div id="wpbody-content" нужно разобраться -->
          </div>
       <?php
    }
}

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

   // Зафиксируем текущий paged, (номер страницы пагинации), для дальнейшего возврата
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
// Відобразити кнопки на формі
// $buttons - задается в виде массива, пример: array('new' => 'New item', 'new1' => 'New item 1')
function display_form_buttons($buttons = array(), $perm_button, $page ){
  global $gl_;

  // Зафиксируем текущий paged, (номер страницы пагинации)
  $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

  // parent - страница родитель для дальнейшего возврата
  $parent    = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

  // это paged для $p (номер страницы пагинации, используется для дальнейшего возврата на родительскую страницу)
  $numbered  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;

  $n = 0;
  if ( current_user_can( $perm_button )){
     foreach ($buttons as $button_action => $button_name) {
        ?>
           <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button_action);?>" class="page-title-action">
              <?php echo _e($button_name, $gl_['plugin_name'] );?>
           </a>
        <?php
        $n++;
     }
     // если пустое значение $buttons
     if (empty($buttons)){
        $button_action = 'new';
        $button_name   = __('New item', "wp-add-function" );
          ?> <a href="<?php echo sprintf('?page=%s&paged=%s&action=%s', $page, $paged, $button_action);?>" class="page-title-action">
                <?php echo _e($button_name, $gl_['plugin_name'] );?>
             </a>
          <?php
     }
  }
  // если есть страница родитель, выводим кнопку для возврата
  if (! empty($parent)){
     ?> <a href="<?php echo sprintf('?page=%s&paged=%s', $parent, $numbered );?>" class="page-title-action">
           <?php echo _e( 'Return', 'wp-add-function' ); ?>
        </a>
     <?php
  }
  //print_r($buttons );
}

//====================================
// Форма списка справочника
// $name            - Имя формы (пример: users)
// $gl_['class-table']     - Имя класса таблицы
// $perm_button     - Права на кнопки
// $title           - Заглавие
// $description1    - Описание 1
// $description2    - Описание 2
// $buttons         - Задается в виде массива, пример: array('new' => 'New item', 'new1' => 'New item 1')
// $search_box_name - Имя кнопки поиска
function form_directory( $name, $class_table, $perm_button, $title, $description1, $description2 = '', $buttons = array(), $search_box_name = '' ) {
   global $gl_;

   if ( $search_box_name == '' ) {
      $search_box_name = __( "Search", "wp-add-function" );
   }

   $search_results = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

   $action         = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

   // Получим $page из $class_table
   $page   = $class_table -> page;

   $class_table -> prepare_items();

   ?>
   <div class="wrap">
   <div id="icon-users" class="icon32"><br/></div>
       <h2>
          <?php echo $title; ?>
          <?php
             display_form_buttons($buttons, $perm_button, $page );
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
                if ( ! empty( $class_table -> filter )){
                   $filter_str = filter_str( $class_table -> filter );
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
// Форма списка с историей
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

   // текущая страница
   $page  = get_page_name( $gl_['prefix'] );

   // страница родитель на которую возвращаемся
   $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';

   // это paged для $parent (номер страницы пагинации, используется для дальнейшего возврата на родительскую страницу)
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

   // получим текущую страницу (вместе с префиксом)
   $page   = get_page_name();
   // получим номер страницы пагинации
   $paged  = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

   // parent - страница родитель для дальнейшего возврата
   $parent = isset( $_REQUEST['p'] ) ? wp_unslash( trim( $_REQUEST['p'] )) : '';
   // это paged для $parent (номер страницы пагинации, используется для дальнейшего возврата на родительскую страницу)
   $parent_n  = isset($_REQUEST['n']) ? max(0, intval($_REQUEST['n'] )) : 1;
   // если использовался фильтр, используем его
   $link_filter = http_values_query(get_http_values( '', 'f'), '', 'f');

   // обработаем нажатие кнопки применить для периода в журнале документов
   $POST_PERIOD = isset( $_POST['button_period'] );
   if ( ! empty( $POST_PERIOD )) {
      // Заполним в массив данные значений полей формы
      [$data_field, $data_table] = post_array('pdate');

      // Запишем даты журнала
      // Получим id пользователя WP
      $user_id = get_current_user_id();
      if( ! update_user_meta( $user_id, str_replace('-','_', $page) . '_date1', $data_field['date1'] ) ){
        add_message('insufficient_permission', sprintf(__( "Failed to update meta field for user %s", 'card-manager' ), "date1"), 'error');
      }
      if( ! update_user_meta( $user_id, str_replace('-','_', $page) . '_date2', $data_field['date2'] ) ){
        add_message('insufficient_permission', sprintf(__( "Failed to update meta field for user %s", 'card-manager' ), "date2"), 'error');
      }
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged ));
   }

   // обработаем нажатие кнопки фильтр для справочника
   $POST_FILTER = isset( $_POST['button_filter'] );
   if ( ! empty( $POST_FILTER )) {
      // Заполним в массив данные значений полей формы
      [$data_field, $data_table] = post_array();

      // cоздадим часть ссылки
      $link_field = http_values_query( $data_field, '', 'f');

      $link_table = http_values_query( $data_table, '', 't');

      wp_redirect( get_admin_url( null, 'admin.php?page=' . $page . $link_field . $link_table));
   }

   // обработаем нажатие кнопки Save
   $POST_SAVE = isset( $_POST['button_save'] );
   if ( ! empty( $POST_SAVE )) {
      save_edit_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged . $link_filter ));
   }

   // обработаем нажатие кнопки Save New
   $POST_SAVE_NEW = isset( $_POST['button_new_save'] );
   if ( ! empty( $POST_SAVE_NEW )) {
      // подготовим ссылку
      // если указан $parent используем его в противном случае $page
      if ( empty( $parent )){
         $link_page = "?page=" . $page;
         $link_paged  = isset($_REQUEST['paged']) ? "&paged=" . wp_unslash( trim( $_REQUEST['paged'])) : '';
         $link_action = '';
      } else {
         $link_page   = "?page=" . $parent;
         $link_paged  = isset($_REQUEST['n']) ? "&paged=" . wp_unslash( trim( $_REQUEST['n'])) : '';
         $link_action = isset($_REQUEST['a']) ? "&action=" . wp_unslash( trim( $_REQUEST['a'])) : '';
      }

      if ( save_new_data() != 1 )
         // Если есть ошибки или сообщения покажем все
         display_message();

      // получим все значения переданные раньше
      $fields_values = get_http_values();

     // получим имя поля для возврата в parent
     $return_field = get_http_return_value();
     if (! empty( $return_field )){
        $fields_values[$return_field] = $gl_[$return_field];

        // cоздадим часть ссылки
        $link_values = http_values_query( $fields_values );
      } else $link_values = "";

      // если нужно вернуться на страницу родитель
      wp_redirect( get_admin_url( null, 'admin.php' . $link_page . $link_paged . $link_action . $link_values . $link_filter));
   }

   // обработаем нажатие кнопки Сancel
   $POST_CANCEL = isset( $_POST['button_cancel'] );
   if ( ! empty( $POST_CANCEL )) {
      // подготовим ссылку
      // если указан $parent используем его в противном случае $page
      if ( empty( $parent )){
         $link_page = "?page=" . $page;
         $link_paged  = isset($_REQUEST['paged']) ? "&paged=" . wp_unslash( trim( $_REQUEST['paged'])) : '';
         $link_action = '';
         // сдалаем исключение для некоторых action
         //if ( isset($_REQUEST['action']) ){
        //    if (( $_REQUEST['action'] == "new") or ( $_REQUEST['action'] == "new1"))
        //       $link_action = '';
        // }
      } else {
         $link_page   = "?page=" . $parent;
         $link_paged  = isset($_REQUEST['n']) ? "&paged=" . wp_unslash( trim( $_REQUEST['n'])) : '';
         $link_action = isset($_REQUEST['a']) ? "&action=" . wp_unslash( trim( $_REQUEST['a'])) : '';
      }

      // получим все значения переданные раньше
      $fields_values = get_http_values();
      // cоздадим часть ссылки
      $link_values = http_values_query( $fields_values );

      // если нужно вернуться на страницу родитель
      wp_redirect( get_admin_url( null, 'admin.php' . $link_page . $link_paged . $link_action . $link_values . $link_filter));
   }

   // обработаем нажатие кнопки Delete
   $POST_DELETE = isset( $_POST['button_delete'] );
   if ( ! empty( $POST_DELETE )) {
      delete_form_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged . $link_filter));
   }

   // Обработаем нажатие кнопки Cancel Delete
   $POST_CANCEL_DELETE = isset( $_POST['button_apply'] );
   if ( ! empty( $POST_CANCEL_DELETE )) {
      delete_form_data();
      // Если есть ошибки или сообщения покажем все
      display_message();
      wp_redirect(get_admin_url(null, 'admin.php?page=' . $page . '&paged=' . $paged . $link_filter));
   }

   // Если есть ошибки или сообщения покажем все
   display_message();
}

//===========================================
// Функция перебирает все поля на форме с префиксами и записывает в два массива для дальнейшей записи в базу данных
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
// Функция отображения кнопки
// display_name - Отображаемое имя кнопки на форме
// link_page    - Cсылка на страницу
// style        - Стиль
// class        - Класс
function button_html($display_name, $link_page, $style = '', $class = 'page-title-action' ){
   ?>
      <a href="<?php echo $link_page;?>" class="<?php echo $class;?>">
         <?php echo $display_name;?>
      </a>
   <?php
}

//===================================================
// Служить для відображення одинарного поля input (якщо потрібно вивести кілька значень input використовуйте html_input_multi).
// $display_name  - Отображаемое имя реквизита на форме
// $type          - Тип реквизита (number, text, date, time...)
// $name          - Имя input
// $value         - Значение
// $extra_options - Дополнительные параметры, стиль тут тоже указывается style="width:352px;"
// $onchange      - Название функции, выполняется после изменения значения элемента формы, когда это изменение зафиксировано.
// $not_field    - Если равно true не использовать field
function html_input( $display_name, $type, $name, $value='', $extra_options = '', $onchange = '', $field = '' ) {
   if ( ! empty( $onchange ) ){
      $onchange = 'onchange="' . $onchange.'"';
      // Добавим javascript
      javascript_arithmetic_input();
   }
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $display_name; ?></th>
         <td>
            <?php

               if (! empty( $extra_options )) {
                  // Если не найдено style и size добавим style="width:350px; min-width: 100px;"
                  if (( strrpos($extra_options, "style=") === false ) and (strrpos($extra_options, "size=") === false))
                     $extra_options='style="width:350px; min-width: 100px;"' . $extra_options;
               } else
                  // Если пустой extra_options используем style="width:350px; min-width: 100px;"
                  $extra_options='style="width:350px; min-width: 100px;"';

               // если $_field неравно true
               if ( $field !== true ){
                  // если $field не пустое
                  if ( ! empty( $field ))
                     $name = $field . '-' . $name;
                  else
                     $name = 'field-' . $name;
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
// $display_name  - Отображаемое имя реквизита на форме , массив (можно указывать несколько значений), пример: array( date1 => "Период с:", date2 => "по:" )
// $type          - Тип реквизита (number, text, date, time...), массив (можно указывать несколько значений), пример: array( date1 => "date", date2 => "date" )
// $value         - Значение (можно указывать несколько, через |)
// $extra_options - Дополнительные параметры, стиль тут тоже указывается style="width:352px;" (можно указывать несколько, через |)
// $onchange      - Название функции, выполняется после изменения значения элемента формы, когда это изменение зафиксировано.
// $field         - Если равно true не использовать field, пример: array( date1 => false, date2 => true )
function html_input_multi( $display_name, $type, $value=array(), $extra_options = array(), $onchange = '', $field = array()) {

   // Проверим что бы переданное количество значений совпадало
   if ( count ( $display_name ) <> count ( $type ) or count ( $display_name ) <> count ( $type )) {
      display_message('number_of_values_function_incorrect', __( 'In the function "html_input" number of values is incorrect', 'wp-add-function'  ), 'error');
   }
   if ( ! empty( $onchange ) ){
      $onchange = 'onchange="' . $onchange.'"';
      // Добавим javascript
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
                     // Если не найдено style и size добавим style="width:350px; min-width: 100px;"
                     if (( strrpos($_extra_options, "style=") === false ) and (strrpos($_extra_options, "size=") === false))
                        $_extra_options='style="width:350px; min-width: 100px;"' . $_extra_options;
                  } else
                     // Если пустой extra_options используем style="width:350px; min-width: 100px;"
                     $_extra_options='style="width:350px; min-width: 100px;"';

                  // если $_field неравно true
                  if ( $_field !== true ){
                     // если $field не пустое
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
               // Добавим 'field-' если в имени его нет
               if ( strpos( $name, 'field-' ) === false )
                  $name = 'field-' . $name;
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
function html_select($display_name, $name, $array_data, $extra_options = '', $value_id = '', $id_field = '', $value_field = '', $not_field = '' ){
   // Добавим 'field-' если в имени его нет
   if ( $not_field != true )
      if ( strpos( $name, 'field-' ) === false )
         $name = 'field-' . $name;
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
// Преобразовать массив в строку
function array_to_string($arr){
   $str=''; $nom=0;
   foreach($arr as $n => $val) {
     if ( $nom > 0)
        $str=$str."__";
     $str="$str$n-$val";

     $nom++;
   }
   return $str;
}

//===================================================
// $display_name  - отображаемое имя реквизита
// $table_name    - имя таблицы базы данных
// $name          - имя объекта, строка или  массив состоящий из имени поля и таблицы базы данных. пример: array("objectId", "table")
// $extra_options - дополнительные параметры (тут тоже указывается стиль: style="width:352px;")
// $select_id     - id выбранной позиции
// $select_name   - строка или масив с именами полей из таблицы базы данных для добавления как name (по умолчанию name). пример: array("objectId", "holderName")
// $php_file      - путь к ajax файлу (не обязательно)
// $if_select     - имя поля для отбора, если не указано то используется objectId
// $params        - параметры для передачи в ajax_php (пример: "?f=objectId&v=1")
function html_select2( $display_name, $table_name, $name, $extra_options = '', $select_id = '', $select_name = '', $php_file = '', $if_select = '', $params = '', $not_field = false ) {
   global $gl_;

   if ( is_array( $name )){
      if ( ! empty( $name[1] ))
         $item_name = array_to_string(array('field' => $name[0], 'table' => $name[1]));
      else
         $item_name = array_to_string(array('field' => $name[0]));
   } else
   // Добавим 'field-' если в имени его нет
   if ( $not_field == true )
        $item_name = array_to_string(array('not_field' => $name));
   else {
      if ( strpos( $name, 'field-' ) === false )
         $item_name = array_to_string(array('field' => $name));
    }

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

   // если есть objectId будем использовать его
   if ( ! empty( $data['objectId'] ))
      $name_id = 'objectId';
   else
      $name_id = 'id';
   // если стиль не указан используем width:352px;
   if ( stripos($extra_options, 'style') == false )
      $extra_options = $extra_options . ' style="width:352px;" ';
   ?>
      <tr class="rich-editing-wrap">
         <th scope="row"><?php echo $display_name; ?></th>
            <td>
               <select class="<?php echo 'item_' . $item_name; ?> form-control" name="<?php echo $item_name; ?>" <?php echo $extra_options ; ?> >
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
   java_item($item_name, $ajax_php, $params);
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

//====================================
// Функция возвращает указанное значение фильтра по name
function request_filter_value( $name ){
   // получим значение фильтра (передается имя поля таблицы)
   $filter = isset( $_REQUEST['f'] ) ? wp_unslash( trim( $_REQUEST['f'] )) : '';

   // для фильтра получим значение value
   $filter_value = isset( $_REQUEST['v'] ) ? wp_unslash( trim( $_REQUEST['v'] )) : '';

   // если используется фильтр
   if ( ! empty( $filter ) and ! empty( $filter_value )){
      // преобразуем filter в массив
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
function add_admin_bar_menu($wp_admin_bar, $id, $image, $page, $nama_lang, $parent = '' ) {
   if ( $parent == '' ) {
       $wp_admin_bar -> add_menu( array(
      'id'    => $id,
      'title' => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
      'href'  => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
       ));
   } else {
       $wp_admin_bar -> add_menu( array(
       'parent' => $parent,           // параметр id из первой ссылки
       'id'     => $id,               // свой id, чтобы можно было добавить дочерние ссылки
       'title'  => admin_bar_menu_title_icon( plugins_url( $image ), $nama_lang),
       'href'   => esc_url(get_admin_url(null, 'admin.php?page=' . $page )),
       ));
   }
}

//===================================================
// Класс автоматизирует добавление дочерней страницы в меню
// которая будет использовать class_table
// $item_name_menu      - имя меню и страницы (без префикса)
// $item_Name_menu_lang - отображаемое имя страницы
// $current_user_can    - отображать страницу для пользователя с правами
// $plugin_name         - имя плагина
// $plugin_prefix       - префикс плагина
// $parent_page         - родительская страница (без префикса)
// $position            - где отображать меню в admin_bar или admin_menu
// $set_fields          - массив с полями настройки страницы (предусмотрено только per_page или layout_columns)
class add_admin_submenu_class_table {
    // объявление свойств
    public $item_name;
    public $item_Name_menu_lang;
    public $current_user_can;
    public $plugin_name;
    public $plugin_prefix;
    public $parent_page;
    public $page;
    public $settings_fields;

    //===================================================
    function __construct($item_name, $item_Name_menu_lang, $current_user_can, $plugin_name, $plugin_prefix, $parent_page, $position = 'admin_bar', $set_fields = array()){
       $this -> item_name           = $item_name;
       $this -> item_Name_menu_lang = $item_Name_menu_lang;
       $this -> current_user_can    = $current_user_can;
       $this -> plugin_name         = $plugin_name;
       $this -> plugin_prefix       = $plugin_prefix;
       $this -> parent_page         = $parent_page;
       $this -> position            = $position;
       $this -> set_fields          = $set_fields;
       // Что бы не было конфликтов с другими плагинами к странице добавим префикс
       $this -> page = $plugin_prefix . '-' . $item_name;
       $this -> add_menu();
    }

    //===================================================
    // Функция которая выполняется при создании класса
    public function add_menu(){
       add_action( 'admin_menu', array( $this, 'submenu_page'));

       // если нужно отображать меню в admin_bar
       if ( $this->position == 'admin_bar')
          // добавим пункт меню справочника в admin_bar_menu (верхнюю панель), привяжем функцию к хуку
          if ( current_user_can( $this->current_user_can )){
               add_action( 'admin_bar_menu', function ( $wp_admin_bar ){
                  add_admin_bar_menu( $wp_admin_bar,
                                      $this->plugin_prefix . '-' . $this->item_name . '-menu-id',
                                      $this->plugin_name . '/images/' . $this->item_name . '-16x16.png',
                                      $this->page,
                                      __( $this->item_Name_menu_lang, $this->plugin_name ),
                                      $this->plugin_prefix . '-' . $this->parent_page . '-menu-id' );
               }, 90 );
          }
    }

    //===================================================
    // Добавляет дочернюю страницу (без отображения в меню) указанного главного меню в админ-панели.
    public function submenu_page(){
         // var_dump( $this);
         // добавим страницу в admin_bar
         if ( $this->position == 'admin_bar')
            $hook_menu = add_submenu_page(null, $this->item_Name_menu_lang, $this->item_Name_menu_lang, $this->current_user_can, $this->page,
                            function(){
                               management_session($this->page);
                               require_once( WP_PLUGIN_DIR .'/'. $this->plugin_name . '/includes/' . $this->item_name . '/page.php' );
                            });
         else
            $hook_menu = add_submenu_page($this->parent_page, $this->item_Name_menu_lang, $this->item_Name_menu_lang, $this->current_user_can, $this->page,
                            function(){
                               management_session($this->page);
                               require_once( WP_PLUGIN_DIR .'/'. $this->plugin_name . '/includes/' . $this->item_name . '/page.php' );
                            });

        // добавим поля в настройки страницы
        // подключаемся к событию, когда страница загружена, но еще ничего не выводится
        add_action( "load-$hook_menu", function() {
           // для add_screen_option предусмотрено только per_page или layout_columns
           $option = 'per_page';
           // если не передали массив с полями настроек используем по умолчанию per page
           if ( empty( $this->set_fields ))
                $args = array(
                    'label'   => __( 'Number of lines per page', 'wp-add-function' ),
                    'default' => 10,
                    // название опции, будет записано в метаполе юзера
                    'option'  => $this->plugin_prefix .'_' . str_replace('-','_', $this->item_name) . '_per_page',
                );
            else
               $args = $this->set_fields;

            // определим является массив многомерным
            if ((count($args, COUNT_RECURSIVE) - count($args)) > 0){
               foreach ( $args as $key => &$value ) {
                  $option = $key;
                  add_screen_option( $option, $value );
               }
            } else
               add_screen_option( $option, $args );

            // создадим имя глобальной переменной
            $perName = $this->plugin_prefix . '_class_table_' . str_replace('-','_', $this->item_name);
            // объявим переменную глобальной
            global ${$perName};

            // создадим класс по имени
            $className = $this->plugin_prefix .'_class_table_' . str_replace('-','_', $this->item_name);
            ${$perName} = new $className;
        });
    }
}

//===================================================
// Управление сессиями
function management_session($sess) {
   //обработка сессий
   if ( ! session_id() ) {
      session_start();
   }
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
   // Зафиксируем текущий paged, (номер страницы пагинации), для дальнейшего возврата 
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
               $actions[$name] = sprintf( '<a href="?page=%s&action=%s&p=%s&n=%s&'.$name_id.'=%s">' . __( 'History', 'wp-add-function' ) . '</a>', $_REQUEST['page'], 'history', $_REQUEST['page'], $paged, $item[ $name_id ] );
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
// Функция формирует для class-wp-list-table в указанном поле кнопку фильтр
// $this_table  - переменная с сылкой на объект class-wp-list-table
// $item        - массив с структурой и значениями выделенной строки таблицы
// $column_name - имя выбранного поля таблицы
// $column_db   - имена полей таблицы базы данных для фильтра. пример: array('id'=>'user', 'name'=> '')
// $tables_db   - если для column_db нужно указать определённую таблицу. пример: array('id'=>'table_name')
// $page        - имя страницы на которую переходим (если не выбрано то текущая)
function column_button_filter( $this_table, $item, $column_name, $column_db, $tables_db = array(), $page = '' ){
   global $color;

   // Станица родитель, используется для дальнейшего возврата
   $parent = isset( $_REQUEST['p'] )    ? wp_unslash( trim( $_REQUEST['p'] )) : '';

   if ( empty( $page ))
      $page = $this_table -> page;

   // Получим фильтр который уже используется
   $old_filter = get_http_values( '', 'f');

   // Добавим выбранное значение к уже существующему фильтру
   if (is_array($column_db))
      foreach ( $column_db as $field => $table ) {
         $old_filter[$field] = $item[ $field ];
      }
   else
      $old_filter[$column_db] = $item[ $column_db ];

   $filter = http_values_query( $old_filter, '', 'f');
   $filter_tables = http_values_query( $tables_db, '', 't');

   $column_value = '<font color="'. $color . '">' . $item[ $column_name ] . '</font>';
   $actions      = array( 'filter' => sprintf('<a href="?page=%s%s%s">' . __( 'Filter', 'wp-add-function' ) . '</a>', $page, $filter, $filter_tables));

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
// Добавим поддержку select2
add_action( 'admin_enqueue_scripts', function() {
   //Our own JS file
   wp_register_script( 'select_search', WPMU_PLUGIN_URL . '/wp-add-function/js/jquery-3.5.1.js', array( 'jquery' ), 3.5, false );
   wp_enqueue_script( 'select_search' );

   //Select2 JS
   wp_register_script( 'select2_js', WPMU_PLUGIN_URL . '/wp-add-function/js/select2.js', array( 'jquery' ), 4.0, false );
   wp_enqueue_script( 'select2_js' );

   //Select2 CSS
   //wp_register_style( 'select2_css', WPMU_PLUGIN_URL . '/wp-add-function/css/select2.min.css' );
   wp_register_style( 'select2_css', WPMU_PLUGIN_URL . '/wp-add-function/css/select2-mod.css' );
   wp_enqueue_style( 'select2_css' );

   // language select2
   $user_lang = substr(get_user_locale(),0, 2);
   wp_register_script( 'select2_lang', WPMU_PLUGIN_URL . '/wp-add-function/js/i18n/' . $user_lang .'.js' );
   wp_enqueue_script( 'select2_lang' );

}, -100 );

//=============================================
// Изменим стиль админки
add_action( 'admin_head', function() {
   // внешний вид формы
   echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/forms.css' .  '">';
   // внешний вид таблицы
   echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/common.css' . '">';
   // внешний вид кнопок
   echo '<link rel="stylesheet" type="text/css" href="' . WPMU_PLUGIN_URL . '/wp-add-function/css/buttons.css' . '">';
});

//=============================================
// Скрыть уведомление об обновлении WordPress с панели администрирования для обычных пользователей.
add_action( 'admin_init', function () {
   //if ( !current_user_can('update_core') ) {
      remove_action( 'admin_notices',         'update_nag', 3 );
      remove_action( 'network_admin_notices', 'update_nag', 3 );
   //}
});

//=============================================
// Отключаем сообщение «JQMIGRATE: Migrate is installed, version 1.4.1»
add_action('wp_default_scripts', function ($scripts) {
    if (!empty($scripts->registered['jquery'])) {
        $scripts->registered['jquery']->deps = array_diff($scripts->registered['jquery']->deps, ['jquery-migrate']);
    }
});

?>

