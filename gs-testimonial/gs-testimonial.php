<?php

/**
 *
 * @package   Solid_Testimonial
 * @author    GS Plugins <hello@gsplugins.com>
 * @license   GPL-2.0+
 * @link      https://www.gsplugins.com/
 * @copyright 2015 GS Plugins
 *
 * @wordpress-plugin
 * Plugin Name:			Solid Testimonials
 * Plugin URI:			https://www.gsplugins.com/wordpress-plugins/
 * Description:       	Discover the Ultimate Responsive Testimonials Slider for Showcasing Client Testimonials and Recommendations. Easily Display Anywhere on Your Site using the Simple Shortcode [gs_testimonial id=1]. Explore <a href="https://testimonial.gsplugins.com/">Solid Testimonials Demo</a> and Comprehensive <a href="https://docs.gsplugins.com/gs-testimonial-slider/">Documentation</a>.
 * Version:           	3.3.8
 * Author:       		GS Plugins
 * Author URI:       	https://www.gsplugins.com/
 * Text Domain:       	gs-testimonial
 * License:           	GPL-2.0+
 * License URI:       	http://www.gnu.org/licenses/gpl-2.0.txt
 * 
 */
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    exit;
}
/**
 * Defining constants
 */
if ( !defined( 'GSTM_VERSION' ) ) {
    define( 'GSTM_VERSION', '3.3.8' );
}
if ( !defined( 'GSTM_MENU_POSITION' ) ) {
    define( 'GSTM_MENU_POSITION', 39 );
}
if ( !defined( 'GSTM_PLUGIN_DIR' ) ) {
    define( 'GSTM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}
if ( !defined( 'GSTM_PLUGIN_URI' ) ) {
    define( 'GSTM_PLUGIN_URI', plugins_url( '', __FILE__ ) );
}
if ( !defined( 'GSTM_PLUGIN_FILE' ) ) {
    define( 'GSTM_PLUGIN_FILE', __FILE__ );
}
if ( function_exists( 'gstm_fs' ) ) {
    gstm_fs()->set_basename( false, __FILE__ );
} else {
    if ( !function_exists( 'gstm_fs' ) ) {
        // Create a helper function for easy SDK access.
        function gstm_fs() {
            global $gstm_fs;
            if ( !isset( $gstm_fs ) ) {
                // Activate multisite network integration.
                if ( !defined( 'WP_FS__PRODUCT_12861_MULTISITE' ) ) {
                    define( 'WP_FS__PRODUCT_12861_MULTISITE', true );
                }
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $gstm_fs = fs_dynamic_init( array(
                    'id'             => '12861',
                    'slug'           => 'gs-testimonial',
                    'premium_slug'   => 'gs-testimonial-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_4a6b7553615bb8b8b5c545954816f',
                    'is_premium'     => false,
                    'premium_suffix' => '- Pro',
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => array(
                        'days'               => 14,
                        'is_require_payment' => true,
                    ),
                    'menu'           => array(
                        'slug'       => 'edit.php?post_type=gs_testimonial',
                        'first-path' => 'edit.php?post_type=gs_testimonial&page=gs-testimonial-plugins-help',
                    ),
                    'is_live'        => true,
                ) );
            }
            return $gstm_fs;
        }

        // Init Freemius.
        gstm_fs();
        // Signal that SDK was initiated.
        do_action( 'gstm_fs_loaded' );
    }
    if ( !gstm_fs()->is_paying_or_trial() ) {
        function gs_tm_free_vs_pro_page() {
            add_submenu_page(
                'edit.php?post_type=gs_testimonial',
                'Free Pro Trial',
                'Free Pro Trial',
                'delete_posts',
                gstm_fs()->get_trial_url()
            );
        }

        add_action( 'admin_menu', 'gs_tm_free_vs_pro_page', 20 );
    }
    /**
     * Initiate Autoloader for Class Load
     *
     * @since 1.0.0
     */
    if ( !class_exists( '\\GSTM\\Autoloader' ) ) {
        require GSTM_PLUGIN_DIR . 'includes/autoloader.php';
        \GSTM\Autoloader::init();
    }
    require_once GSTM_PLUGIN_DIR . '/includes/plugin.php';
}