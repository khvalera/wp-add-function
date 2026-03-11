<?php
// pages-export.php
// Експорт даних з таблиць (журналів та довідників)
//
// Ключова ідея універсальності:
// 1) Експортер завжди показує рядки (за період / на сторінці) — бо це є для будь-якої таблиці.
// 2) Додаткові підсумкові поля (сума/кількість/будь-що) НЕ “зашиті” в експортері.
//    Їх передає конкретна таблиця через метод get_export_totals() (структуровано).
// 3) Для довідників / таблиць без totals — блок “Total” покаже лише рядки.
//
// Рекомендований контракт для таблиці (опціонально):
// public function get_export_totals(): array {
//   return [
//     [
//       'label'    => __( 'Quantity', 'your-domain' ),
//       'period'   => 3974.11,      // значення за період (може бути null)
//       'page'     => 456.87,       // значення на цій сторінці (може бути null)
//       'type'     => 'number',     // 'number' | 'int' | 'money' | 'text'
//       'decimals' => 2,            // для number/money
//     ],
//     ...
//   ];
// }
//
// Також опціонально:
// public function export_include_table(): bool { return true; }
//
// Додатково опціонально для layout:
// public function get_export_column_layout(): array {
//   return [
//     'column_key' => [
//       'width'      => 14,          // ширина у %
//       'head_align' => 'center',    // left|center|right
//       'body_align' => 'left',      // left|center|right
//     ],
//   ];
// }

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * BaseExporter - базовий клас для експорту даних.
 */
abstract class BaseExporter {

    protected Base_List_Table $table;

    public function __construct( Base_List_Table $table ) {
        $this->table = $table;
    }

    /* ======================================================
     * DATA
     * ==================================================== */

    /**
     * Визначаємо колонки для експорту.
     */
    protected function prepare_columns(): array {

        $columns = $this->table->get_columns();

        // Видаляємо колонку з чекбоксами та actions.
        unset( $columns['cb'], $columns['actions'] );

        // Прибираємо приховані колонки (як в UI).
        if ( function_exists( 'get_current_screen' ) ) {
            $screen = get_current_screen();
            if ( $screen ) {
                $hidden = get_hidden_columns( $screen );
                foreach ( $hidden as $key ) {
                    unset( $columns[ $key ] );
                }
            }
        }

        return $columns;
    }

    /**
     * Гарантуємо, що items заповнені з урахуванням search/sort/filter/paging.
     */
    protected function ensure_items_prepared(): void {
        if ( method_exists( $this->table, 'prepare_items' ) ) {
            $this->table->prepare_items();
        }
    }

