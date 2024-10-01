<?php

namespace GSTM;

if (!defined('ABSPATH')) exit;

class Hooks {

    public function __construct() {
        add_action( 'in_admin_header', [$this, 'disable_admin_notices'], PHP_INT_MAX );
        add_filter( 'admin_post_thumbnail_html', [ $this, 'img_size_note' ] );
        add_filter( 'plugin_action_links_' . plugin_basename(GSTM_PLUGIN_FILE), [ $this, 'pro_link' ] );
        add_action( 'init', [ $this, 'plugin_update_version' ], 0 );
        add_action( 'plugins_loaded', [ $this, 'plugin_loaded' ] );
        add_action( 'init', [ $this, 'gs_flush_rewrite_rules' ] );
        add_action( 'plugins_loaded', [ $this, 'i18n'] );
        add_filter( 'jetpack_content_options_featured_image_exclude_cpt', [$this, 'jetpack__featured_image_exclude_cpt']);

        register_activation_hook( GSTM_PLUGIN_FILE, [ $this, 'plugin_activate' ] );
    }

    function jetpack__featured_image_exclude_cpt( $excluded_post_types ) {
        return array_merge( $excluded_post_types, ['gs_testimonial'] );
    }

    function disable_admin_notices( ) {
        global $parent_file;
        if ( $parent_file != 'edit.php?post_type=gs_testimonial' ) return;
        remove_all_actions( 'network_admin_notices' );
        remove_all_actions( 'user_admin_notices' );
        remove_all_actions( 'admin_notices' );
        remove_all_actions( 'all_admin_notices' );
    }
    
    function img_size_note($content) {
        global $post_type, $post;
    
        if ($post_type == 'gs_testimonial') {
            if (!has_post_thumbnail($post->ID)) {
                $content .= '<p>' . __('Recommended image size 400px X 400px for perfect view on various devices.', 'gs-testimonial') . '</p>';
            }
        }

        return $content;
    }
    
    function pro_link($gstm_links) {
        $gstm_links[] = '<a href="https://www.gsplugins.com/wordpress-plugins" target="_blank">GS Plugins</a>';
        return $gstm_links;
    }
    
    public function plugin_update_version() {
    
        $old_version = get_option('gs_testimonial_plugin_version');
    
        if (GSTM_VERSION === $old_version) return;
    
        update_option('gs_testimonial_plugin_version', GSTM_VERSION);
    
        plugin()->builder->maybe_upgrade_data($old_version);

        gsTestimonialAssetGenerator()->assets_purge_all();
        delete_option('gs_testimonial_plugin_permalinks_flushed');
        
    }
    
    // Plugin On Activation
    function plugin_activate() {
        plugin()->cpt->register();
        flush_rewrite_rules();
        gsTestimonialAssetGenerator()->assets_purge_all();
    }
    
    // Plugin On Loaded
    function plugin_loaded() {
        Builder::maybe_create_shortcodes_table();
    }
    
    // Reset Permalinks
    function gs_flush_rewrite_rules() {
        if ( ! get_option('gs_testimonial_plugin_permalinks_flushed') ) {
            flush_rewrite_rules();
            update_option('gs_testimonial_plugin_permalinks_flushed', 1);
        }
    }
    
    // Load translations
    function i18n() {
        load_plugin_textdomain('gs-testimonial', false, dirname(plugin_basename(GSTM_PLUGIN_FILE)) . '/languages');
    }
}
