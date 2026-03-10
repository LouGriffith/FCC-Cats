<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class FCC_Cats_Post_Type {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'init', [ $this, 'register' ] );
    }

    public function register() {
        $labels = [
            'name'               => 'Cats',
            'singular_name'      => 'Cat',
            'menu_name'          => 'Cats',
            'all_items'          => 'All Cats',
            'edit_item'          => 'Edit Cat',
            'view_item'          => 'View Cat',
            'add_new_item'       => 'Add New Cat',
            'add_new'            => 'Add New Cat',
            'new_item'           => 'New Cat',
            'search_items'       => 'Search Cats',
            'not_found'          => 'No cats found',
            'not_found_in_trash' => 'No cats found in Trash',
            'archives'           => 'Cat Archives',
        ];

        register_post_type( 'fcc_cat', [
            'labels'             => $labels,
            'public'             => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'menu_position'      => 26,
            'menu_icon'          => 'dashicons-pets',
            'supports'           => [ 'title' ],
            'has_archive'        => false,
            'publicly_queryable' => true,
            'rewrite'            => [ 'slug' => 'cat' ],
            'enter_title_here'   => "What's the cat's name?",
        ] );
    }
}