    /**
     * Отримуємо та нормалізуємо значення колонки.
     */
    protected function get_column_value( $item, string $column_key ): string {

        $method_name = 'column_' . $column_key;

        if ( method_exists( $this->table, $method_name ) ) {
            ob_start();
            call_user_func( [ $this->table, $method_name ], $item );
            $value = ob_get_clean();
        } elseif ( method_exists( $this->table, 'column_default' ) ) {
            $value = $this->table->column_default( $item, $column_key );
        } else {
            $value = is_array( $item ) && isset( $item[ $column_key ] ) ? $item[ $column_key ] : '';
        }

        $value = (string) $value;
        $value = html_entity_decode( $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
        $value = str_replace( [ "\xC2\xA0", '&nbsp;' ], ' ', $value );
        $value = trim( wp_strip_all_tags( $value ) );
        $value = preg_replace( '/[ \t]+/u', ' ', $value );

        return $value;
    }

    /* ======================================================
     * CONTEXT (HEADER)
     * ==================================================== */

    /**
     * Повертає масив контексту експорту:
     *  - search/sort/paging
     *  - активні filter-* / pdate-* і f[]/t[]
     *  - period_from / period_to (з pdate-date1 / pdate-date2)
     */
    protected function build_export_context(): array {

        $ctx = [];

        $ctx['search']  = isset( $_REQUEST['s'] ) ? trim( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : '';
        $ctx['orderby'] = isset( $_REQUEST['orderby'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : '';
        $ctx['order']   = isset( $_REQUEST['order'] ) ? strtolower( sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) ) : '';
        $ctx['paged']   = isset( $_REQUEST['paged'] ) ? max( 1, (int) $_REQUEST['paged'] ) : 1;

        $filters = [];

        foreach ( $_REQUEST as $k => $v ) {
            if ( ! is_string( $k ) ) {
                continue;
            }

            if ( $k === '_wpnonce' || $k === 'action' || $k === 'export_format' ) {
                continue;
            }
            if ( str_starts_with( $k, 'button_' ) ) {
                continue;
            }

            $is_filter = str_starts_with( $k, 'filter-' ) || str_starts_with( $k, 'pdate-' );
            if ( ! $is_filter ) {
                continue;
            }

            if ( is_array( $v ) ) {
                $vv = array_map( static fn( $x ) => sanitize_text_field( wp_unslash( $x ) ), $v );
                $vv = array_filter( $vv, static fn( $x ) => $x !== '' );
                if ( $vv !== [] ) {
                    $filters[ $k ] = $vv;
                }
            } else {
                $vv = sanitize_text_field( wp_unslash( $v ) );
                if ( $vv !== '' ) {
                    $filters[ $k ] = $vv;
                }
            }
        }

        if ( isset( $_REQUEST['f'] ) && is_array( $_REQUEST['f'] ) ) {
            $arr = [];
            foreach ( $_REQUEST['f'] as $k => $v ) {
                $vv = sanitize_text_field( wp_unslash( $v ) );
                if ( $vv !== '' ) {
                    $arr[ sanitize_key( $k ) ] = $vv;
                }
            }
            if ( $arr ) {
                $filters['f'] = $arr;
            }
        }

        if ( isset( $_REQUEST['t'] ) && is_array( $_REQUEST['t'] ) ) {
            $arr = [];
            foreach ( $_REQUEST['t'] as $k => $v ) {
                $vv = sanitize_text_field( wp_unslash( $v ) );
                if ( $vv !== '' ) {
                    $arr[ sanitize_key( $k ) ] = $vv;
                }
            }
            if ( $arr ) {
                $filters['t'] = $arr;
            }
        }

        $d1 = isset( $filters['pdate-date1'] ) ? (string) $filters['pdate-date1'] : '';
        $d2 = isset( $filters['pdate-date2'] ) ? (string) $filters['pdate-date2'] : '';

        if ( $d1 !== '' ) {
            $ctx['period_from'] = $d1;
        }
        if ( $d2 !== '' ) {
            $ctx['period_to'] = $d2;
        }

        unset( $filters['pdate-date1'], $filters['pdate-date2'] );

        $ctx['filters'] = $filters;

        return $ctx;
    }

    /**
     * Форматує період для відображення.
     */
    protected function format_period( array $ctx ): string {

        $from = isset( $ctx['period_from'] ) ? trim( (string) $ctx['period_from'] ) : '';
        $to   = isset( $ctx['period_to'] ) ? trim( (string) $ctx['period_to'] ) : '';

        if ( $from !== '' && $to !== '' ) {
            return sprintf( __( "from '%1\$s' to '%2\$s'", 'wp-add-function' ), $from, $to );
        }
        if ( $from !== '' ) {
            return sprintf( __( "from '%s'", 'wp-add-function' ), $from );
        }
        if ( $to !== '' ) {
            return sprintf( __( "to '%s'", 'wp-add-function' ), $to );
        }

        return '';
    }

    /**
     * Підсумок для експорту.
     */
    protected function build_export_summary( int $rows_exported ): array {

        $summary = [
            'rows_exported' => $rows_exported,
        ];

        if ( method_exists( $this->table, 'get_pagination_arg' ) ) {
            $total_items = (int) $this->table->get_pagination_arg( 'total_items' );
            $total_pages = (int) $this->table->get_pagination_arg( 'total_pages' );

            if ( $total_items > 0 ) {
                $summary['total_items'] = $total_items;
            }
            if ( $total_pages > 0 ) {
                $summary['total_pages'] = $total_pages;
            }
        }

        if ( method_exists( $this->table, 'get_export_summary' ) ) {
            try {
                $extra = $this->table->get_export_summary();
                if ( is_array( $extra ) ) {
                    $summary['table_summary'] = $extra;
                }
            } catch ( Throwable $e ) {
                // не критично
            }
        }

        return $summary;
    }

    /* ======================================================
     * HEADER VIEW HELPERS
     * ==================================================== */

    /**
     * Назва експорту.
     */
    protected function get_export_title(): string {
        if ( method_exists( $this->table, 'get_export_title' ) ) {
            try {
                $t = trim( wp_strip_all_tags( (string) $this->table->get_export_title() ) );
                if ( $t !== '' ) {
                    return $t;
                }
            } catch ( Throwable $e ) {
            }
        }
        return __( 'Export', 'wp-add-function' );
    }

    /**
     * Людинозрозумілий список фільтрів для шапки.
     */
    protected function get_export_filters_pretty( array $filters ): array {

        if ( method_exists( $this->table, 'get_export_filters_pretty' ) ) {
            try {
                $pretty = $this->table->get_export_filters_pretty( $filters );
                if ( is_array( $pretty ) ) {
                    $pretty = array_map( static fn( $x ) => trim( wp_strip_all_tags( (string) $x ) ), $pretty );
                    $pretty = array_values( array_filter( $pretty, static fn( $x ) => $x !== '' ) );
                    if ( $pretty ) {
                        return $pretty;
                    }
                }
            } catch ( Throwable $e ) {
            }
        }

        $out = [];
        foreach ( $filters as $k => $v ) {
            if ( is_array( $v ) ) {
                if ( $k === 'f' || $k === 't' ) {
                    foreach ( $v as $kk => $vv ) {
                        $out[] = $k . '[' . $kk . '] = ' . (string) $vv;
                    }
                } else {
                    $out[] = $k . ' = ' . implode( ',', array_map( 'strval', $v ) );
                }
            } else {
                $out[] = $k . ' = ' . (string) $v;
            }
        }

        $out = array_map( static fn( $x ) => trim( wp_strip_all_tags( (string) $x ) ), $out );
        return array_values( array_filter( $out, static fn( $x ) => $x !== '' ) );
    }

    /**
     * Загальна кількість сторінок, якщо доступна.
     */
    protected function get_total_pages(): int {
        if ( method_exists( $this->table, 'get_pagination_arg' ) ) {
            return max( 0, (int) $this->table->get_pagination_arg( 'total_pages' ) );
        }
        return 0;
    }

    /**
     * Чи потрібно експортувати табличні дані.
     */
    protected function export_include_table(): bool {
        if ( method_exists( $this->table, 'export_include_table' ) ) {
            try {
                return (bool) $this->table->export_include_table();
            } catch ( Throwable $e ) {
            }
        }
        return true;
    }

    /**
     * Універсальні totals від таблиці.
     */
    protected function get_export_totals(): array {

        $out = [];

        if ( method_exists( $this->table, 'get_export_totals' ) ) {
            try {
                $raw = $this->table->get_export_totals();
                if ( is_array( $raw ) ) {
                    foreach ( $raw as $row ) {
                        if ( ! is_array( $row ) ) {
                            continue;
                        }

                        $label = isset( $row['label'] ) ? trim( wp_strip_all_tags( (string) $row['label'] ) ) : '';
                        if ( $label === '' ) {
                            continue;
                        }

                        $type = isset( $row['type'] ) ? strtolower( (string) $row['type'] ) : 'number';
                        if ( ! in_array( $type, [ 'number', 'int', 'money', 'text' ], true ) ) {
                            $type = 'number';
                        }

                        $decimals = isset( $row['decimals'] ) ? (int) $row['decimals'] : 2;
                        if ( $decimals < 0 || $decimals > 6 ) {
                            $decimals = 2;
                        }

                        $out[] = [
                            'label'    => $label,
                            'period'   => $row['period'] ?? null,
                            'page'     => $row['page'] ?? null,
                            'type'     => $type,
                            'decimals' => $decimals,
                        ];
                    }
                }
            } catch ( Throwable $e ) {
                // не критично
            }
        }

        return $out;
    }

    /**
     * Форматування значення totals.
     */
    protected function format_total_value( $value, string $type, int $decimals ): string {

        if ( $value === null || $value === '' ) {
            return '';
        }

        switch ( $type ) {
            case 'int':
                return (string) (int) $value;

            case 'text':
                $text = html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                $text = str_replace( [ "\xC2\xA0", '&nbsp;' ], ' ', $text );
                $text = trim( wp_strip_all_tags( $text ) );
                $text = preg_replace( '/[ \t]+/u', ' ', $text );
                return $text;

            case 'money':
            case 'number':
            default:
                if ( ! is_numeric( $value ) ) {
                    $text = html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                    $text = str_replace( [ "\xC2\xA0", '&nbsp;' ], ' ', $text );
                    $text = trim( wp_strip_all_tags( $text ) );
                    $text = preg_replace( '/[ \t]+/u', ' ', $text );
                    return $text;
                }

                return number_format_i18n( (float) $value, $decimals );
        }
    }

    /* ======================================================
     * COLUMN LAYOUT (UNIVERSAL)
     * ==================================================== */

    /**
     * Layout колонок з конкретної таблиці.
     *
     * Очікується:
     * [
     *   'column_key' => [
     *     'width'      => 14,
     *     'head_align' => 'center',
     *     'body_align' => 'left',
     *   ],
     * ]
     */
    protected function get_export_column_layout(): array {

        if ( method_exists( $this->table, 'get_export_column_layout' ) ) {
            try {
                $layout = $this->table->get_export_column_layout();
                if ( is_array( $layout ) ) {
                    return $layout;
                }
            } catch ( Throwable $e ) {
                // не критично
            }
        }

        return [];
    }

    /**
     * Побудувати ширини колонок.
     */
    protected function build_column_widths( array $columns ): array {

        $layout    = $this->get_export_column_layout();
        $widths    = [];
        $remaining = 100;
        $auto_cols = [];

        foreach ( $columns as $key => $name ) {
            $col   = isset( $layout[ $key ] ) && is_array( $layout[ $key ] ) ? $layout[ $key ] : [];
            $width = isset( $col['width'] ) ? (int) $col['width'] : 0;

            if ( $width > 0 ) {
                $widths[ $key ] = $width;
                $remaining -= $width;
            } else {
                $auto_cols[] = $key;
            }
        }

        if ( $remaining < count( $auto_cols ) ) {
            $remaining = count( $auto_cols );
        }

        $auto_width = $auto_cols ? floor( $remaining / count( $auto_cols ) ) : 0;
        foreach ( $auto_cols as $key ) {
            $widths[ $key ] = $auto_width;
        }

        $sum_widths = array_sum( $widths );
        if ( $sum_widths !== 100 && ! empty( $columns ) ) {
            $last_key = array_key_last( $columns );
            $widths[ $last_key ] += ( 100 - $sum_widths );
        }

        return $widths;
    }

    /**
     * Вирівнювання заголовка колонки.
     */
    protected function get_export_column_head_align( string $column_key ): string {

        $layout = $this->get_export_column_layout();
        $align  = isset( $layout[ $column_key ]['head_align'] ) ? strtolower( (string) $layout[ $column_key ]['head_align'] ) : 'center';

        return in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'center';
    }

    /**
     * Вирівнювання даних колонки.
     */
    protected function get_export_column_body_align( string $column_key ): string {

        $layout = $this->get_export_column_layout();
        $align  = isset( $layout[ $column_key ]['body_align'] ) ? strtolower( (string) $layout[ $column_key ]['body_align'] ) : 'left';

        return in_array( $align, [ 'left', 'center', 'right' ], true ) ? $align : 'left';
    }

    /**
     * Виконати експорт.
     */
    abstract public function export(): void;
}

/**
 * CSVExporter - експорт у CSV.
 */
class CSVExporter extends BaseExporter {

    /**
     * PHP 8.4+: fputcsv() вимагає явний $escape.
     */
    private function putcsv( $out, array $row ): void {
        fputcsv( $out, $row, ';', '"', '\\' );
    }

    /**
     * Виконує CSV-експорт.
     */
    public function export(): void {

        $this->ensure_items_prepared();

        $columns = $this->prepare_columns();
        $items   = $this->table->get_all_items_for_export();
        $context = $this->build_export_context();

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: inline; filename="export_' . date( 'Y-m-d_H-i-s' ) . '.csv"' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        $out = fopen( 'php://output', 'w' );

        fprintf( $out, chr(0xEF) . chr(0xBB) . chr(0xBF) );

        $this->csv_write_context( $out, $context );

        $headers = [];
        foreach ( $columns as $key => $name ) {
            $headers[] = trim( wp_strip_all_tags( (string) $name ) );
        }
        $this->putcsv( $out, $headers );

        foreach ( $items as $item ) {
            $row = [];
            foreach ( $columns as $key => $name ) {
                $row[] = $this->get_column_value( $item, $key );
            }
            $this->putcsv( $out, $row );
        }

        fclose( $out );
        exit;
    }

    /**
     * Записує контекст експорту в CSV як коментарні рядки.
     */
    private function csv_write_context( $out, array $ctx ): void {

        $lines   = [];
        $lines[] = sprintf( '%s %s', __( 'Exported:', 'wp-add-function' ), wp_date( 'Y-m-d H:i:s' ) );

        $period = $this->format_period( $ctx );
        if ( $period !== '' ) {
            $lines[] = sprintf( '%s %s', __( 'Period:', 'wp-add-function' ), $period );
        }

        $pretty_filters = $this->get_export_filters_pretty( (array) ( $ctx['filters'] ?? [] ) );
        if ( $pretty_filters ) {
            $lines[] = sprintf( '%s %s', __( 'Filters:', 'wp-add-function' ), implode( '; ', $pretty_filters ) );
        }

        foreach ( $lines as $line ) {
            $line = trim( (string) $line );
            if ( $line === '' ) {
                continue;
            }
            $this->putcsv( $out, [ '# ' . $line ] );
        }
    }
}

/**
 * HTMLExporter - експорт у HTML.
 */
class HTMLExporter extends BaseExporter {

    /**
     * Виконує HTML-експорт.
     */
    public function export(): void {

        $this->ensure_items_prepared();

        $columns = $this->prepare_columns();
        $items   = $this->table->get_all_items_for_export();
        $context = $this->build_export_context();
        $summary = $this->build_export_summary( count( $items ) );

        header( 'Content-Type: text/html; charset=utf-8' );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo '<!doctype html><html><head>';
        echo '<meta charset="UTF-8">';
        echo '<style>';

        echo 'body{font-family:Arial,sans-serif;font-size:14px;color:#111;}';
        echo 'table{border-collapse:collapse;width:100%;margin-top:10px;}';
        echo 'th,td{border:1px solid #ddd;padding:6px 8px;vertical-align:top;}';
        echo 'th{background:#f2f2f2;}';

        echo '.export-header{margin:0 0 14px 0;}';
        echo '.export-meta{display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;}';
        echo '.export-title{text-align:center;font-size:20px;font-weight:700;margin:6px 0 10px;}';
        echo '.export-line{text-align:center;font-size:14px;margin:4px 0;}';
        echo '.export-line strong{font-weight:700;}';
        echo '.export-filters{text-align:center;font-size:14px;margin-top:10px;}';

        echo '.export-total{margin-top:10px;width:100%;}';
        echo '.export-total td.val{font-weight:700;text-align:right;white-space:nowrap;font-variant-numeric:tabular-nums;}';
        echo '.export-total td.center{font-weight:700;text-align:center;font-variant-numeric:tabular-nums;}';
        echo '.export-total .title-row td{background:#f2f2f2;font-weight:700;}';
        echo '.export-total td.muted{color:#555;}';

        echo '</style>';
        echo '</head><body>';

        $title          = $this->get_export_title();
        $period_text    = $this->format_period( $context );
        $pretty_filters = $this->get_export_filters_pretty( (array) ( $context['filters'] ?? [] ) );
        $filters_text   = $pretty_filters ? implode( '; ', $pretty_filters ) : '';

        echo '<div class="export-header">';
        echo '<div class="export-meta">';
        echo '<div>' . esc_html__( 'Exported:', 'wp-add-function' ) . ' ' . esc_html( wp_date( 'Y-m-d H:i:s' ) ) . '</div>';
        echo '</div>';

        echo '<div class="export-title">' . esc_html( $title ) . '</div>';

        if ( $period_text !== '' ) {
            echo '<div class="export-line"><strong>' . esc_html__( 'Period:', 'wp-add-function' ) . '</strong> ' . esc_html( $period_text ) . '</div>';
        }

        if ( $filters_text !== '' ) {
            echo '<div class="export-filters"><strong>' . esc_html__( 'Filters:', 'wp-add-function' ) . '</strong> ' . esc_html( $filters_text ) . '</div>';
        }

        echo '</div>';

        if ( $this->export_include_table() ) {
            $widths = $this->build_column_widths( $columns );

            echo '<table><tr>';
            foreach ( $columns as $key => $name ) {
                $head_align = $this->get_export_column_head_align( $key );
                echo '<th style="width:' . (int) $widths[ $key ] . '%;text-align:' . esc_attr( $head_align ) . ';">' . esc_html( trim( wp_strip_all_tags( (string) $name ) ) ) . '</th>';
            }
            echo '</tr>';

            foreach ( $items as $item ) {
                echo '<tr>';
                foreach ( $columns as $key => $name ) {
                    $val        = $this->get_column_value( $item, $key );
                    $body_align = $this->get_export_column_body_align( $key );
                    echo '<td style="text-align:' . esc_attr( $body_align ) . ';">' . esc_html( $val ) . '</td>';
                }
                echo '</tr>';
            }

            echo '</table>';
        }

        $total_items = isset( $summary['total_items'] ) ? (int) $summary['total_items'] : 0;
        $extra       = $this->get_export_totals();

        echo '<table class="export-total">';
        echo '<tr class="title-row"><td colspan="4">' . esc_html__( 'Total', 'wp-add-function' ) . ':</td></tr>';

        echo '<tr>';
        echo '<td>' . esc_html__( 'Total rows for period', 'wp-add-function' ) . ':</td>';
        echo '<td class="center">' . (int) $total_items . '</td>';
        echo '</tr>';

        if ( $extra ) {
            foreach ( $extra as $r ) {
                $label      = (string) $r['label'];
                $type       = (string) $r['type'];
                $decimals   = (int) $r['decimals'];
                $period_val = $this->format_total_value( $r['period'] ?? null, $type, $decimals );

                echo '<tr>';
                echo '<td>' . esc_html( sprintf( __( '%s for period', 'wp-add-function' ), $label ) ) . ':</td>';
                echo '<td class="center">' . esc_html( $period_val ) . '</td>';
                echo '</tr>';
            }
        } else {
            echo '<tr>';
            echo '<td class="muted" colspan="4">' . esc_html__( 'No additional totals provided by the table.', 'wp-add-function' ) . '</td>';
            echo '</tr>';
        }

        echo '</table>';

        echo '</body></html>';
        exit;
    }
}

/**
 * PDFExporter - експорт у PDF (через TCPDF).
 */
class PDFExporter extends BaseExporter {

    /**
     * Формує PDF у пам'яті та повертає ім'я файла і бінарні дані.
     */
    protected function build_pdf_binary(): array {

        $this->ensure_items_prepared();

        $columns = $this->prepare_columns();
        $items   = $this->table->get_all_items_for_export();
        $context = $this->build_export_context();
        $summary = $this->build_export_summary( count( $items ) );

        $pdf = new \TCPDF( 'L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

        $pdf->SetCreator( PDF_CREATOR );
        $pdf->SetAuthor( 'Counterparty Cards' );
        $pdf->SetTitle( $this->get_export_title() );
        $pdf->SetSubject( 'Export' );
        $pdf->SetKeywords( '' );

        $pdf->setPrintHeader( false );
        $pdf->setPrintFooter( false );

        $pdf->SetMargins( 10, 10, 10 );
        $pdf->SetAutoPageBreak( true, 10 );
        $pdf->AddPage();

        $title          = $this->get_export_title();
        $period_text    = $this->format_period( $context );
        $pretty_filters = $this->get_export_filters_pretty( (array) ( $context['filters'] ?? [] ) );
        $total_items    = isset( $summary['total_items'] ) ? (int) $summary['total_items'] : 0;

        $pdf->SetFont( 'dejavusans', '', 8 );
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            '<span><strong>' . esc_html__( 'Exported:', 'wp-add-function' ) . '</strong> ' . esc_html( wp_date( 'Y-m-d H:i:s' ) ) . '</span>',
                            0,
                            1,
                            false,
                            true,
                            'L',
                            true
        );

        $pdf->SetFont( 'dejavusans', 'B', 14 );
        $pdf->writeHTMLCell(
            0,
            0,
            '',
            '',
            '<span>' . esc_html( $title ) . '</span>',
                            0,
                            1,
                            false,
                            true,
                            'C',
                            true
        );

        $pdf->Ln( 2 );

        if ( $period_text !== '' ) {
            $pdf->SetFont( 'dejavusans', '', 9 );
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<span><strong>' . esc_html__( 'Period:', 'wp-add-function' ) . '</strong> ' . esc_html( $period_text ) . '</span>',
                                0,
                                1,
                                false,
                                true,
                                'C',
                                true
            );
        }

        if ( $pretty_filters ) {
            $pdf->SetFont( 'dejavusans', '', 8.5 );
            $pdf->writeHTMLCell(
                0,
                0,
                '',
                '',
                '<span><strong>' . esc_html__( 'Filters:', 'wp-add-function' ) . '</strong> ' . esc_html( implode( '; ', $pretty_filters ) ) . '</span>',
                                0,
                                1,
                                false,
                                true,
                                'C',
                                true
            );
        }

        $pdf->Ln( 3 );

        if ( $this->export_include_table() ) {

            $widths = $this->build_column_widths( $columns );

            $html  = '<style>';
            $html .= 'table{border-collapse:collapse;width:100%;}';
            $html .= 'th{border:1px solid #999;background-color:#f2f2f2;font-weight:bold;padding:4px;vertical-align:middle;}';
            $html .= 'td{border:1px solid #ccc;padding:4px;vertical-align:top;}';
            $html .= '</style>';

            $html .= '<table cellpadding="3">';
            $html .= '<tr>';

            foreach ( $columns as $key => $name ) {
                $label      = trim( wp_strip_all_tags( (string) $name ) );
                $head_align = $this->get_export_column_head_align( $key );
                $html      .= '<th align="' . esc_attr( $head_align ) . '" width="' . (int) $widths[ $key ] . '%"><b>' . esc_html( $label ) . '</b></th>';
            }

            $html .= '</tr>';

            foreach ( $items as $item ) {
                $html .= '<tr>';

                foreach ( $columns as $key => $name ) {
                    $val = $this->get_column_value( $item, $key );
                    $val = html_entity_decode( $val, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
                    $val = str_replace( [ "\xC2\xA0", '&nbsp;' ], ' ', $val );
                    $val = preg_replace( '/[ \t]+/u', ' ', $val );

                    $body_align = $this->get_export_column_body_align( $key );
                    $html      .= '<td align="' . esc_attr( $body_align ) . '" width="' . (int) $widths[ $key ] . '%">' . nl2br( esc_html( $val ) ) . '</td>';
                }

                $html .= '</tr>';
            }

            $html .= '</table>';

            $pdf->SetFont( 'dejavusans', '', 8 );
            $pdf->writeHTML( $html, true, false, true, false, '' );
        }

        $totals_html  = '<table cellpadding="4">';
        $totals_html .= '<tr><td style="background-color:#f2f2f2;font-weight:bold;"><strong>' . esc_html__( 'Total', 'wp-add-function' ) . ':</strong></td></tr>';
        $totals_html .= '<tr><td>' . esc_html__( 'Total rows for period', 'wp-add-function' ) . ': ' . esc_html( (string) $total_items ) . '</td></tr>';

        $extra = $this->get_export_totals();
        if ( $extra ) {
            foreach ( $extra as $r ) {
                $label      = (string) $r['label'];
                $period_val = $this->format_total_value(
                    $r['period'] ?? null,
                    (string) $r['type'],
                                                        (int) $r['decimals']
                );

                $totals_html .= '<tr><td>' . esc_html( sprintf( '%s (%s)', $label, __( 'for period', 'wp-add-function' ) ) ) . ': ' . esc_html( $period_val ) . '</td></tr>';
            }
        }

        $totals_html .= '</table>';

        $pdf->Ln( 3 );
        $pdf->writeHTML( $totals_html, true, false, true, false, '' );

        $filename = 'export_' . date( 'Y-m-d_H-i-s' ) . '.pdf';
        $pdf_data = $pdf->Output( $filename, 'S' );

        return [
            'filename' => $filename,
            'data'     => $pdf_data,
        ];
    }

    /**
     * Віддає PDF напряму в браузер.
     */
    public function export(): void {

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        $result = $this->build_pdf_binary();

        if ( function_exists( 'nocache_headers' ) ) {
            nocache_headers();
        }

        header( 'Content-Type: application/pdf' );
        header( 'Content-Disposition: inline; filename="' . $result['filename'] . '"' );
        header( 'Content-Length: ' . strlen( $result['data'] ) );
        header( 'Content-Transfer-Encoding: binary' );
        header( 'Accept-Ranges: none' );

        echo $result['data'];
        exit;
    }

    /**
     * Старий viewer через pdf.js.
     */
    public function viewer(): void {

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        $result = $this->build_pdf_binary();
        $base64 = base64_encode( $result['data'] );

        header( 'Content-Type: text/html; charset=utf-8' );
        ?>
        <!doctype html>
        <html>
        <head>
        <meta charset="utf-8">
        <title>PDF Viewer</title>
        <style>
        body {
            margin: 0;
            background: #f1f1f1;
        }
        #viewer canvas {
        display: block;
        margin: 20px auto;
        box-shadow: 0 2px 10px rgba(0,0,0,.15);
        }
        </style>
        </head>
        <body>
        <div id="viewer"></div>

        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
        const pdfData = atob("<?php echo $base64; ?>");
        const bytes = new Uint8Array(pdfData.length);

        for (let i = 0; i < pdfData.length; i++) {
            bytes[i] = pdfData.charCodeAt(i);
        }

        pdfjsLib.getDocument({data: bytes}).promise.then(function(pdf) {
            for (let i = 1; i <= pdf.numPages; i++) {
                pdf.getPage(i).then(function(page) {
                    const scale = 1.4;
                    const viewport = page.getViewport({scale: scale});
                    const canvas = document.createElement("canvas");
                    const ctx = canvas.getContext("2d");

                    canvas.height = viewport.height;
                    canvas.width = viewport.width;

                    document.getElementById("viewer").appendChild(canvas);

                    page.render({
                        canvasContext: ctx,
                        viewport: viewport
                    });
                });
            }
        });
        </script>
        </body>
        </html>
        <?php
        exit;
    }

    /**
     * Відкриває PDF у вбудованому viewer через iframe.
     */
    public function render_embedded_viewer(): void {

        while ( ob_get_level() ) {
            ob_end_clean();
        }

        $src = wpaf_get_export_url_from_request( 'pdf' );

        if ( function_exists( 'nocache_headers' ) ) {
            nocache_headers();
        }

        header( 'Content-Type: text/html; charset=UTF-8' );

        echo '<!doctype html>';
        echo '<html lang="uk">';
        echo '<head>';
        echo '<meta charset="utf-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
        echo '<title>' . esc_html( $this->get_export_title() ) . '</title>';
        echo '<style>';
        echo 'html,body{height:100%;margin:0;background:#f1f1f1;}';
        echo '.wpaf-pdf-viewer{width:100%;height:100%;border:0;display:block;background:#fff;}';
        echo '</style>';
        echo '</head>';
        echo '<body>';
        echo '<iframe class="wpaf-pdf-viewer" src="' . esc_url( $src ) . '#toolbar=1&navpanes=0&scrollbar=1"></iframe>';
        echo '</body>';
        echo '</html>';
        exit;
    }
}

/**
 * Обробник експорту.
 */
class ExportHandler {

    /**
     * Перехоплює POST від кнопок експорту та переводить на GET-URL експорту.
     */
    public static function maybe_export_redirect(): void {

        if ( ! is_admin() || ( $_SERVER['REQUEST_METHOD'] ?? '' ) !== 'POST' ) {
            return;
        }

        $format = '';
        if ( isset( $_POST['button_export_csv'] ) ) {
            $format = 'csv';
        } elseif ( isset( $_POST['button_export_html'] ) ) {
            $format = 'html';
        } elseif ( isset( $_POST['button_export_pdf'] ) ) {
            $format = 'pdf_viewer';
        }

        if ( $format === '' ) {
            return;
        }

        $export_url = wpaf_get_export_url_from_request( $format );

        wp_safe_redirect( $export_url );
        exit;
    }

    /**
     * Обробляє фінальний GET-запит на експорт.
     */
    public static function handle_export( $screen = null ): void {

        if ( ! isset( $_GET['action'] ) || $_GET['action'] !== 'export' ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_GET['_wpnonce'] ), 'export-table' ) ) {
            wp_die( __( 'Invalid export request.', 'wp-add-function' ) );
        }

        $table = self::resolve_table_instance();
        if ( ! $table ) {
            wp_die( __( 'Table not found for export.', 'wp-add-function' ) );
        }

        $format = isset( $_GET['export_format'] ) ? sanitize_text_field( wp_unslash( $_GET['export_format'] ) ) : 'csv';

        switch ( $format ) {
            case 'html':
                ( new HTMLExporter( $table ) )->export();
                break;
            case 'pdf':
                ( new PDFExporter( $table ) )->export();
                break;
            case 'pdf_viewer':
                ( new PDFExporter( $table ) )->render_embedded_viewer();
                break;
            case 'csv':
            default:
                ( new CSVExporter( $table ) )->export();
                break;
        }
    }

