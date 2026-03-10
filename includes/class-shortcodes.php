<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FCC_Cats_Shortcodes {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode( 'fcc_cats', [ $this, 'render_cats' ] );
        add_shortcode( 'fcc_success_stories', [ $this, 'render_success_stories' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            'fcc-cats-frontend',
            FCC_CATS_URL . 'assets/cats-frontend.css',
            [],
            FCC_CATS_VERSION
        );
    }

    /**
     * [fcc_cats] — display adoptable (or all) cats
     * Attributes:
     *   status  = "adoptable" | "adopted" | "all"  (default: adoptable)
     *   columns = 2 | 3 | 4                        (default: 3)
     *   limit   = number                            (default: -1 = all)
     */
    public function render_cats( $atts ) {
        $atts = shortcode_atts( [
            'status'  => 'adoptable',
            'columns' => '3',
            'limit'   => '-1',
        ], $atts, 'fcc_cats' );

        $meta_query = [];

        if ( $atts['status'] === 'adoptable' ) {
            $meta_query = [
                'relation' => 'OR',
                [ 'key' => '_fcc_adopted', 'value' => '0' ],
                [ 'key' => '_fcc_adopted', 'compare' => 'NOT EXISTS' ],
            ];
        } elseif ( $atts['status'] === 'adopted' ) {
            $meta_query = [
                [ 'key' => '_fcc_adopted', 'value' => '1' ],
            ];
        }

        $query_args = [
            'post_type'      => 'fcc_cat',
            'post_status'    => 'publish',
            'posts_per_page' => intval( $atts['limit'] ),
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ( ! empty( $meta_query ) ) {
            $query_args['meta_query'] = $meta_query;
        }

        $cats = new WP_Query( $query_args );

        if ( ! $cats->have_posts() ) {
            return '<p class="fcc-no-cats">No cats found.</p>';
        }

        $cols = intval( $atts['columns'] );
        ob_start();

        echo '<div class="fcc-cats-grid fcc-cats-cols-' . esc_attr( $cols ) . '">';

        while ( $cats->have_posts() ) {
            $cats->the_post();
            $post_id    = get_the_ID();
            $name       = get_the_title();
            $age        = get_post_meta( $post_id, '_fcc_cat_age', true );
            $sex        = get_post_meta( $post_id, '_fcc_sex', true );
            $cat_pic    = get_post_meta( $post_id, '_fcc_cat_pic', true );
            $adopted    = get_post_meta( $post_id, '_fcc_adopted', true );
            $trait_terms = wp_get_post_terms( $post_id, 'fcc_cat_trait' );
            $best_trait  = '';
            if ( ! is_wp_error( $trait_terms ) && ! empty( $trait_terms ) ) {
                $best_trait = FCC_Cats_Taxonomy::get_trait_label( $trait_terms[0] );
            }
            $status_label = ( $adopted === '1' ) ? 'Adopted' : 'Adoptable';
            $status_class = ( $adopted === '1' ) ? 'fcc-status-adopted' : 'fcc-status-adoptable';
            ?>
            <div class="fcc-cat-card">
                <div class="fcc-cat-status <?php echo esc_attr( $status_class ); ?>">
                    <?php echo esc_html( $status_label ); ?>
                </div>
                <?php if ( $cat_pic ) : ?>
                    <div class="fcc-cat-photo">
                        <img src="<?php echo esc_url( $cat_pic ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
                    </div>
                <?php endif; ?>
                <div class="fcc-cat-info">
                    <h3 class="fcc-cat-name"><?php echo esc_html( $name ); ?></h3>
                    <div class="fcc-cat-meta">
                        <?php if ( $age ) : ?>
                            <span class="fcc-cat-age"><?php echo esc_html( $age ); ?></span>
                        <?php endif; ?>
                        <?php if ( $sex ) : ?>
                            <span class="fcc-cat-sex"><?php echo esc_html( $sex ); ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ( $best_trait ) : ?>
                        <span class="fcc-cat-trait"><?php echo esc_html( $best_trait ); ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();

        echo '</div>';
        return ob_get_clean();
    }

    /**
     * [fcc_success_stories] — display adopted cats' success stories
     * Attributes:
     *   limit   = number (default: -1)
     *   columns = 2 | 3  (default: 2)
     */
    public function render_success_stories( $atts ) {
        $atts = shortcode_atts( [
            'limit'   => '-1',
            'columns' => '2',
        ], $atts, 'fcc_success_stories' );

        $query_args = [
            'post_type'      => 'fcc_cat',
            'post_status'    => 'publish',
            'posts_per_page' => intval( $atts['limit'] ),
            'orderby'        => 'meta_value',
            'meta_key'       => '_fcc_adopted_date',
            'order'          => 'DESC',
            'meta_query'     => [
                [ 'key' => '_fcc_adopted', 'value' => '1' ],
            ],
        ];

        $cats = new WP_Query( $query_args );

        if ( ! $cats->have_posts() ) {
            return '<p class="fcc-no-cats">No success stories yet.</p>';
        }

        $cols = intval( $atts['columns'] );
        ob_start();

        echo '<div class="fcc-stories-grid fcc-stories-cols-' . esc_attr( $cols ) . '">';

        while ( $cats->have_posts() ) {
            $cats->the_post();
            $post_id      = get_the_ID();
            $name         = get_the_title();
            $adopted_pic  = get_post_meta( $post_id, '_fcc_adopted_pic', true );
            $cat_pic      = get_post_meta( $post_id, '_fcc_cat_pic', true );
            $parents_name = get_post_meta( $post_id, '_fcc_adopted_parents_name', true );
            $story        = get_post_meta( $post_id, '_fcc_success_story', true );
            $photo        = $adopted_pic ?: $cat_pic;
            ?>
            <div class="fcc-story-card">
                <?php if ( $photo ) : ?>
                    <div class="fcc-story-photo">
                        <img src="<?php echo esc_url( $photo ); ?>" alt="<?php echo esc_attr( $name ); ?>" loading="lazy" />
                    </div>
                <?php endif; ?>
                <div class="fcc-story-content">
                    <?php if ( $story ) : ?>
                        <blockquote class="fcc-story-quote">
                            <?php echo esc_html( $story ); ?>
                        </blockquote>
                    <?php endif; ?>
                    <?php if ( $parents_name ) : ?>
                        <cite class="fcc-story-credit">~ <?php echo esc_html( $parents_name ); ?></cite>
                    <?php endif; ?>
                </div>
            </div>
            <?php
        }
        wp_reset_postdata();

        echo '</div>';
        return ob_get_clean();
    }
}
