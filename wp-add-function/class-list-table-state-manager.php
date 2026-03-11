
<?php
/**
 * List_Table_State_Manager
 *
 * Менеджер стану таблиць:
 *  - search (s)
 *  - sorting (orderby, order)
 *  - paging (paged)
 *  - filter-* (POST/GET)
 *  - field-* (для журналів)
 *  - період (date1/date2)
 *
 * Всі параметри зберігаються та відновлюються
 */

class List_Table_State_Manager {

    protected string $page;
    protected int $user_id;
    protected string $meta_key;
    protected array $state = [];

    public function __construct( string $page_slug ) {

        if ( ! is_admin() ) {
            return;
        }

        $this->page    = $page_slug;
        $this->user_id = get_current_user_id();

        if ( ! $this->user_id || ! $this->page ) {
            return;
        }

        $this->meta_key = str_replace( '-', '_', $this->page ) . '_list_table_state';

        $this->load();
        $this->maybe_reset();
        $this->maybe_save();
        $this->restore_to_request();
    }

    /* ======================================================
     * LOAD
     * ==================================================== */
    protected function load(): void {
        $saved = get_user_meta( $this->user_id, $this->meta_key, true );
        $this->state = is_array( $saved ) ? $saved : [];

    }

    /* ======================================================
     * RESET (button_reset)
     * ==================================================== */
    protected function maybe_reset(): void {

        if ( $_SERVER['REQUEST_METHOD'] !== 'POST' ) {
            return;
        }

        if ( ! isset( $_POST['button_reset'] ) ) {
            return;
        }

        // 🔴 Скидаємо ВСЕ, крім періоду (date1/date2) - вони можуть зберігатися окремо
        $period = [
            'date1' => $this->state['date1'] ?? '',
            'date2' => $this->state['date2'] ?? '',
        ];

        delete_user_meta( $this->user_id, $this->meta_key );
        $this->state = $period;

        // Зберегти лише період
        update_user_meta( $this->user_id, $this->meta_key, $this->state );

        // 🔴 КЛЮЧОВЕ: перенаправляємо на чистий GET
        wp_safe_redirect( admin_url( 'admin.php?page=' . $this->page ) );
        exit;
    }

    /* ======================================================
     * SAVE (зберігаємо всі параметри)
     * ==================================================== */
    protected function maybe_save(): void {

        // після reset ми сюди не дійдемо (exit)
        $new = [];


        // 🔴 Базові параметри таблиці (пошук, сортування, пагінація)
        // 🔴 PAGED: зберігаємо тільки якщо > 1 (paged=1 — це значення за замовчуванням)
        if ( isset( $_GET['paged'] ) || isset( $_POST['paged'] ) ) {
            $paged_val = max( 1, (int) ( $_GET['paged'] ?? $_POST['paged'] ?? 1 ) );
            if ( $paged_val > 1 ) {
                $new['paged'] = $paged_val;
            } else {
                // 🔴 Видаляємо paged=1 зі стану (щоб кнопка "Перша сторінка" працювала)
                unset( $this->state['paged'] );
            }
        }


        if ( isset( $_GET['s'] ) || isset( $_POST['s'] ) ) {
            $search = $_GET['s'] ?? $_POST['s'] ?? '';
            $new['s'] = sanitize_text_field( wp_unslash( $search ) );
        }

        if ( isset( $_GET['orderby'] ) || isset( $_POST['orderby'] ) ) {
            $orderby = $_GET['orderby'] ?? $_POST['orderby'] ?? '';
            $new['orderby'] = sanitize_text_field( wp_unslash( $orderby ) );
        }

        if ( isset( $_GET['order'] ) || isset( $_POST['order'] ) ) {
            $order = $_GET['order'] ?? $_POST['order'] ?? '';
            $new['order'] = sanitize_key( wp_unslash( $order ) );
        }

        // 🔴 Фільтри з POST (при натисканні Filter)
        foreach ( $_POST as $key => $val ) {
            if ( str_starts_with( $key, 'filter-' ) || str_starts_with( $key, 'field-' ) ) {
                if ( is_array( $val ) ) {
                    $new[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $val ) );
                } else {
                    $new[ $key ] = sanitize_text_field( wp_unslash( $val ) );
                }
            }
        }

        // 🔴 Фільтри з GET (якщо користувач вручну в URL додав)
        foreach ( $_GET as $key => $val ) {
            if ( str_starts_with( $key, 'filter-' ) || str_starts_with( $key, 'field-' ) ) {
                if ( is_array( $val ) ) {
                    $new[ $key ] = array_map( 'sanitize_text_field', wp_unslash( $val ) );
                } else {
                    $new[ $key ] = sanitize_text_field( wp_unslash( $val ) );
                }
            }
        }