    /**
     * Намагається відновити інстанс таблиці для експорту.
     */
    private static function resolve_table_instance(): ?Base_List_Table {

        $gl_ = gl_form_array::get();
        if ( is_array( $gl_ ) && isset( $gl_['class-table'] ) && ( $gl_['class-table'] instanceof Base_List_Table ) ) {
            return $gl_['class-table'];
        }

        $page = '';
        if ( isset( $_GET['page'] ) && $_GET['page'] !== '' ) {
            $page = sanitize_key( wp_unslash( $_GET['page'] ) );
        }

        if ( $page === '' ) {
            $ref = wp_get_referer();
            if ( $ref ) {
                $parts = wp_parse_url( $ref );
                if ( ! empty( $parts['query'] ) ) {
                    parse_str( $parts['query'], $q );
                    if ( ! empty( $q['page'] ) ) {
                        $page = sanitize_key( $q['page'] );
                    }
                }
            }
        }

        if ( $page === '' || strpos( $page, '-' ) === false ) {
            return null;
        }

        [ $prefix, $item ] = explode( '-', $page, 2 );
        if ( $prefix === '' || $item === '' ) {
            return null;
        }

        $class = $prefix . '_class_table_' . str_replace( '-', '_', $item );
        if ( ! class_exists( $class ) ) {
            return null;
        }

        try {
            $instance = new $class();
        } catch ( Throwable $e ) {
            error_log( '[EXPORT] Failed to instantiate ' . $class . ': ' . $e->getMessage() );
            return null;
        }

        try {
            gl_form_array::update( 'class-table', $instance );
        } catch ( Throwable $e ) {
            // не критично
        }

        return $instance;
    }
}

