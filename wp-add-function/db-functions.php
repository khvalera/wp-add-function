<?php

// Функції до роботи з базою даних

//===========================================
// Клас створює и отрумує wpdb, інкапсуляція, контроль і можливість підключати.
// кілька БД (наприклад, у різних плагінах.
/*
1. Один клас керує всіма зовнішніми БД.
2. Конфігурації ізольовані по плагінах.
3. Підключення створюється лише один раз (синглтон-підхід).
4. Можна легко додавати нові плагіни — просто додати конфіг у масив $configs.
*/
//===========================================
class external_db {

    private static $instances = [];

    // * Повертає wpdb для плагіна
    public static function get_db(string $plugin_name): wpdb {
        if (!isset(self::$instances[$plugin_name])) {
            self::$instances[$plugin_name] = self::create_instance($plugin_name);
        }
        return self::$instances[$plugin_name];
    }

    // * Створює підключення
    private static function create_instance(string $plugin_name): wpdb {
        $conf = self::get_db_conf($plugin_name);

        $db = new wpdb($conf['user'], $conf['pass'], $conf['name'], $conf['host']);

        if (!empty($db -> error)) {
            wp_die(sprintf(
                __('Could not connect to external database for "%s": %s', 'wp-add-function'),
                esc_html($plugin_name),
                esc_html($db ->error)
            ));
        }

        return $db;
    }

    // * Отримує конфігурацію з файлу /includes/db-config.php конкретного плагіна
    private static function get_db_conf(string $plugin_name): array {
        $config_file = WP_PLUGIN_DIR . '/' . $plugin_name . '/includes/db-config.php';

        if (!file_exists($config_file)) {
            wp_die(sprintf(__('DB config file not found for plugin "%s"', 'wp-add-function'), esc_html($plugin_name)));
        }

        // Підключаємо файл і отримуємо масив
        require_once $config_file;

        if (!function_exists('get_db_conf')) {
            wp_die(sprintf(__('Function get_db_conf() not found in %s', 'wp-add-function'), esc_html($config_file)));
        }

        $conf = get_db_conf();

        // Перевірка мінімальних полів
        $required_keys = ['name','user','pass','host'];
        foreach ($required_keys as $key) {
            if (empty($conf[$key])) {
                wp_die(sprintf(__('DB config missing key "%s" in %s', 'wp-add-function'), $key, esc_html($config_file)));
            }
        }

        // Додати charset, якщо не вказано
        if (!isset($conf['charset'])) {
            $conf['charset'] = 'utf8mb4';
        }

        return $conf;
    }
}

//=============================================
// Функция создает часть запроса MySQL для фильтра
function add_query_filter( $array_filter, $array_filter_tables ) {

   $gl_ = gl_form_array::get();

   $query_filter = "";
   if ( empty( $array_filter ))
      return;
   foreach ( $array_filter as $field => $value ) {
      if (! empty( $value )) {
         // если в имени поля первый знак *, то не используем таблицу (устарело)
         if ( $field[0] == "*"){
            if ( !empty($query_filter))
               $query_filter =  $query_filter . " AND ";
            $query_filter =  $query_filter . substr($field, 1 ) . " = " . $value;
         // если есть точка, значит с полем указана таблица (устарело)
         } elseif ( strpos($field, ".") != false ){
            if ( !empty($query_filter))
               $query_filter =  $query_filter . " AND ";
            $query_filter =  $query_filter . $field . " = " . $value;
         } else {
            if ( !empty($query_filter))
               $query_filter =  $query_filter . " AND ";
            if ( empty( $array_filter_tables )){
               $query_filter = $query_filter . $gl_['db_table_name'] . "." . $field . " = " . $value;
             }else {
               // если для фильтра нужно указать конкретную таблицу
               if (array_key_exists($field, $array_filter_tables)){
                  $query_filter = $query_filter . $array_filter_tables[$field] . "." . $field . " = " . $value;}
               else
                  $query_filter = $query_filter . $gl_['db_table_name'] . "." . $field . " = " . $value;
            }
         }
      }
   }
   return $query_filter;
}

//=============================================
// Функция для получения строки таблицы по $id
// $db_table_name - имя таблицы базы данных (не обязательно, если не указано берется из $gl_)
// $output_type   - вид возврата данных (не обязательно, по умолчанию ARRAY_A)
// $id            - если нужно указать id явно
function get_row_table_id( $db_table_name = '', $output_type = '', $id = '' ) {

   $gl_ = gl_form_array::get();

   if ( empty( $db_table_name ))
      $db_table_name = $gl_['db_table_name'];
   if ( empty( $output_type ))
      $output_type = ARRAY_A;

   // получим id
   if ( empty($id) )
      $id = isset( $_REQUEST['id'] ) ? wp_unslash( trim( $_REQUEST['id'] )) : '';

   $query     = $gl_['db'] -> prepare( "SELECT * FROM " . $db_table_name . " WHERE id = %s", $id);
   $row_table = $gl_['db'] -> get_row( $query , $output_type );

   return $row_table;
}

//====================================
// Удаление или отмена удаления данных из формы
function delete_form_data() {
   $gl_ = gl_form_array::get();

   // Выполним функцию с префиксом
   $func = $gl_['prefix'] . '_delete_form_data';
   $func();
}

//====================================
// Запись истории
function write_data_history($id){

   $gl_ = gl_form_array::get();

   // $action используется для фильтров и нажатия кнопок
   $action = isset( $_REQUEST['action'] ) ? wp_unslash( trim( $_REQUEST['action'] )) : '';

   // получим нужную строку таблицы по $id в виде массива
   $data = get_row_table_id( '', '', $id);

   $data['object_id'] = $data['id'];
   // Удалим id в истоии он будет свой
   unset($data['id']);
   $data['modify'] = current_date_time();

   if ( $action == 'new' )
      $data['action'] = __( 'Record new', 'wp-add-function' );
   elseif ( $action == 'edit' )
      $data['action'] = __( 'Data correction', 'wp-add-function' );
   elseif ( $action == 'cancel-deletion' )
      $data['action'] = __( 'Cancel deletion', 'wp-add-function' );
   elseif ( $action == 'delete' )
      $data['action'] = __( 'Delete mark', 'wp-add-function' );
   // Для нового делаем только insert
   $error = $gl_['db'] -> insert( $gl_['db_table_name'] . '_history', $data);
   if ( $error === false ) {
      display_message('error_inserting_history_data',__( 'Error inserting history data!', 'wp-add-function' ), 'error');
      return;
   }
}

?>