        // 🔴 ЗБЕРІГАЄМО період (якщо він є)
        if ( isset( $this->state['date1'] ) ) {
            $new['date1'] = $this->state['date1'];
        }
        if ( isset( $this->state['date2'] ) ) {
            $new['date2'] = $this->state['date2'];
        }

        // 🔴 Зберегти період з POST (кнопка Apply)
        if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['button_period'] ) ) {
            if ( isset( $_POST['pdate-date1'] ) ) {
                $new['date1'] = sanitize_text_field( wp_unslash( $_POST['pdate-date1'] ) );
            }
            if ( isset( $_POST['pdate-date2'] ) ) {
                $new['date2'] = sanitize_text_field( wp_unslash( $_POST['pdate-date2'] ) );
            }
        }

        // 🔴 Зберігаємо ВСЕ разом
        if ( ! empty( $new ) ) {
            $merged = array_merge( $this->state, $new );

            // 🔴 Видаляємо порожні значення (крім date1/date2)
            foreach ($merged as $key => $value) {
                if ($value === '' && !in_array($key, ['date1', 'date2'])) {
                    unset($merged[$key]);
                }
            }

            update_user_meta( $this->user_id, $this->meta_key, $merged );
            $this->state = $merged;

        }
    }

    /* ======================================================
     * RESTORE (відновлюємо значення в $_REQUEST для html_select2 та інших)
     * ==================================================== */
    protected function restore_to_request(): void {
        // 🔴 Якщо є новий пошук/фільтр — скидаємо paged зі стану
        $has_new_search = isset( $_REQUEST['s'] ) && $_REQUEST['s'] !== '';

        $has_new_filter = false;
        foreach ( $_REQUEST as $k => $v ) {
            if ( ( str_starts_with( $k, 'filter-' ) || str_starts_with( $k, 'field-' ) ) && $v !== '' ) {
                $has_new_filter = true;
                break;
            }
        }

        if ( $has_new_search || $has_new_filter ) {
            unset( $this->state['paged'] );
            // 🔴 Примусово встановлюємо paged=1 в запит
            $_GET['paged'] = 1;
            $_POST['paged'] = 1;
            $_REQUEST['paged'] = 1;
        }

        // 🔴 Відновлюємо ВСІ параметри в $_REQUEST та $_GET
        foreach ( $this->state as $key => $value ) {
            // Для фільтрів та полів
            if ( str_starts_with( $key, 'filter-' ) || str_starts_with( $key, 'field-' ) ) {
                if ( ! isset( $_REQUEST[ $key ] ) || $_REQUEST[ $key ] === '' ) {
                    $_REQUEST[ $key ] = $value;
                }
                if ( ! isset( $_GET[ $key ] ) || $_GET[ $key ] === '' ) {
                    $_GET[ $key ] = $value;
                }
            }

        // 🔴 Відновлюємо параметри таблиці (пошук, сортування, пагінація)
        // 🔴 PAGED: не відновлюємо, якщо його немає в URL (щоб "Перша сторінка" працювала)
            if ( in_array( $key, ['s', 'orderby', 'order'] ) ) {
                if ( ! isset( $_REQUEST[ $key ] ) || $_REQUEST[ $key ] === '' ) {
                    $_REQUEST[ $key ] = $value;
                }
                if ( ! isset( $_GET[ $key ] ) || $_GET[ $key ] === '' ) {
                    $_GET[ $key ] = $value;
                }
            }
        // 🔴 PAGED обробляється окремо — тільки якщо явно вказаний в URL
        }
    }

    /* ======================================================
     * GETTERS
     * ==================================================== */
    public function get( string $key, $default = null ) {
        return $this->state[ $key ] ?? $default;
    }

    public function all(): array {
        return $this->state;
    }

    public function set( string $key, $value ): void {
        $this->state[ $key ] = $value;
        update_user_meta( $this->user_id, $this->meta_key, $this->state );
    }

    public function remove( string $key ): void {
        unset( $this->state[ $key ] );
        update_user_meta( $this->user_id, $this->meta_key, $this->state );
    }

    /* ======================================================
     * ПОБУДУВАТИ URL ПОВЕРНЕННЯ
     * ==================================================== */
    public function build_return_url(): string {
        $params = [];

        // 🔴 Базові параметри
        $params['page'] = $this->page;

        // Додаємо всі збережені параметри
        foreach ($this->state as $key => $value) {
            if ($key === 'date1' || $key === 'date2') {
                continue; // Період обробляється окремо через POST
            }

            if ($value !== '' && !is_array($value)) {
                $params[$key] = $value;
            }
        }

        $url = admin_url('admin.php?' . http_build_query($params));

        return $url;
    }
}
