<?php

// Функции для работы с базой данных

//=============================================
// Функция создает часть запроса MySQL для фильтра
function add_query_filter( $array_filter, $array_filter_tables ) {
   global $gl_;

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
   global $gl_;

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
   global $gl_;

   // Выполним функцию с префиксом
   $func = $gl_['prefix'] . '_delete_form_data';
   $func();
}

//====================================
// Запись истории
function write_data_history($id){
   global $gl_;

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
