<?php
/**
 * Base_List_Table
 *
 * Універсальна база для журналів та довідників
 * WP 6.4+ / PHP 8.2 safe
 *
 * Пріоритет джерел значень:
 *  - POST (кнопки/фільтри, якщо форма post)
 *  - GET (коли користувач явно в URL)
 *  - STATE (останній збережений стан користувача)
 *  - DEFAULT
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
  require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

abstract class Base_List_Table extends WP_List_Table {

  /* === СУМІСНІСТЬ З WP ядром / LEGACY === */
  public array $filter = [];
  public array $filter_tables = [];
  public string $search = '';

  /* === СТАН (поточний) === */
  protected string $page_slug = '';
  protected int $paged = 1;
  protected int $per_page = 20;
  protected int $offset = 0;
  protected int $total_items = 0;

  protected string $search_value = '';
  protected string $orderby = '';
  protected string $order = 'asc';

  /* === ПЕРІОД (опційно) === */
  protected bool $supports_period = false;
  protected string $date1 = '';
  protected string $date2 = '';

  /** Менеджер стану (search/sort/page/filter-*) */
  protected List_Table_State_Manager $state;

  public function __construct( array $args = [] ) {

    $this->page_slug = isset( $_REQUEST['page'] )
    ? sanitize_key( wp_unslash( $_REQUEST['page'] ) )
    : '';

    $this->state = new List_Table_State_Manager( $this->page_slug );

    $this->init_request();     // s, orderby, order, paged
    $this->init_pagination();  // per_page, offset
    $this->init_period();      // date1/date2 (якщо supports_period)

    parent::__construct( array_merge(
      [
        'singular' => 'item',
        'plural'   => 'items',
        'ajax'     => false,
      ],
      $args
    ) );
  }

  /* =====================================================
   * REQUEST (POST > GET > STATE)
   * =================================================== */

  protected function init_request(): void {

    // 🔴 Пріоритет 1: POST (поточне відправлення форми)
    // 🔴 Пріоритет 2: GET (явно вказано в URL)
    // 🔴 Пріоритет 3: STATE (збережене)

    // SEARCH
    if ( isset( $_POST['s'] ) ) {
      $this->search_value = sanitize_text_field( wp_unslash( $_POST['s'] ) );
    } elseif ( isset( $_GET['s'] ) ) {
      $this->search_value = sanitize_text_field( wp_unslash( $_GET['s'] ) );
    } else {
      $this->search_value = $this->state->get( 's', '' );
    }
    $this->search_value = trim( $this->search_value );

    // ORDERBY (зберігаємо регістр!)
    if ( isset( $_POST['orderby'] ) ) {
      $this->orderby = sanitize_text_field( wp_unslash( $_POST['orderby'] ) );
    } elseif ( isset( $_GET['orderby'] ) ) {
      $this->orderby = sanitize_text_field( wp_unslash( $_GET['orderby'] ) );
    } else {
      $this->orderby = $this->state->get( 'orderby', '' );
    }
    $this->orderby = trim( $this->orderby );

    // ORDER
    if ( isset( $_POST['order'] ) ) {
      $this->order = strtolower( sanitize_text_field( wp_unslash( $_POST['order'] ) ) );
    } elseif ( isset( $_GET['order'] ) ) {
      $this->order = strtolower( sanitize_text_field( wp_unslash( $_GET['order'] ) ) );
    } else {
      $this->order = strtolower( $this->state->get( 'order', 'asc' ) );
    }

    if ( ! in_array( $this->order, [ 'asc', 'desc' ], true ) ) {
      $this->order = 'asc';
    }

    // PAGED
    if ( isset( $_POST['paged'] ) ) {
      $this->paged = max( 1, (int) $_POST['paged'] );
    } elseif ( isset( $_GET['paged'] ) ) {
      $this->paged = max( 1, (int) $_GET['paged'] );
    } else {
      $this->paged = max( 1, (int) $this->state->get( 'paged', 1 ) );
    }
  }

  /* =====================================================
   * PAGINATION
   * =================================================== */
  protected function init_pagination(): void {

    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    $opt    = $screen ? $screen->get_option( 'per_page' ) : [];

    $meta_key = $opt['option'] ?? '';
    $default  = (int) ( $opt['default'] ?? 20 );

    /**
     * ВАЖЛИВО:
     * Під час export-запиту (admin.php?action=export) WP не завантажує вашу
     * сторінку через add_submenu_page(), а отже НЕ спрацьовує load-$hook_menu,
     * де викликається add_screen_option('per_page', ...).
     *
     * У такому режимі get_current_screen()->get_option('per_page') повертає порожньо,
     * meta_key стає '', і ми падаємо на дефолт 20, тоді як UI працює з дефолтом 10.
     *
     * Щоб експорт віддавав рівно ту ж кількість рядків, що й таблиця в UI,
     * робимо fallback meta_key з page slug.
     *
     * У вашій схемі screen option key будується так само:
     *   option = plugin_prefix . '_' . str_replace('-', '_', item_name) . '_per_page'
     * а page slug виглядає як:
     *   plugin_prefix-item_name
     *
     * Тому meta_key = str_replace('-', '_', page_slug) . '_per_page'.
     */
    if ( $meta_key === '' && $this->page_slug !== '' ) {
      $meta_key = str_replace( '-', '_', $this->page_slug ) . '_per_page';
      // UI для ваших сторінок задає default=10 у load-$hook_menu
      $default  = 10;
    }

    $user_val = $meta_key
    ? (int) get_user_meta( get_current_user_id(), $meta_key, true )
    : 0;

    // Обмеження для "Number of lines per page": глобальна опція (для всіх користувачів)
    // Раніше було жорстко 100, тепер керується через сторінку Settings.
    $max_per_page = (int) get_option( 'waf_max_rows_per_page', 100 );
    $max_per_page = max( 1, $max_per_page );

    $per_page = $user_val > 0 ? $user_val : $default;
    $per_page = max( 1, min( $max_per_page, (int) $per_page ) );

    $this->per_page = $per_page;
    $this->offset   = ( $this->paged - 1 ) * $this->per_page;
  }

  /* =====================================================
   * PERIOD (окремий механізм)
   * =================================================== */
  protected function init_period(): void {

    if ( ! $this->supports_period ) {
      return;
    }

    // Глобальне обмеження: максимальна кількість днів у вибраному періоді журналів.
    // Задається на сторінці Settings (wp_options: waf_max_journal_days).
    $max_days = (int) get_option( 'waf_max_journal_days', 100 );
    $max_days = max( 1, $max_days );

    $today = current_time( 'Y-m-d' );

    // Локальний helper для безпечного DateTimeImmutable з Y-m-d.
    $tz = function_exists( 'wp_timezone' ) ? wp_timezone() : new DateTimeZone( 'UTC' );
    $to_dt = static function ( string $ymd, DateTimeZone $tz, string $fallback_ymd ): DateTimeImmutable {
      $ymd = trim( $ymd );
      $dt  = DateTimeImmutable::createFromFormat( 'Y-m-d', $ymd, $tz );
      if ( $dt instanceof DateTimeImmutable ) {
        // Страховка від часткового парсингу (напр. 2025-13-99).
        $errors = DateTimeImmutable::getLastErrors();
        if ( empty( $errors['warning_count'] ) && empty( $errors['error_count'] ) ) {
          return $dt;
        }
      }
      return DateTimeImmutable::createFromFormat( 'Y-m-d', $fallback_ymd, $tz ) ?: new DateTimeImmutable( 'now', $tz );
    };

    // Пріоритет: POST > STATE > default
    if ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) === 'POST' && isset( $_POST['button_period'] ) ) {
      $raw1 = isset( $_POST['pdate-date1'] ) ? sanitize_text_field( wp_unslash( $_POST['pdate-date1'] ) ) : $today;
      $raw2 = isset( $_POST['pdate-date2'] ) ? sanitize_text_field( wp_unslash( $_POST['pdate-date2'] ) ) : $today;
    } else {
      // Беремо зі збереженого стану
      $raw1 = (string) $this->state->get( 'date1', $today );
      $raw2 = (string) $this->state->get( 'date2', $today );
    }

    $d1 = $to_dt( $raw1, $tz, $today );
    $d2 = $to_dt( $raw2, $tz, $today );

    // Нормалізація: якщо користувач переплутав, міняємо місцями.
    if ( $d1 > $d2 ) {
      [ $d1, $d2 ] = [ $d2, $d1 ];
    }

    // Перевірка та обмеження діапазону (в днях), інклюзивно.
    // Якщо період довший за $max_days, зменшуємо "з" до дозволеного діапазону відносно "по".
    $diff_days_inclusive = (int) $d2->diff( $d1 )->format( '%a' ) + 1;
    if ( $diff_days_inclusive > $max_days ) {
      $d1 = $d2->sub( new DateInterval( 'P' . max( 0, $max_days - 1 ) . 'D' ) );
    }

    $this->date1 = $d1->format( 'Y-m-d' );
    $this->date2 = $d2->format( 'Y-m-d' );

    // Зберігаємо НОРМАЛІЗОВАНІ значення в стані тільки коли це потрібно (щоб зайвий раз не писати user_meta).
    $need_persist = ( ( $_SERVER['REQUEST_METHOD'] ?? '' ) === 'POST' && isset( $_POST['button_period'] ) );

    if ( ! $need_persist ) {
      $prev1 = (string) $this->state->get( 'date1', '' );
      $prev2 = (string) $this->state->get( 'date2', '' );
      $need_persist = ( $prev1 !== $this->date1 ) || ( $prev2 !== $this->date2 );
    }

    if ( $need_persist ) {
      $this->state->set( 'date1', $this->date1 );
      $this->state->set( 'date2', $this->date2 );
    }
  }

  /* =====================================================
   * FILTER-* API (POST > GET > STATE)
   * =================================================== */

  /**
   * Отримати значення filter-*
   * Пріоритет:
   *  1) POST (поточне відправлення)
   *  2) GET (явно в URL)
   *  3) STATE (збережене)
   *  4) default
   */
  public function get_filter( string $name, $default = null ) {

    $key = ( str_starts_with( $name, 'filter-' ) || str_starts_with( $name, 'field-' ) )
    ? $name
    : 'filter-' . $name;

    // 1) POST (найвищий пріоритет)
    if ( isset( $_POST[ $key ] ) && $_POST[ $key ] !== '' ) {
      $value = is_array( $_POST[ $key ] )
      ? array_map( 'sanitize_text_field', wp_unslash( $_POST[ $key ] ) )
      : sanitize_text_field( wp_unslash( $_POST[ $key ] ) );

      // 🔴 Автоматично зберігаємо при отриманні з POST
      $this->state->set( $key, $value );
      return $value;
    }

    // 2) GET / REQUEST (після редіректу або ручного URL)
    if ( isset( $_GET[ $key ] ) && $_GET[ $key ] !== '' ) {
      $value = is_array( $_GET[ $key ] )
      ? array_map( 'sanitize_text_field', wp_unslash( $_GET[ $key ] ) )
      : sanitize_text_field( wp_unslash( $_GET[ $key ] ) );

      // 🔴 Також зберігаємо при отриманні з GET
      $this->state->set( $key, $value );
      return $value;
    }

    // 3) STATE (збережене)
    $value = $this->state->get( $key, null );
    if ( $value !== null && $value !== '' ) {
      return $value;
    }

    return $default;
  }

  /* =====================================================
   * GETTERS (для дочірніх таблиць)
   * =================================================== */
  public function get_search(): string { return $this->search_value; }
  public function get_orderby(): string { return $this->orderby; }
  public function get_order(): string { return $this->order; }
  public function get_limit(): int { return $this->per_page; }
  public function get_offset(): int { return $this->offset; }



  /* =====================================================
   * EXPORT UI VISIBILITY (Screen Options)
   * =================================================== */
  protected function get_export_visibility_meta_key( string $type ): string {
    if ( $this->page_slug === '' ) {
      return '';
    }

    $base = str_replace( '-', '_', $this->page_slug );

    if ( $type === 'csv' ) {
      return $base . '_hide_export_csv';
    }

    if ( $type === 'html' ) {
      return $base . '_hide_export_html';
    }

    if ( $type === 'pdf' ) {
      return $base . '_hide_export_pdf';
    }

    return '';
  }

  public function is_export_csv_hidden(): bool {
    $key = $this->get_export_visibility_meta_key( 'csv' );
    if ( $key === '' ) {
      return false;
    }

    return ( (int) get_user_meta( get_current_user_id(), $key, true ) ) === 1;
  }

  public function is_export_html_hidden(): bool {
    $key = $this->get_export_visibility_meta_key( 'html' );
    if ( $key === '' ) {
      return false;
    }

    return ( (int) get_user_meta( get_current_user_id(), $key, true ) ) === 1;
  }

  public function is_export_pdf_hidden(): bool {
    $key = $this->get_export_visibility_meta_key( 'pdf' );
    if ( $key === '' ) {
      return false;
    }

    return ( (int) get_user_meta( get_current_user_id(), $key, true ) ) === 1;
  }

  /* =====================================================
   * EXPORT HELPERS (для шапки експорту) — ДОДАНО АКУРАТНО
   * =================================================== */

  /**
   * Дефолтна назва експорту (шапка). Дочірні класи можуть перевизначити.
   */
  public function get_export_title(): string {
    return __( 'Export', 'wp-add-function' );
  }

  /**
   * Дати періоду (якщо supports_period=true). Експортер може їх використати.
   */
  public function get_export_period(): array {
    if ( ! $this->supports_period ) {
      return [ 'from' => '', 'to' => '' ];
    }
    return [
      'from' => (string) $this->date1,
      'to'   => (string) $this->date2,
    ];
  }

  /**
   * Людинозрозуміле представлення фільтрів для шапки експорту.
   *
   * ВАЖЛИВО:
   *  - Тут робимо мінімальний “смарт” тільки для filter-accountId.
   *  - Значення беремо з $this->filter_tables якщо там є мапа id => label.
   *  - Якщо мапи немає — повертаємо raw значення.
   *
   * Очікуваний результат:
   *  filter-accountId = 2  =>  "Картковий рахунок = 41342194 АГРОСПЕЦ Дт"
   */
  public function get_export_filters_pretty( array $filters ): array {

    $out = [];

    // 1) Картковий рахунок
    if ( array_key_exists( 'filter-accountId', $filters ) ) {

      $raw = $filters['filter-accountId'];

      // normalize -> scalar id
      $id = is_array( $raw ) ? (int) reset( $raw ) : (int) $raw;

      $label = __( 'Card account', 'wp-add-function' );

      // Пошук у filter_tables:
      // допускаємо структури:
      //  - $filter_tables['accountId'][2] = '4134...'
      //  - $filter_tables['filter-accountId'][2] = '4134...'
      $pretty = $this->export_lookup_filter_table_value( 'accountId', $id );
      if ( $pretty === '' ) {
        $pretty = $this->export_lookup_filter_table_value( 'filter-accountId', $id );
      }

      $value = $pretty !== '' ? $pretty : (string) $raw;

      $out[] = sprintf( '%s = %s', $label, $value );
    }

    // (опційно) інші filter-* можна додати у дочірніх класах

    // чистка
    $out = array_map( static fn( $x ) => trim( wp_strip_all_tags( (string) $x ) ), $out );
    return array_values( array_filter( $out, static fn( $x ) => $x !== '' ) );
  }

  /**
   * Акуратний lookup значення по id у filter_tables.
   */
  protected function export_lookup_filter_table_value( string $table_key, int $id ): string {

    if ( $id <= 0 ) { return ''; }

    $tbl = $this->filter_tables[ $table_key ] ?? null;
    if ( ! is_array( $tbl ) ) { return ''; }

    // ключі можуть бути int або string
    if ( isset( $tbl[ $id ] ) ) {
      return trim( (string) $tbl[ $id ] );
    }
    $sid = (string) $id;
    if ( isset( $tbl[ $sid ] ) ) {
      return trim( (string) $tbl[ $sid ] );
    }

    return '';
  }

  /* =====================================================
   * URL для повернення з форми редагування
   * =================================================== */

  /**
   * Побудувати URL для повернення з форми редагування/створення
   * Зберігає всі параметри: пошук, сортування, фільтри, пагінацію
   */
  public function get_return_url(): string {
    return $this->state->build_return_url();
  }

  /**
   * Побудувати URL для переходу на форму редагування/створення
   * Додає параметри action та id, але зберігає параметри для повернення
   */
  public function get_edit_url( string $action, $id = null ): string {
    $params = [];

    // 🔴 Базові параметри
    $params['page']   = $this->page_slug;
    $params['action'] = $action;

    if ( $id ) {
      $params['id'] = $id;
    }

    // 🔴 Додаємо всі збережені параметри для повернення
    foreach ( $this->state->all() as $key => $value ) {
      if ( in_array( $key, [ 'date1', 'date2' ], true ) ) {
        continue; // Період обробляється окремо
      }

      if ( $value !== '' && ! is_array( $value ) && ! isset( $params[ $key ] ) ) {
        $params[ $key ] = $value;
      }
    }

    $url = admin_url( 'admin.php?' . http_build_query( $params ) );
    error_log( '[BASE_LIST_TABLE] Built edit URL: ' . $url );

    return $url;
  }

  /* =====================================================
   * WP_List_Table
   * =================================================== */
  public function prepare_items(): void {

    $this->items = $this->query_items();

    $total_pages = $this->per_page > 0
    ? (int) ceil( $this->total_items / $this->per_page )
    : 1;

    $this->set_pagination_args( [
      'total_items' => $this->total_items,
      'per_page'    => $this->per_page,
      'total_pages' => $total_pages,
    ] );
  }

  abstract protected function query_items(): array;

  public function column_default( $item, $column_name ) {
    return isset( $item[ $column_name ] )
    ? esc_html( (string) $item[ $column_name ] )
    : '';
  }

  /**
   * Допоміжний метод для відображення кнопок Filter/Reset
   */
  public function extra_tablenav( $which ) {
    if ( 'top' !== $which ) {
      return;
    }

    // Виводимо фільтри та кнопки
    if ( method_exists( $this, 'render_filters' ) ) {
      $this->render_filters();
    }
  }

  /**
   * Get all items for export (without pagination).
   *
   * @return array All items from the table
   */
  public function get_all_items_for_export(): array {
    // Зберігаємо поточні значення
    $saved_per_page = $this->per_page;
    $saved_paged    = $this->paged;
    $saved_offset   = $this->offset;
    $saved_items    = $this->items;

    // Вимикаємо пагінацію - отримуємо всі записи
    $max_per_page = (int) get_option( 'waf_max_rows_per_page', 100 );
    $max_per_page = max( 10000, $max_per_page );

    $this->per_page = $max_per_page;
    $this->paged    = 1;
    $this->offset   = 0;

    // Отримуємо всі записи
    $this->items = $this->query_items();

    // Зберігаємо результат
    $all_items = $this->items;

    // Відновлюємо значення
    $this->per_page = $saved_per_page;
    $this->paged    = $saved_paged;
    $this->offset   = $saved_offset;
    $this->items    = $saved_items;

    return $all_items;
  }
}
