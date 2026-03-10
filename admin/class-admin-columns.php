<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FCC_Cats_Admin_Columns {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter( 'manage_fcc_cat_posts_columns', [ $this, 'add_columns' ] );
        add_action( 'manage_fcc_cat_posts_custom_column', [ $this, 'render_column' ], 10, 2 );
        add_filter( 'manage_edit-fcc_cat_sortable_columns', [ $this, 'sortable_columns' ] );
        add_action( 'pre_get_posts', [ $this, 'sort_query' ] );
        add_action( 'admin_head', [ $this, 'column_styles' ] );
    }

    public function add_columns( $columns ) {
        return [
            'cb'          => $columns['cb'],
            'title'       => $columns['title'],
            'fcc_status'  => 'Status',
            'fcc_arrived' => 'Date Added',
            'fcc_trait'   => 'Best Trait',
        ];
    }

    public function render_column( $column, $post_id ) {
        switch ( $column ) {
            case 'fcc_photo':
                $cat_pic = get_post_meta( $post_id, '_fcc_cat_pic', true );
                if ( $cat_pic ) {
                    echo '<img src="' . esc_url( $cat_pic ) . '" style="width:50px;height:50px;object-fit:cover;border-radius:50%;display:block;" alt="" />';
                } else {
                    echo '<span style="color:#ccc;font-size:24px;">🐱</span>';
                }
                break;

            case 'fcc_status':
                $adopted = get_post_meta( $post_id, '_fcc_adopted', true );
                if ( $adopted === '1' ) {
                    $adopted_date = get_post_meta( $post_id, '_fcc_adopted_date', true );
                    echo '<span style="display:inline-block;background:#d4edda;color:#155724;padding:3px 10px;border-radius:3px;font-size:12px;font-weight:600;">Adopted</span>';
                    if ( $adopted_date ) {
                        echo '<br><small style="color:#888;">' . esc_html( date( 'M j, Y', strtotime( $adopted_date ) ) ) . '</small>';
                    }
                } else {
                    echo '<span style="display:inline-block;background:#fff3cd;color:#856404;padding:3px 10px;border-radius:3px;font-size:12px;font-weight:600;">Adoptable</span>';
                }
                break;

            case 'fcc_age':
                $age = get_post_meta( $post_id, '_fcc_cat_age', true );
                echo $age ? esc_html( $age ) : '<span style="color:#999;">—</span>';
                break;

            case 'fcc_sex':
                $sex = get_post_meta( $post_id, '_fcc_sex', true );
                echo $sex ? esc_html( $sex ) : '<span style="color:#999;">—</span>';
                break;

            case 'fcc_trait':
                $terms = wp_get_post_terms( $post_id, 'fcc_cat_trait' );
                if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                    echo esc_html( FCC_Cats_Taxonomy::get_trait_label( $terms[0] ) );
                } else {
                    echo '<span style="color:#999;">—</span>';
                }
                break;

            case 'fcc_arrived':
                $arrived = get_post_meta( $post_id, '_fcc_arrived', true );
                if ( $arrived ) {
                    echo esc_html( date( 'M j, Y', strtotime( $arrived ) ) );
                } else {
                    echo '<span style="color:#999;">—</span>';
                }
                break;
        }
    }

    public function sortable_columns( $columns ) {
        $columns['fcc_status']  = 'fcc_adopted';
        $columns['fcc_arrived'] = 'fcc_arrived';
        return $columns;
    }

    public function sort_query( $query ) {
        if ( ! is_admin() || ! $query->is_main_query() ) return;
        $orderby = $query->get( 'orderby' );
        if ( $orderby === 'fcc_adopted' ) {
            $query->set( 'meta_key', '_fcc_adopted' );
            $query->set( 'orderby', 'meta_value' );
        }
        if ( $orderby === 'fcc_arrived' ) {
            $query->set( 'meta_key', '_fcc_arrived' );
            $query->set( 'orderby', 'meta_value' );
        }
    }

    public function column_styles() {
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'fcc_cat' ) return;
        echo '<style>
            .column-fcc_status { width: 120px; }
            .column-fcc_arrived { width: 120px; }
            .column-fcc_trait { width: 160px; }
        </style>';
    }
}
