<?php

namespace GSTM;

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Registers custom post type for the testimonial.
 * 
 * @since 1.0.0
 */
class Cpt {
    /**
     * Class constructor.
     * 
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'init', [$this, 'register'] );
        add_action( 'after_setup_theme', [$this, 'theme_support'] );
        add_action( 'init', array($this, 'register_taxonomies'), 0 );
    }

    /**
     * Registers a new post type
     * @uses $wp_post_types Inserts new post type object into the list
     *
     * @param string  Post type key, must not exceed 20 characters
     * @param array|string  See optional args description above.
     * @return object|WP_Error the registered post type object, or an error object
     */
    public function register() {
        $labels = [
            'name'                  => _x( 'Testimonials', 'gs-testimonial' ),
            'singular_name'         => _x( 'Testimonial', 'gs-testimonial' ),
            'menu_name'             => _x( 'Solid Testimonials', 'admin menu', 'gs-testimonial' ),
            'name_admin_bar'        => _x( 'Solid Testimonials', 'add new on admin bar', 'gs-testimonial' ),
            'add_new'               => _x( 'Add New', 'Testimonial', 'gs-testimonial' ),
            'add_new_item'          => __( 'Add New Testimonial', 'gs-testimonial' ),
            'new_item'              => __( 'New Testimonial', 'gs-testimonial' ),
            'edit_item'             => __( 'Edit Testimonial', 'gs-testimonial' ),
            'view_item'             => __( 'View Testimonial', 'gs-testimonial' ),
            'all_items'             => __( 'All Testimonials', 'gs-testimonial' ),
            'search_items'          => __( 'Search Testimonials', 'gs-testimonial' ),
            'parent_item_colon'     => __( 'Parent Testimonials:', 'gs-testimonial' ),
            'not_found'             => __( 'No Testimonials found.', 'gs-testimonial' ),
            'not_found_in_trash'    => __( 'No Testimonials found in Trash.', 'gs-testimonial' ),
            'featured_image'        => __( 'Reviewer Image', 'gs-testimonial' ),
            'set_featured_image'    => __( 'Set Reviewer Image', 'gs-testimonial' ),
            'remove_featured_image' => __( 'Remove Reviewer Image', 'gs-testimonial' ),
            'use_featured_image'    => __( 'Use as Reviewer Image', 'gs-testimonial' ),
        ];
        $args = [
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => GSTM_MENU_POSITION,
            'menu_icon'          => GSTM_PLUGIN_URI . '/assets/img/icon.svg',
            'supports'           => ['title', 'editor', 'thumbnail'],
        ];
        if ( plugin()->builder->get( 'enable_single_page' ) ) {
            $args['publicly_queryable'] = true;
            $args['rewrite'] = [
                'slug' => 'gs-testimonial',
            ];
        }
        register_post_type( 'gs_testimonial', $args );
    }

    /**
     * Add post type theme support.
     * 
     * @since 1.0.0
     */
    public function theme_support() {
        // TODO: check if the pro is not enabled
        add_theme_support( 'post-thumbnails', ['gs_testimonial'] );
        add_filter( 'widget_text', 'do_shortcode' );
    }

    // Register Custom Taxonomy For Testimonial Slider
    public function register_taxonomies() {
        $labels = array(
            'name'                       => _x( 'Testimonial Categories', 'Taxonomy General Name', 'gs-testimonial' ),
            'singular_name'              => _x( 'Testimonial Category', 'Taxonomy Singular Name', 'gs-testimonial' ),
            'menu_name'                  => __( 'Category', 'gs-testimonial' ),
            'all_items'                  => __( 'All Testimonial Category', 'gs-testimonial' ),
            'parent_item'                => __( 'Parent Testimonial Category', 'gs-testimonial' ),
            'parent_item_colon'          => __( 'Parent Testimonial Category:', 'gs-testimonial' ),
            'new_item_name'              => __( 'New Testimonial Category', 'gs-testimonial' ),
            'add_new_item'               => __( 'Add New Testimonial Category', 'gs-testimonial' ),
            'edit_item'                  => __( 'Edit Testimonial Category', 'gs-testimonial' ),
            'update_item'                => __( 'Update Testimonial Category', 'gs-testimonial' ),
            'separate_items_with_commas' => __( 'Separate Testimonial Category with commas', 'gs-testimonial' ),
            'search_items'               => __( 'Search Testimonial Category', 'gs-testimonial' ),
            'add_or_remove_items'        => __( 'Add or remove Testimonial Category', 'gs-testimonial' ),
            'choose_from_most_used'      => __( 'Choose from the most used Testimonial Categories', 'gs-testimonial' ),
            'not_found'                  => __( 'Not Found', 'gs-testimonial' ),
        );
        $rewrite = array(
            'slug'         => 'gs-testimonial-category',
            'with_front'   => true,
            'hierarchical' => false,
        );
        $args = array(
            'labels'            => $labels,
            'hierarchical'      => true,
            'public'            => true,
            'show_ui'           => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => false,
            'show_tagcloud'     => false,
            'rewrite'           => $rewrite,
        );
        register_taxonomy( 'gs_testimonial_category', array('gs_testimonial'), $args );
    }

}
