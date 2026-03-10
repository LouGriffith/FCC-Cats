<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the "Cat Trait" taxonomy for fcc_cat.
 * - Tag-style (non-hierarchical).
 * - Each trait can have an emoji stored as term meta (fcc_trait_emoji).
 * - Custom radio meta box enforces single-selection on the cat edit screen.
 * - Emoji field appears on the Traits taxonomy edit/add screens.
 */
class FCC_Cats_Taxonomy {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init',            [ $this, 'register' ] );
        add_action( 'add_meta_boxes',  [ $this, 'add_trait_meta_box' ] );
        add_action( 'save_post_fcc_cat', [ $this, 'save_trait' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );

        // Emoji field on the Traits taxonomy screens
        add_action( 'fcc_cat_trait_add_form_fields',  [ $this, 'term_add_emoji_field' ] );
        add_action( 'fcc_cat_trait_edit_form_fields', [ $this, 'term_edit_emoji_field' ] );
        add_action( 'created_fcc_cat_trait',  [ $this, 'save_term_emoji' ] );
        add_action( 'edited_fcc_cat_trait',   [ $this, 'save_term_emoji' ] );

        // Show emoji in the Traits list table column
        add_filter( 'manage_edit-fcc_cat_trait_columns',        [ $this, 'term_columns' ] );
        add_filter( 'manage_fcc_cat_trait_custom_column',       [ $this, 'term_column_content' ], 10, 3 );
    }

    /* ── Taxonomy registration ───────────────────────────────── */

    public function register() {
        register_taxonomy( 'fcc_cat_trait', 'fcc_cat', [
            'labels' => [
                'name'          => 'Cat Traits',
                'singular_name' => 'Cat Trait',
                'menu_name'     => 'Traits',
                'all_items'     => 'All Traits',
                'edit_item'     => 'Edit Trait',
                'add_new_item'  => 'Add New Trait',
                'new_item_name' => 'New Trait Name',
                'search_items'  => 'Search Traits',
                'not_found'     => 'No traits found',
                'no_terms'      => 'No traits',
            ],
            'hierarchical'      => false,
            'public'            => true,
            'show_ui'           => true,
            'show_in_menu'      => true,
            'show_in_rest'      => true,
            'show_admin_column' => true,
            'rewrite'           => [ 'slug' => 'cat-trait' ],
            'meta_box_cb'       => false, // we render our own
        ] );
    }

    /* ── Emoji field on Traits taxonomy screens ──────────────── */

    public function term_add_emoji_field() {
        ?>
        <div class="form-field">
            <label for="fcc_trait_emoji">Emoji</label>
            <input type="text" id="fcc_trait_emoji" name="fcc_trait_emoji" value="" maxlength="8" style="width:80px;font-size:20px;" placeholder="🐱" />
            <p class="description">Optional emoji to display alongside this trait (e.g. 🎾).</p>
        </div>
        <?php
    }

    public function term_edit_emoji_field( $term ) {
        $emoji = get_term_meta( $term->term_id, 'fcc_trait_emoji', true );
        ?>
        <tr class="form-field">
            <th><label for="fcc_trait_emoji">Emoji</label></th>
            <td>
                <input type="text" id="fcc_trait_emoji" name="fcc_trait_emoji" value="<?php echo esc_attr( $emoji ); ?>" maxlength="8" style="width:80px;font-size:20px;" placeholder="🐱" />
                <p class="description">Optional emoji to display alongside this trait (e.g. 🎾).</p>
            </td>
        </tr>
        <?php
    }

    public function save_term_emoji( $term_id ) {
        if ( isset( $_POST['fcc_trait_emoji'] ) ) {
            $emoji = sanitize_text_field( $_POST['fcc_trait_emoji'] );
            update_term_meta( $term_id, 'fcc_trait_emoji', $emoji );
        }
    }

    /* ── Emoji column on the Traits list table ───────────────── */

    public function term_columns( $columns ) {
        $new = [];
        foreach ( $columns as $key => $label ) {
            if ( $key === 'name' ) {
                $new['fcc_emoji'] = 'Emoji';
            }
            $new[ $key ] = $label;
        }
        return $new;
    }

    public function term_column_content( $content, $column, $term_id ) {
        if ( $column === 'fcc_emoji' ) {
            $emoji = get_term_meta( $term_id, 'fcc_trait_emoji', true );
            return $emoji
                ? '<span style="font-size:20px;line-height:1;">' . esc_html( $emoji ) . '</span>'
                : '<span style="color:#ccc;">—</span>';
        }
        return $content;
    }

    /* ── Helper: get formatted label "🎾 Very Playful" ──────── */

    public static function get_trait_label( $term ) {
        $emoji = get_term_meta( $term->term_id, 'fcc_trait_emoji', true );
        return $emoji
            ? $emoji . ' ' . $term->name
            : $term->name;
    }

    /* ── Cat edit screen: radio meta box ────────────────────── */

    public function add_trait_meta_box() {
        add_meta_box(
            'fcc_cat_trait_box',
            'Best Trait',
            [ $this, 'render_trait_box' ],
            'fcc_cat',
            'side',
            'default'
        );
    }