/**
 * Побудувати URL експорту зі збереженням стану таблиці.
 */
function wpaf_get_export_url_from_request( string $format = 'csv' ): string {

    $params = [];

    $keep_scalar = [ 'page', 'paged', 'orderby', 'order', 's', 'p', 'tab', 'view', 'post_type' ];
    foreach ( $keep_scalar as $k ) {
        if ( isset( $_REQUEST[ $k ] ) && $_REQUEST[ $k ] !== '' ) {
            $v = wp_unslash( $_REQUEST[ $k ] );
            $params[ $k ] = is_numeric( $v ) ? (int) $v : sanitize_text_field( $v );
        }
    }

    $ref = wp_get_referer();
    if ( $ref ) {
        $parts = wp_parse_url( $ref );
        if ( ! empty( $parts['query'] ) ) {
            parse_str( $parts['query'], $q );
            foreach ( $keep_scalar as $k ) {
                if ( empty( $params[ $k ] ) && isset( $q[ $k ] ) && $q[ $k ] !== '' ) {
                    $v = wp_unslash( $q[ $k ] );
                    $params[ $k ] = is_numeric( $v ) ? (int) $v : sanitize_text_field( $v );
                }
            }
        }
    }

    if ( isset( $_REQUEST['f'] ) && is_array( $_REQUEST['f'] ) ) {
        $params['f'] = array_map( static fn( $x ) => sanitize_text_field( wp_unslash( $x ) ), $_REQUEST['f'] );
    }
    if ( isset( $_REQUEST['t'] ) && is_array( $_REQUEST['t'] ) ) {
        $params['t'] = array_map( static fn( $x ) => sanitize_text_field( wp_unslash( $x ) ), $_REQUEST['t'] );
    }

    foreach ( $_REQUEST as $k => $v ) {

        if ( ! is_string( $k ) ) {
            continue;
        }

        if ( $k === '_wpnonce' || $k === 'action' || $k === 'export_format' ) {
            continue;
        }
        if ( str_starts_with( $k, 'button_' ) ) {
            continue;
        }

        if ( str_starts_with( $k, 'filter-' ) || str_starts_with( $k, 'pdate-' ) ) {
            if ( is_array( $v ) ) {
                $params[ $k ] = array_map( static fn( $x ) => sanitize_text_field( wp_unslash( $x ) ), $v );
            } else {
                if ( $v !== '' ) {
                    $params[ $k ] = sanitize_text_field( wp_unslash( $v ) );
                }
            }
        }
    }

    $params['action']        = 'export';
    $params['export_format'] = $format;
    $params['_wpnonce']      = wp_create_nonce( 'export-table' );

    return add_query_arg( $params, admin_url( 'admin.php' ) );
}

// Хуки
add_action( 'admin_init', [ ExportHandler::class, 'maybe_export_redirect' ], 4 );
add_action( 'current_screen', [ ExportHandler::class, 'handle_export' ], 20 );
