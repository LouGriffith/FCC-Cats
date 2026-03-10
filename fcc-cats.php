<?php
/**
 * Plugin Name: FCC Cats
 * Plugin URI:  https://lougriffith.com
 * Description: Manage the adoptable cats at Fancy Cat Cafe. Add and update each cat's profile — including photo, age, sex, arrival date, and best trait — and mark them as adopted with a success story from their new family. Display cats and adoption stories anywhere on your site using shortcodes.
 * Version:     1.3.0
 * Author:      Lou Griffith
 * Author URI:  https://lougriffith.com
 * Text Domain: fcc-cats
 * GitHub Plugin URI: https://github.com/LouGriffith/fcc-cats
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'FCC_CATS_VERSION', '1.3.0' );
define( 'FCC_CATS_DIR', plugin_dir_path( __FILE__ ) );
define( 'FCC_CATS_URL', plugin_dir_url( __FILE__ ) );

require_once FCC_CATS_DIR . 'includes/class-github-updater.php';
require_once FCC_CATS_DIR . 'includes/class-post-type.php';
require_once FCC_CATS_DIR . 'includes/class-taxonomy.php';
require_once FCC_CATS_DIR . 'includes/class-meta-boxes.php';
require_once FCC_CATS_DIR . 'includes/class-shortcodes.php';
require_once FCC_CATS_DIR . 'admin/class-admin-columns.php';

// GitHub auto-updater
new FCC_GitHub_Updater( __FILE__, 'LouGriffith', 'fcc-cats' );

FCC_Cats_Post_Type::get_instance();
FCC_Cats_Taxonomy::get_instance();
FCC_Cats_Meta_Boxes::get_instance();
FCC_Cats_Shortcodes::get_instance();
FCC_Cats_Admin_Columns::get_instance();