    public function enqueue_assets( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'fcc_cat' ) return;

        wp_add_inline_style( 'wp-admin', '
            .fcc-trait-list { margin: 0; padding: 0; list-style: none; }
            .fcc-trait-list li { padding: 5px 0; border-bottom: 1px solid #f0f0f0; }
            .fcc-trait-list li:last-child { border-bottom: none; }
            .fcc-trait-list label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 13px; }
            .fcc-trait-list input[type="radio"] { margin: 0; flex-shrink: 0; }
            .fcc-trait-emoji { font-size: 18px; line-height: 1; width: 22px; text-align: center; }
            .fcc-trait-none { color: #888; font-size: 12px; font-style: italic; margin-top: 8px; }
            .fcc-trait-add { margin-top: 10px; border-top: 1px solid #eee; padding-top: 10px; }
            .fcc-trait-add-row { display: flex; gap: 6px; margin-bottom: 4px; }
            .fcc-trait-add-row input[type="text"]:first-child { width: 54px; font-size: 18px; flex-shrink: 0; text-align: center; }
            .fcc-trait-add-row input[type="text"]:last-child { flex: 1; }
            .fcc-trait-add p { font-size: 11px; color: #888; margin: 4px 0 0; }
        ' );
    }

    public function render_trait_box( $post ) {
        wp_nonce_field( 'fcc_cat_trait_save', 'fcc_cat_trait_nonce' );

        $all_traits    = get_terms( [ 'taxonomy' => 'fcc_cat_trait', 'hide_empty' => false ] );
        $current_terms = wp_get_post_terms( $post->ID, 'fcc_cat_trait' );
        $current_id    = ( ! is_wp_error( $current_terms ) && ! empty( $current_terms ) )
                         ? $current_terms[0]->term_id
                         : 0;
        ?>
        <ul class="fcc-trait-list">
            <li>
                <label>
                    <input type="radio" name="fcc_cat_trait_id" value="0" <?php checked( $current_id, 0 ); ?> />
                    <em style="color:#999;">None</em>
                </label>
            </li>
            <?php if ( ! is_wp_error( $all_traits ) ) : ?>
                <?php foreach ( $all_traits as $trait ) :
                    $emoji = get_term_meta( $trait->term_id, 'fcc_trait_emoji', true );
                ?>
                    <li>
                        <label>
                            <input type="radio" name="fcc_cat_trait_id" value="<?php echo esc_attr( $trait->term_id ); ?>" <?php checked( $current_id, $trait->term_id ); ?> />
                            <?php if ( $emoji ) : ?>
                                <span class="fcc-trait-emoji"><?php echo esc_html( $emoji ); ?></span>
                            <?php endif; ?>
                            <?php echo esc_html( $trait->name ); ?>
                        </label>
                    </li>
                <?php endforeach; ?>
            <?php endif; ?>
        </ul>

        <?php if ( ! is_wp_error( $all_traits ) && empty( $all_traits ) ) : ?>
            <p class="fcc-trait-none">No traits yet. Add one below.</p>
        <?php endif; ?>

        <div class="fcc-trait-add">
            <div class="fcc-trait-add-row">
                <input type="text" name="fcc_new_trait_emoji" placeholder="🐱" maxlength="8" title="Emoji (optional)" />
                <input type="text" name="fcc_new_trait" placeholder="Add new trait…" />
            </div>
            <p>Emoji is optional. Save the post to select the new trait.</p>
        </div>
        <?php
    }

    public function save_trait( $post_id ) {
        if ( ! isset( $_POST['fcc_cat_trait_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['fcc_cat_trait_nonce'], 'fcc_cat_trait_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Create a new trait if a name was provided
        if ( ! empty( $_POST['fcc_new_trait'] ) ) {
            $new_name  = sanitize_text_field( $_POST['fcc_new_trait'] );
            $new_emoji = isset( $_POST['fcc_new_trait_emoji'] ) ? sanitize_text_field( $_POST['fcc_new_trait_emoji'] ) : '';
            $existing  = get_term_by( 'name', $new_name, 'fcc_cat_trait' );

            if ( ! $existing ) {
                $result = wp_insert_term( $new_name, 'fcc_cat_trait' );
                if ( ! is_wp_error( $result ) ) {
                    if ( $new_emoji ) {
                        update_term_meta( $result['term_id'], 'fcc_trait_emoji', $new_emoji );
                    }
                    // Auto-select the new trait if no radio was chosen
                    if ( empty( $_POST['fcc_cat_trait_id'] ) ) {
                        wp_set_post_terms( $post_id, [ $result['term_id'] ], 'fcc_cat_trait' );
                        return;
                    }
                }
            }
        }

        // Apply the selected trait (single term only)
        $selected_id = isset( $_POST['fcc_cat_trait_id'] ) ? intval( $_POST['fcc_cat_trait_id'] ) : 0;

        if ( $selected_id > 0 ) {
            wp_set_post_terms( $post_id, [ $selected_id ], 'fcc_cat_trait' );
        } else {
            wp_set_post_terms( $post_id, [], 'fcc_cat_trait' );
        }
    }
}
