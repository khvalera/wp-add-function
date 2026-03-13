<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WAF_Tiles_Renderer {

    public static function render( array $items, array $schema, array $screen ) : string {
        ob_start();

        if ( empty( $items ) ) {
            $empty_title   = $schema['empty_title'] ?? __( 'No data found', 'wp-add-function' );
            $empty_message = $schema['empty_message'] ?? __( 'There is nothing to display.', 'wp-add-function' );
            ?>
            <div class="waf-tiles-empty-state">
                <h3><?php echo esc_html( $empty_title ); ?></h3>
                <p><?php echo esc_html( $empty_message ); ?></p>
            </div>
            <?php
            return (string) ob_get_clean();
        }

        $primary_label      = $schema['primary']['label'] ?? '';
        $primary_field      = $schema['primary']['field'] ?? '';
        $primary_sub_field  = $schema['primary']['sub_field'] ?? '';
        $rows               = $schema['rows'] ?? [];
        ?>
        <div class="waf-tiles-list">
            <?php foreach ( $items as $item ) : ?>
                <article class="waf-tile" data-item-id="<?php echo esc_attr( (string) ( $item['id'] ?? '' ) ); ?>">
                    <div class="waf-tile__visual">
                        <?php if ( $primary_label !== '' ) : ?>
                            <div class="waf-tile__primary-label"><?php echo esc_html( $primary_label ); ?></div>
                        <?php endif; ?>

                        <?php if ( $primary_field !== '' ) : ?>
                            <div class="waf-tile__primary-value"><?php echo esc_html( (string) ( $item[ $primary_field ] ?? '' ) ); ?></div>
                        <?php endif; ?>

                        <?php if ( $primary_sub_field !== '' && ! empty( $item[ $primary_sub_field ] ) ) : ?>
                            <div class="waf-tile__primary-sub"><?php echo esc_html( (string) $item[ $primary_sub_field ] ); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="waf-tile__content">
                        <?php foreach ( $rows as $row ) :
                            $label      = $row['label'] ?? '';
                            $field      = $row['field'] ?? '';
                            $class_field = $row['class_field'] ?? '';
                            $static_value_class = $row['value_class'] ?? '';
                            $value       = $field !== '' ? ( $item[ $field ] ?? '' ) : '';
                            $value_class = 'waf-tile__row-value';

                            if ( $field !== '' && empty( $value ) && isset( $row['default'] ) ) {
                                $value = $row['default'];
                            }

                            if ( $static_value_class !== '' ) {
                                $value_class .= ' ' . sanitize_html_class( (string) $static_value_class );
                            }

                            if ( $class_field !== '' && ! empty( $item[ $class_field ] ) ) {
                                $value_class .= ' ' . sanitize_html_class( (string) $item[ $class_field ] );
                            }
                        ?>
                            <div class="waf-tile__row">
                                <?php if ( $label !== '' ) : ?>
                                    <div class="waf-tile__row-label"><?php echo esc_html( rtrim( $label, ':' ) . ':' ); ?></div>
                                <?php endif; ?>
                                <div class="<?php echo esc_attr( $value_class ); ?>"><?php echo esc_html( (string) $value ); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
        <?php

        return (string) ob_get_clean();
    }
}
