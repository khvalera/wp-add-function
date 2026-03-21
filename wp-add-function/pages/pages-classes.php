<?php
// Класи для роботи з формами та сторінками

//====================================
// Клас для створення кнопки з текстом (використовується разом з class_dialogue_form)
// button_text      - текст на кнопці
// button_title     - спливаюча підказка
// label_text       - текст мітки перед кнопкою
// link_page        - адреса документа, на який слід перейти
// current_user_can - права користувача
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
  // відображення
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
// Клас використовується для створення кнопки з використанням href
// text             - текст на кнопці
// title            - спливаюча підказка
// link_page        - адреса документа, на який слід перейти
// current_user_can - права користувача
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
  // відображення кнопки
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
    // якщо не встановлені права користувача
    if ( empty( $this -> current_user_can ))
      $this -> button();
    else
      if ( current_user_can( $this -> current_user_can ))
        $this -> button();
  }
}

//====================================
// Клас для створення діалогової форми
// description1_font_size і description2_font_size - розмір шрифта для тексту описів
class class_dialogue_form {
  public $display_description = false;
  public $display_controls = false;
  public $plugin_name, $image_file, $header_text, $footer_button, $description_text1, $description_text2, $description1_font_size, $description2_font_size;
  public $item_controls;

  //====================================
  function __construct(){
  }

  //====================================
  // plugin_name - ім'я плагіна
  // image_file  - шлях до файлу з зображенням відносно каталога плагіна
  // description_text1 - опис №1
  // description_text2 - опис №2
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
  // функція, яка виконується під час створення класу
  public function form_display(){
    ?>
    <form action="" method="post">
    <div class="wrap">
    <h2>
    <?php echo $this -> header_text; ?>
    </h2>
    </div>
    <?php
    if ( $this -> display_description )
      $this -> description($this -> plugin_name, $this -> image_file, $this -> description_text1, $this -> description_text2);

    if ( $this -> display_controls )
      $this -> controls();
    ?>
    <p>
    <?php call_user_func( $this -> footer_button); ?>
    </p>
    </form>
    </div>
    <?php
  }
}

//===========================================
// Клас для збереження та доступу до даних сторінкової форми
//===========================================
class gl_form_array {
  private static $data = null;

  //=========================================
  // Ініціалізація (один раз на початку сторінки)
  public static function set(array $config): void {
    $defaults = [
      'plugin_name'        => '',
      'singular_name'      => '',
      'plural_name'        => '',
      'db_table_name'      => '',
      'search_box_name'    => 'Search',
      'name_function'      => 'get_row_table_id',
    ];

    $data = array_merge($defaults, $config);

    if (empty($data['plural_name']) && !empty($data['singular_name'])) {
      $data['plural_name'] = $data['singular_name'] . 's';
    }

    if (empty($data['db_table_name']) && !empty($data['singular_name'])) {
      $data['db_table_name'] = $data['singular_name'] . 's';
    }

    $data['singular_name_lang'] = __(str_replace('-', ' ', $data['singular_name']), $data['plugin_name']);
    $data['singular_Name_lang'] = __(ucfirst(str_replace('-', ' ', $data['singular_name'])), $data['plugin_name']);
    $data['plural_name_lang']   = __(str_replace('-', ' ', $data['plural_name']), $data['plugin_name']);
    $data['picture_title']      = "/images/" . $data['plural_name'] . "-64x64.png";

    $data['db_table_name_history'] = 'history_' . $data['db_table_name'];
    $data['db'] = external_db::get_db($data['plugin_name']);

    self::$data = $data;
  }

  //=========================================
  // Отримати весь масив
  public static function get(): ?array {
    return self::$data;
  }

  //=========================================
  // Отримати конкретний елемент
  public static function get_value(string $key, $default = null) {
    return self::$data[$key] ?? $default;
  }

  //=========================================
  // Перевірити чи існує ключ
  public static function has(string $key): bool {
    return self::$data !== null && array_key_exists($key, self::$data);
  }

  //=========================================
  // Доступ як до властивостей
  public static function __callStatic($name, $args) {
    return self::get_value($name, $args[0] ?? null);
  }

  //=========================================
  // Оновлення одного ключа
  public static function update( string $key, $value ) {
    $gl_ = self::get();
    $gl_[$key] = $value;
    self::set($gl_);
  }
}

