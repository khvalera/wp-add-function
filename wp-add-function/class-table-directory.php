<?php

//===========================================
// LOAD THE BASE CLASS
if ( ! class_exists('WP_List_Table')) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//===========================================
// Таблица для справочников
class class_table_directory extends WP_List_Table {
    // глобальные переменные
    public $action, $page, $paged, $per_page, $paged_query;
    public $search_value, $filter, $filter_tables, $count_lines;
    public $color;
    public $period_date1, $period_date2;

    /** Подготавливает данные для таблицы. Метод должен быть описан в дочернем классе.
    Это важный метод на нем строиться вся таблица. Тут обычно устанавливаются все данные таблицы.
    Используйте в этом методе $this->set_pagination_args() и определите свойство $this->items - 
    обычно в него записывается результат SQL запроса.
    * @return Void */
    public function prepare_items() {

        // определим поля
        $columns     = $this -> get_columns();

        // $hidden определяет скрытые столбцы
        $hidden      = $this -> get_hidden_columns();

        // $sortable определяет, может ли таблица быть отсортирована по этому столбцу.
        $sortable    = $this -> get_sortable_columns();
        $data        = $this -> table_data();
        usort( $data, array( &$this, 'sort_data' ) );

        // общее количество элементов
        $total_items = $this -> count_lines;

        // количество страниц
        $total_pages = ceil($total_items / $this -> per_page);
        $this -> set_pagination_args( array(
            'total_items' => $total_items,
            'total_pages' => $total_pages,
            // сколько элементов отображается на странице
            'per_page'    => $this -> per_page
         ));

        $this -> _column_headers = $this->get_column_info();
        $this -> items = $data;
    }

