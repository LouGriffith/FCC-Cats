<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FCC_Cats_Meta_Boxes {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', [ $this, 'register' ] );
        add_action( 'save_post_fcc_cat', [ $this, 'save' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    public function enqueue( $hook ) {
        if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ] ) ) return;
        $screen = get_current_screen();
        if ( ! $screen || $screen->post_type !== 'fcc_cat' ) return;

        wp_add_inline_script( 'jquery', "
            jQuery(function(\$) {
                function toggleAdoptionFields() {
                    var checked = \$('#fcc_adopted').is(':checked');
                    \$('.fcc-adoption-fields').toggle(checked);
                }
                toggleAdoptionFields();
                \$('#fcc_adopted').on('change', toggleAdoptionFields);
            });
        " );
    }

    public function register() {
        add_meta_box(
            'fcc_cat_details',
            'Cat Details',
            [ $this, 'render_details' ],
            'fcc_cat',
            'normal',
            'high'
        );
        add_meta_box(
            'fcc_cat_photo',
            'Cat Photo',
            [ $this, 'render_photo' ],
            'fcc_cat',
            'side',
            'high'
        );
    }

    public function render_photo( $post ) {
        wp_nonce_field( 'fcc_cat_photo_save', 'fcc_cat_photo_nonce' );
        $cat_pic = get_post_meta( $post->ID, '_fcc_cat_pic', true );
        ?>
        <style>
            .fcc-photo-preview { margin-bottom: 8px; }
            .fcc-photo-preview img { max-width: 100%; height: auto; border-radius: 6px; display:block; }
            .fcc-photo-actions { display: flex; gap: 8px; flex-wrap: wrap; }
        </style>
        <div class="fcc-photo-preview" id="fcc_cat_pic_preview">
            <?php if ( $cat_pic ) : ?>
                <img src="<?php echo esc_url( $cat_pic ); ?>" alt="Cat photo" />
            <?php endif; ?>
        </div>
        <input type="hidden" name="fcc_cat_pic" id="fcc_cat_pic" value="<?php echo esc_url( $cat_pic ); ?>" />
        <div class="fcc-photo-actions">
            <button type="button" class="button" id="fcc_cat_pic_btn"><?php echo $cat_pic ? 'Change Photo' : 'Upload Photo'; ?></button>
            <?php if ( $cat_pic ) : ?>
                <button type="button" class="button" id="fcc_cat_pic_remove">Remove</button>
            <?php endif; ?>
        </div>
        <script>
        jQuery(function($) {
            var frame;
            $('#fcc_cat_pic_btn').on('click', function(e) {
                e.preventDefault();
                if (frame) { frame.open(); return; }
                frame = wp.media({ title: 'Select Cat Photo', button: { text: 'Use this photo' }, multiple: false });
                frame.on('select', function() {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $('#fcc_cat_pic').val(attachment.url);
                    $('#fcc_cat_pic_preview').html('<img src="' + attachment.url + '" />');
                    $('#fcc_cat_pic_btn').text('Change Photo');
                });
                frame.open();
            });
            $('#fcc_cat_pic_remove').on('click', function() {
                $('#fcc_cat_pic').val('');
                $('#fcc_cat_pic_preview').html('');
                $('#fcc_cat_pic_btn').text('Upload Photo');
                $(this).hide();
            });
        });
        </script>
        <?php
    }

    public function render_details( $post ) {
        wp_nonce_field( 'fcc_cat_save', 'fcc_cat_nonce' );

        $age          = get_post_meta( $post->ID, '_fcc_cat_age', true );
        $sex          = get_post_meta( $post->ID, '_fcc_sex', true );
        $arrived      = get_post_meta( $post->ID, '_fcc_arrived', true );
        $adopted      = get_post_meta( $post->ID, '_fcc_adopted', true );
        $adopted_pic  = get_post_meta( $post->ID, '_fcc_adopted_pic', true );
        $adopted_date = get_post_meta( $post->ID, '_fcc_adopted_date', true );
        $parents_name = get_post_meta( $post->ID, '_fcc_adopted_parents_name', true );
        $success_story = get_post_meta( $post->ID, '_fcc_success_story', true );
        ?>
        <style>
            .fcc-cat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
            .fcc-cat-field { margin-bottom: 16px; }
            .fcc-cat-field label { display: block; font-weight: 600; margin-bottom: 4px; font-size: 13px; }
            .fcc-cat-field input[type="text"],
            .fcc-cat-field input[type="date"],
            .fcc-cat-field select,
            .fcc-cat-field textarea { width: 100%; }
            .fcc-section { border-top: 1px solid #ddd; margin-top: 20px; padding-top: 20px; }
            .fcc-section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; color: #444; margin: 0 0 16px; }
            .fcc-adoption-section { background: #f9fbe7; border: 1px solid #c8e6c9; border-radius: 6px; padding: 16px; margin-top: 8px; }
            .fcc-adopted-photo-preview img { max-width: 200px; border-radius: 6px; display: block; margin-bottom: 8px; }
        </style>

        <div class="fcc-cat-grid">
            <div class="fcc-cat-field">
                <label for="fcc_cat_age">Age</label>
                <input type="text" id="fcc_cat_age" name="fcc_cat_age" value="<?php echo esc_attr( $age ); ?>" placeholder="e.g. 4 mo, 2 yo" />
            </div>
            <div class="fcc-cat-field">
                <label for="fcc_sex">Sex</label>
                <select id="fcc_sex" name="fcc_sex">
                    <option value="">— Select —</option>
                    <option value="Male" <?php selected( $sex, 'Male' ); ?>>Male</option>
                    <option value="Female" <?php selected( $sex, 'Female' ); ?>>Female</option>
                </select>
            </div>
            <div class="fcc-cat-field">
                <label for="fcc_arrived">Arrived at Cafe</label>
                <input type="date" id="fcc_arrived" name="fcc_arrived" value="<?php echo esc_attr( $arrived ); ?>" />
            </div>
        </div>

        <div class="fcc-section">
            <div class="fcc-cat-field">
                <label>
                    <input type="checkbox" id="fcc_adopted" name="fcc_adopted" value="1" <?php checked( $adopted, '1' ); ?> />
                    <strong>This cat has been adopted</strong>
                </label>
            </div>

            <div class="fcc-adoption-fields fcc-adoption-section" <?php echo $adopted !== '1' ? 'style="display:none;"' : ''; ?>>
                <p class="fcc-section-title">🎉 Adoption Details</p>
                <div class="fcc-cat-grid">
                    <div class="fcc-cat-field">
                        <label for="fcc_adopted_date">Adoption Date</label>
                        <input type="date" id="fcc_adopted_date" name="fcc_adopted_date" value="<?php echo esc_attr( $adopted_date ); ?>" />
                    </div>
                    <div class="fcc-cat-field">
                        <label for="fcc_adopted_parents_name">Adopted Parent(s) Name</label>
                        <input type="text" id="fcc_adopted_parents_name" name="fcc_adopted_parents_name" value="<?php echo esc_attr( $parents_name ); ?>" placeholder="e.g. The Griffith Family" />
                    </div>
                </div>

                <div class="fcc-cat-field">
                    <label>Adoption Photo</label>
                    <div class="fcc-adopted-photo-preview" id="fcc_adopted_pic_preview">
                        <?php if ( $adopted_pic ) : ?>
                            <img src="<?php echo esc_url( $adopted_pic ); ?>" alt="Adoption photo" />
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="fcc_adopted_pic" id="fcc_adopted_pic" value="<?php echo esc_url( $adopted_pic ); ?>" />
                    <button type="button" class="button" id="fcc_adopted_pic_btn"><?php echo $adopted_pic ? 'Change Photo' : 'Upload Adoption Photo'; ?></button>
                    <script>
                    jQuery(function($) {
                        var frame2;
                        $('#fcc_adopted_pic_btn').on('click', function(e) {
                            e.preventDefault();
                            if (frame2) { frame2.open(); return; }
                            frame2 = wp.media({ title: 'Select Adoption Photo', button: { text: 'Use this photo' }, multiple: false });
                            frame2.on('select', function() {
                                var att = frame2.state().get('selection').first().toJSON();
                                $('#fcc_adopted_pic').val(att.url);
                                $('#fcc_adopted_pic_preview').html('<img src="' + att.url + '" />');
                                $('#fcc_adopted_pic_btn').text('Change Photo');
                            });
                            frame2.open();
                        });
                    });
                    </script>
                </div>

                <div class="fcc-cat-field">
                    <label for="fcc_success_story">Success Story</label>
                    <textarea id="fcc_success_story" name="fcc_success_story" rows="5" placeholder="Tell us their success story..."><?php echo esc_textarea( $success_story ); ?></textarea>
                </div>
            </div>
        </div>
        <?php
    }

    public function save( $post_id ) {
        // Photo meta box save
        if ( isset( $_POST['fcc_cat_photo_nonce'] ) && wp_verify_nonce( $_POST['fcc_cat_photo_nonce'], 'fcc_cat_photo_save' ) ) {
            if ( ! defined( 'DOING_AUTOSAVE' ) || ! DOING_AUTOSAVE ) {
                if ( current_user_can( 'edit_post', $post_id ) ) {
                    if ( isset( $_POST['fcc_cat_pic'] ) ) {
                        update_post_meta( $post_id, '_fcc_cat_pic', esc_url_raw( $_POST['fcc_cat_pic'] ) );
                    }
                }
            }
        }

        // Details meta box save
        if ( ! isset( $_POST['fcc_cat_nonce'] ) ) return;
        if ( ! wp_verify_nonce( $_POST['fcc_cat_nonce'], 'fcc_cat_save' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        $text_fields = [
            'fcc_cat_age'              => '_fcc_cat_age',
            'fcc_sex'                  => '_fcc_sex',
            'fcc_arrived'              => '_fcc_arrived',
            'fcc_adopted_date'         => '_fcc_adopted_date',
            'fcc_adopted_parents_name' => '_fcc_adopted_parents_name',
        ];

        foreach ( $text_fields as $key => $meta_key ) {
            if ( isset( $_POST[ $key ] ) ) {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $_POST[ $key ] ) );
            }
        }

        if ( isset( $_POST['fcc_success_story'] ) ) {
            update_post_meta( $post_id, '_fcc_success_story', sanitize_textarea_field( $_POST['fcc_success_story'] ) );
        }

        if ( isset( $_POST['fcc_adopted_pic'] ) ) {
            update_post_meta( $post_id, '_fcc_adopted_pic', esc_url_raw( $_POST['fcc_adopted_pic'] ) );
        }

        $adopted = isset( $_POST['fcc_adopted'] ) ? '1' : '0';
        update_post_meta( $post_id, '_fcc_adopted', $adopted );
    }
}