//===================================================
// Клас автоматизує додавання дочірньої сторінки в меню
//===================================================
class add_admin_submenu_class_table {
  public string $item_name;
  public string $item_name_lang;
  public string $current_user_can;
  public string $plugin_name;
  public string $plugin_prefix;
  public string $parent_page;
  public string $page;
  public string $position;
  public string $page_callback;
  public array $set_fields;

  //===================================================
  function __construct(array $config) {
    $this->plugin_name      = $config['plugin_name'] ?? '';
    $this->item_name        = $config['item_name'] ?? '';
    $this->item_name_lang   = __($config['item_name_lang'] ?? $this->item_name, $this->plugin_name);
    $this->current_user_can = $config['current_user_can'] ?? 'manage_options';
    $this->plugin_prefix    = $config['plugin_prefix'] ?? '';
    $this->parent_page      = $config['parent_page'] ?? 'options-general.php';
    $this->position         = $config['position'] ?? 'admin_menu';
    $this->page_callback    = $config['page_callback'] ?? '';
    $this->set_fields       = $config['set_fields'] ?? [];

    $this->page = $this->plugin_prefix . '-' . $this->item_name;

    if ( ! empty( $this->plugin_prefix ) ) {

      $target_option = $this->plugin_prefix . '_' . str_replace('-', '_', $this->item_name) . '_per_page';

      static $per_page_filters_registered = [];
      if ( empty( $per_page_filters_registered[ $target_option ] ) ) {
        $per_page_filters_registered[ $target_option ] = true;

        add_filter( 'set-screen-option', function( $status, $option, $value ) use ( $target_option ) {

          if ( $option === $target_option ) {
            $max = (int) get_option( 'waf_max_rows_per_page', 100 );
            $max = max( 1, $max );

            $v = (int) $value;
            $v = max( 1, min( $max, $v ) );

            return $v;
          }

          return $status;

        }, 10, 3 );
      }
    }

    $this->add_menu();
  }

  //===================================================
  // Функція створює клас по імені
  private function create_class_table(): void {
    $className = $this->plugin_prefix . '_class_table_' . str_replace('-', '_', $this->item_name);
    $instance  = new $className();
    gl_form_array::update('class-table', $instance);
  }

  //===================================================
  // Зберігає прапорці приховування кнопок експорту з Screen Options
  private function save_export_screen_options(
    string $export_csv_meta_key,
    string $export_html_meta_key,
    string $export_pdf_meta_key
  ): void {

    if ( ! is_admin() ) {
      return;
    }

    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
      return;
    }

    if ( empty( $_POST['screenoptionnonce'] ) ) {
      return;
    }

    if ( empty( $_POST['ccards_screen_export_flags'] ) ) {
      return;
    }

    if ( ! wp_verify_nonce( wp_unslash( $_POST['screenoptionnonce'] ), 'screen-options-nonce' ) ) {
      return;
    }

    $user_id = get_current_user_id();
    if ( ! $user_id ) {
      return;
    }

    $hide_csv  = ! empty( $_POST['ccards_hide_export_csv'] ) ? 1 : 0;
    $hide_html = ! empty( $_POST['ccards_hide_export_html'] ) ? 1 : 0;
    $hide_pdf  = ! empty( $_POST['ccards_hide_export_pdf'] ) ? 1 : 0;