    function __construct(){
        global $color_all, $color;

        // action используется для фильтров и нажатия кнопок
        $this -> action = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

        // Получим страницу
        $this -> page   = get_page_name();

        $this -> paged = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] )) : 1;

        // получим параметры настроек страницы
        $per_page_option = get_current_screen() -> get_option('per_page');

        // пробуем получить сохраненную настройку
        $this -> per_page = get_user_meta( get_current_user_id(), $per_page_option['option'], true );

        // если сохраненной настройки нет, берем по умолчанию
        if( ! $this -> per_page )
           $this -> per_page = $per_page_option['default'];

        // номер текущей страницы для запроса
        $this -> paged_query = isset($_REQUEST['paged']) ? max(0, intval($_REQUEST['paged'] -1) * $this -> per_page) : 0;

        // получим id пользователя WP
        $user_id = get_current_user_id();

        // пробуем получить сохраненную настройку
        $this -> period_date1 = get_user_meta( $user_id, str_replace('-','_', $this->page) . '_date1', true );
        $this -> period_date2 = get_user_meta( $user_id, str_replace('-','_', $this->page) . '_date2', true );

        // если сохраненной настройки нет, берем текущую дату
        if ( ! $this -> period_date1 )
           $this -> period_date1 = current_date_time("Y-m-d");
        if ( ! $this -> period_date2 )
           $this -> period_date2 = current_date_time("Y-m-d");

        // получим массив с фильтром
        $this -> filter = get_http_values( '', 'f');

        // получим таблицы для фильтра
        $this -> filter_tables = get_http_values( '', 't');

        // получим значение из диалога поиска
        $this -> search_value = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] )) : '';

        // Определим цвет для дальнейшего использования в функциях
        if ( $this -> action == 'filter-deletion' )
           $color = $color_all['red'];
        elseif ( $this -> action == 'history' )
           $color = $color_all['light_brown'];
        else
           $color = '';

        /* plural (строка) — Название для множественного числа, используется во всяких заголовках, например в css классах, 
        в заметках, например 'posts', тогда 'posts' будет добавлен в класс table. По умолчанию: '' ($this->screen->base)

       singular (строка) — Название для единственного числа, например 'post'. По умолчанию: ''

       ajax (логический) — Должна ли поддерживать таблица AJAX. Если true, класс будет вызывать метод _js_vars() в подвале, 
       чтобы передать нужные переменные любому скрипту обрабатывающему AJAX события. По умолчанию: false

       screen (строка) — Строка содержащая название хука, нужного для определения текущей страницы. Если null, то будет установлен 
       текущий экран. По умолчанию: null */
       parent::__construct( array(
            'singular'  => __( 'book',  'wp-add-function' ),  //singular name of the listed records
            'plural'    => __( 'books', 'wp-add-function' ),  //plural name of the listed records
            'ajax'      => false                                   //does this table support ajax?

       ));
       add_action( 'admin_head', array( &$this, 'admin_header' ) );
    }

    //====================================
    // Дополнительные элементы управления таблицей, которые расположены между групповыми действиями и пагинацией.
    // Обычно сюда располагают фильтры данных таблицы.
    public function extra_tablenav( $which ){
       global $color_all;

       if ( 'top' != $which )
          return;
       if ( $this -> action == 'history' )
          return;
       ?>
          <form id="form-extra_tablenav" action="" method="post">
          <ul class="subsubsub">
             <?php echo __( 'Filter', 'wp-add-function' ) . ': ';
             if ( $this -> action != 'filter-deletion' ) {
                ?> <a href="<?php echo sprintf('?page=%s&action=%s', $_REQUEST['page'], 'filter-deletion');?>" class="page-title-action" style="color: <?php echo $color_all['red'];?>">
                      <?php echo _e("Marked for deletion", 'card-manager' );?>
                   </a> &nbsp;
                <?php
                //button_action( __('Filter', 'card-manager' ), "button_filter" );
             }
             button_html(__("Reset", 'card-manager' ), sprintf('?page=%s', $_REQUEST['page']));?>
          </ul>
          </form>
       <?php
    }

   /* Форма поиска
    * Search form
    * @since 1.8
    * @param string $text
    * @param int $input_id
    * @uses _admin_search_query()
    * @uses has_items()
    * @uses submit_button()*/
    public function search_box($text, $input_id) {
      if ( $this -> action == 'history' )
          return;
      if ( $this -> action == 'filter-deletion' )
          return;
      if(empty($_REQUEST['s']) && !$this -> has_items()) {
        return;
      }
      $input_id = $input_id.'-search-input';
      if(!empty($_REQUEST['orderby'])) {
        echo '<input type="hidden" name="orderby" value="'.esc_attr($_REQUEST['orderby']).'" />';
      }
      if(!empty($_REQUEST['order'])) {
        echo '<input type="hidden" name="order" value="'.esc_attr($_REQUEST['order']).'" />';
      }
      if(!empty($_REQUEST['detached'])) {
        echo '<input type="hidden" name="detached" value="'.esc_attr($_REQUEST['detached']).'" />';
      }
      ?>
         <form id="<?php echo $this->page; ?>-filter" action="" method="get">
           <p class="search-box">
             <label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
             <input type="hidden" id="page" name="page" value="<?php echo $this->page; ?>" />
             <?php
                if ( ! empty( $this -> action ))
                   if ( $this -> action == 'filter-deletion' ){
                      echo '<input type="hidden" id="action" name="action" value="filter-deletion" />';
                 }
             ?>
             <input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
             <?php submit_button($text, 'button', false, false, array('id' => 'search-submit')); ?>
           </p>
         </form>
      <?php
    }

    function admin_header() {
       //if ( 'oa-products' != $this->page )
       //  return;
       echo '<style type="text/css">';
       echo '.wp-list-table .column-id          { width: 8%; }';
       echo '.wp-list-table .column-name        { width: 40%; }';
       echo '.wp-list-table .column-commentary  { width: 35%; }';
       echo '.wp-list-table .column-modify      { width: 10%; }';
       if ( $this -> action == 'history' ) {
           echo '.wp-list-table .column-action  { width: 12%; }';
       }
       echo '</style>';
    }

    /**********************************/
    function no_items() {
       _e( 'There is not a single value.', 'wp-add-function');
    }

    // Определяет столбцы, которые будут отображаться в вашей таблице
    // @return Array
    public function get_columns() {

        $columns = array(
            'id'         => __( 'Number',    'wp-add-function' ),
            'name'       => __( 'Name',      'wp-add-function' ),
            'commentary' => __( 'Comment',   'wp-add-function' ),
            'modify'     => __( 'Modified',  'wp-add-function' )
        );
        if ( $this -> action == 'history' ) {
           $columns['action'] = __( 'Action',    'wp-add-function' );
        }
        return $columns;
    }

    public function get_hidden_columns() {
        return array();
    }

    // Определить сортируемые столбцы.
    // @return Array
    public function get_sortable_columns() {
        $sortable_columns = array(
            'id'     => array('id',     true),
            'name'   => array('name',   true),
            'modify' => array('modify', true),
        );
        return $sortable_columns;
    }

    /** Метод который отвечает за то что содержит отдельная ячейка колонки,
    когда для вывода её данных не определен отдельный метод.
    Можно так же это сделать в function column_имя колонки.
     * @param  Array $item  Data
     * @param  String $column_name - Current column name
     * @return Mixed */
    public function column_default( $item, $column_name ) {
       if ( $column_name == "modify" ) {
          if (( $this -> action == 'history' ) or ( $this -> action == 'filter-deletion' ))
             return display_column_default( $item, $column_name );
          else
             return display_column_button( $this, $item, $column_name, array('history'), 'id' );
       } elseif ( $column_name == "name" ) {
          if ( $this -> action == 'filter-deletion' )
             return display_column_button( $this, $item, $column_name, array('cancel-deletion'), 'id' );
          elseif ( $this -> action == 'history' )
             return display_column_default( $item, $column_name );
          else
             return display_column_button( $this, $item, $column_name, array('edit','delete','filter_s'), 'id' );
       } else
             return display_column_default( $item, $column_name );
    }

    //====================================
    /** Позволяет сортировать данные по переменным, установленным в $_GET
     * @return Mixed */
    function sort_data( $a, $b ){
        // Set defaults
        $orderby = 'id';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby'])){
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order'])) {
            $order = $_GET['order'];
        }
        $result = strnatcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc') {
            return $result;
        }
        return -$result;
    }

    // Заполняем данные таблицы
    public function table_data() {
       global $gl_;

       // Если есть то получим значение ID
       $id = isset( $_REQUEST['id'] ) ? wp_unslash( trim( $_REQUEST['id'] )) : '';

       $db_table_name = $gl_['db_table_name'];
       // Дополнительный запрос (используется для отбора по objectId)
       $query_additional = $db_table_name . ".status IS NULL";
       if ( ! empty( $this -> action )) {
          if ( $this -> action == 'filter-deletion' ){
             // Помеченные на удаление
             $query_additional = $db_table_name . ".status = 1";
          } elseif ( $this -> action == 'history' ) {
             // История изменений
             $db_table_name = $gl_['db_table_name'] . '_history';
             $query_additional = $db_table_name . ".object_id = " . $id;
          }
       }

       // часть запроса для поиска
       $query_search = "";
       if ( ! empty( $this -> search_value ))
          $query_search = "AND ( " . $db_table_name . ".id      LIKE    '%" . $this-> search_value . "%' OR
                                 " . $db_table_name . ".name    LIKE    '%" . $this-> search_value . "%' OR
                                 " . $db_table_name . ".modify  LIKE    '%" . $this-> search_value . "%' OR
                                 " . $db_table_name . ".commentary LIKE '%" . $this-> search_value . "%' )";

       // часть запроса для определения общего количества строк
       $query_count  = "SELECT COUNT(*)";

       // часть запроса для формирования полей
       $query_select = "SELECT " . $db_table_name . ".*";

       // часть запроса с общей структурой
       $query_structure = "FROM " . $db_table_name . "
                           WHERE ";

       // Получим количество строк в таблице запроса
       $this -> count_lines = $gl_['db'] -> get_var( $query_count . " " . $query_structure . " " . $query_additional . " " . $query_search );

       // Получим данные
       $data = $gl_['db'] -> get_results( $query_select . " " . $query_structure . " " . $query_additional . " " . $query_search .
                                          " LIMIT " . $this -> per_page . " OFFSET " . $this -> paged_query, ARRAY_A);
       return $data;
    }

} //class