    update_user_meta( $user_id, $export_csv_meta_key,  $hide_csv );
    update_user_meta( $user_id, $export_html_meta_key, $hide_html );
    update_user_meta( $user_id, $export_pdf_meta_key,  $hide_pdf );
  }

  //===================================================
  // Додає хуки меню
  public function add_menu(): void {
    add_action( 'admin_menu', array( $this, 'submenu_page'));

    if ( $this->position == 'admin_bar' ) {
      if ( current_user_can( $this->current_user_can ) ) {
        add_action( 'admin_bar_menu', function ( $wp_admin_bar ) {
          add_admin_bar_menu(
            $wp_admin_bar,
            $this->plugin_prefix . '-' . $this->item_name . '-menu-id',
            $this->plugin_name . '/images/' . $this->item_name . '-16x16.png',
            $this->page,
            $this->item_name_lang,
            $this->plugin_prefix . '-' . $this->parent_page . '-menu-id'
          );
        }, 90 );
      }
    }
  }

  //===================================================
  // Додає дочірню сторінку та налаштування Screen Options
  public function submenu_page(): void {

    if ( $this->position == 'admin_bar' ) {
      $hook_menu = add_submenu_page(
        'options.php',
        $this->item_name_lang,
        $this->item_name_lang,
        $this->current_user_can,
        $this->page,
        function(){
          require_once( WP_PLUGIN_DIR . '/' . $this->plugin_name . '/includes/' . $this->item_name . '/page.php' );

          if ( ! empty( $this->page_callback ) && function_exists( $this->page_callback ) ) {
            call_user_func( $this->page_callback );
          }
        }
      );
    } else {
      $hook_menu = add_submenu_page(
        $this->parent_page,
        $this->item_name_lang,
        $this->item_name_lang,
        $this->current_user_can,
        $this->page,
        function(){
          require_once( WP_PLUGIN_DIR . '/' . $this->plugin_name . '/includes/' . $this->item_name . '/page.php' );

          if ( ! empty( $this->page_callback ) && function_exists( $this->page_callback ) ) {
            call_user_func( $this->page_callback );
          }
        }
      );
    }

    add_action( "load-$hook_menu", function() use ( $hook_menu ) {

      $option = 'per_page';

      if ( empty( $this->set_fields ) ) {
        $args = array(
          'label'   => __( 'Number of lines per page', 'wp-add-function' ),
                      'default' => 10,
                      'option'  => $this->plugin_prefix . '_' . str_replace( '-', '_', $this->item_name ) . '_per_page',
        );
      } else {
        $args = $this->set_fields;
      }

      if ( ( count( $args, COUNT_RECURSIVE ) - count( $args ) ) > 0 ) {
        foreach ( $args as $key => &$value ) {
          $option = $key;
          add_screen_option( $option, $value );
        }
      } else {
        add_screen_option( $option, $args );
      }

      // Screen Options: hide export buttons (CSV / HTML / PDF)
      $export_csv_meta_key  = $this->plugin_prefix . '_' . str_replace( '-', '_', $this->item_name ) . '_hide_export_csv';
      $export_html_meta_key = $this->plugin_prefix . '_' . str_replace( '-', '_', $this->item_name ) . '_hide_export_html';
      $export_pdf_meta_key  = $this->plugin_prefix . '_' . str_replace( '-', '_', $this->item_name ) . '_hide_export_pdf';

      // ЗБЕРЕЖЕННЯ ПРАПОРЦІВ
      $this->save_export_screen_options(
        $export_csv_meta_key,
        $export_html_meta_key,
        $export_pdf_meta_key
      );

      add_filter(
        'screen_settings',
        function( $settings, $screen ) use ( $hook_menu, $export_csv_meta_key, $export_html_meta_key, $export_pdf_meta_key ) {

          if ( empty( $screen ) || $screen->id !== $hook_menu ) {
            return $settings;
          }

          $hide_csv  = (int) get_user_meta( get_current_user_id(), $export_csv_meta_key, true );
          $hide_html = (int) get_user_meta( get_current_user_id(), $export_html_meta_key, true );
          $hide_pdf  = (int) get_user_meta( get_current_user_id(), $export_pdf_meta_key, true );

          $html  = '<fieldset class="screen-options">';
          $html .= '<legend>' . esc_html__( 'Export buttons', 'wp-add-function' ) . '</legend>';

          // Маркер, щоб знати, що це саме наш submit із Screen Options
          $html .= '<input type="hidden" name="ccards_screen_export_flags" value="1" />';

          $html .= '<label style="margin-right:16px;">';
          $html .= '<input type="checkbox" name="ccards_hide_export_csv" value="1" ' . checked( 1, $hide_csv, false ) . ' />';
          $html .= ' ' . esc_html__( 'Hide CSV button', 'wp-add-function' );
          $html .= '</label>';

          $html .= '<label style="margin-right:16px;">';
          $html .= '<input type="checkbox" name="ccards_hide_export_html" value="1" ' . checked( 1, $hide_html, false ) . ' />';
          $html .= ' ' . esc_html__( 'Hide HTML button', 'wp-add-function' );
          $html .= '</label>';

          $html .= '<label style="margin-right:16px;">';
          $html .= '<input type="checkbox" name="ccards_hide_export_pdf" value="1" ' . checked( 1, $hide_pdf, false ) . ' />';
          $html .= ' ' . esc_html__( 'Hide PDF button', 'wp-add-function' );
          $html .= '</label>';

          $html .= '</fieldset>';

          return $settings . $html;
        },
        10,
        2
      );

      $this->create_class_table();
    } );
  }
}
?>
