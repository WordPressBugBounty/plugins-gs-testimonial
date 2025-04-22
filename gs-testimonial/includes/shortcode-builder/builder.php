<?php

namespace GSTM;

/**
 * Protect direct access
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
final class Builder {
    use Upgrade;
    public function __construct() {
        add_action( 'admin_menu', array($this, 'register_sub_menu') );
        add_action( 'admin_enqueue_scripts', array($this, 'admin_scripts') );
        add_action( 'wp_enqueue_scripts', array($this, 'preview_scripts') );
        add_action( 'wp_ajax_gstm_create_shortcode', array($this, 'create_shortcode') );
        add_action( 'wp_ajax_gstm_clone_shortcode', array($this, 'clone_shortcode') );
        add_action( 'wp_ajax_gstm_get_shortcode', array($this, 'get_shortcode') );
        add_action( 'wp_ajax_gstm_get_shortcodes', array($this, 'get_shortcodes') );
        add_action( 'wp_ajax_gstm_update_shortcode', array($this, 'update_shortcode') );
        add_action( 'wp_ajax_gstm_delete_shortcodes', array($this, 'delete_shortcodes') );
        add_action( 'wp_ajax_gstm_temp_save_shortcode_settings', array($this, 'temp_save_shortcode_settings') );
        add_action( 'wp_ajax_gstm_get_shortcode_pref', array($this, 'get_shortcode_pref') );
        add_action( 'wp_ajax_gstm_save_shortcode_pref', array($this, 'save_shortcode_pref') );
        add_action( 'template_include', array($this, 'populate_shortcode_preview') );
        add_action( 'show_admin_bar', array($this, 'hide_admin_bar_from_preview') );
        return $this;
    }

    public function register_sub_menu() {
        add_submenu_page(
            'edit.php?post_type=gs_testimonial',
            __( 'Testimonial Shortcodes', 'gs-testimonial' ),
            __( 'Shortcodes', 'gs-testimonial' ),
            'manage_options',
            'gst-shortcodes',
            [$this, 'view'],
            3
        );
        do_action( 'gs_tstm_after_shortcode_submenu' );
    }

    public function admin_scripts( $hook ) {
        if ( 'gs_testimonial_page_gst-shortcodes' != $hook ) {
            return;
        }
        wp_register_style(
            'gs-zmdi-fonts',
            GSTM_PLUGIN_URI . '/assets/libs/material-design-iconic-font/css/material-design-iconic-font.min.css',
            '',
            GSTM_VERSION,
            'all'
        );
        wp_enqueue_style(
            'gs-testimonial-shortcode-builder',
            GSTM_PLUGIN_URI . '/assets/admin/css/shortcode.min.css',
            array('gs-zmdi-fonts'),
            GSTM_VERSION,
            'all'
        );
        $data = array(
            'nonce'    => array(
                'create_shortcode'             => wp_create_nonce( '_gstm_create_shortcode_gs_' ),
                'clone_shortcode'              => wp_create_nonce( '_gstm_clone_shortcode_gs_' ),
                'update_shortcode'             => wp_create_nonce( '_gstm_update_shortcode_gs_' ),
                'delete_shortcodes'            => wp_create_nonce( '_gstm_delete_shortcodes_gs_' ),
                'temp_save_shortcode_settings' => wp_create_nonce( '_gstm_temp_save_shortcode_settings_gs_' ),
                'save_shortcode_pref'          => wp_create_nonce( '_gstm_save_shortcode_pref_gs_' ),
                'sync_data'                    => wp_create_nonce( '_gstm_sync_data_gs_' ),
                "import_gstm_demo"             => wp_create_nonce( "_gstm_simport_gstm_demo_gs_" ),
            ),
            'ajaxurl'  => admin_url( 'admin-ajax.php' ),
            'adminurl' => admin_url(),
            'siteurl'  => home_url(),
        );
        $data['shortcode_settings'] = $this->get_shortcode_default_settings();
        $data['shortcode_options'] = $this->get_shortcode_default_options();
        $data['preferences'] = $this->get_shortcode_default_prefs();
        $data['translations'] = $this->get_translation_srtings();
        $data['isProActivated'] = gstm_fs()->can_use_premium_code();
        $data['demo_data'] = [
            'items_data'     => wp_validate_boolean( get_option( 'gstm_dummy_items_data_created' ) ),
            'shortcode_data' => wp_validate_boolean( get_option( 'gstm_dummy_shortcode_data_created' ) ),
        ];
        wp_enqueue_script(
            'gs-testimonial-shortcode-builder',
            GSTM_PLUGIN_URI . '/assets/admin/js/shortcode.min.js',
            array('jquery'),
            GSTM_VERSION,
            true
        );
        wp_localize_script( 'gs-testimonial-shortcode-builder', 'GSTM_DATA', $data );
    }

    public function preview_scripts( $hook ) {
        if ( !is_preview() ) {
            return;
        }
        wp_enqueue_style(
            'gstm-shortcode-preview',
            GSTM_PLUGIN_URI . '/assets/css/preview.min.css',
            [],
            GSTM_VERSION
        );
    }

    public function create_shortcode() {
        if ( !check_admin_referer( '_gstm_create_shortcode_gs_' ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
        }
        $name = ( !empty( $_POST['shortcode_name'] ) ? sanitize_text_field( $_POST['shortcode_name'] ) : __( 'Untitled', 'gs-testimonial' ) );
        $settings = ( !empty( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : '' );
        if ( empty( $settings ) || !is_array( $settings ) ) {
            wp_send_json_error( __( 'Please configure the settings properly', 'gs-testimonial' ), 206 );
        }
        $settings = $this->validate_shortcode_settings( $settings );
        $wpdb = self::get_wpdb();
        $tableName = table_name();
        $data = array(
            'shortcode_name'     => $name,
            'shortcode_settings' => json_encode( $settings ),
            "created_at"         => current_time( 'mysql' ),
            "updated_at"         => current_time( 'mysql' ),
        );
        $wpdb->insert( $tableName, $data, $this->get_db_columns() );
        // check for database error
        if ( $this->gstm_check_db_error() ) {
            wp_send_json_error( sprintf( __( 'Database Error: %s' ), $wpdb->last_error ), 500 );
        }
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
        do_action( 'gstm_shortcode_created', $wpdb->insert_id );
        do_action( 'gsp_shortcode_created', $wpdb->insert_id );
        // send success response with inserted id
        wp_send_json_success( [
            'message'      => __( 'Shortcode created successfully', 'gs-testimonial' ),
            'shortcode_id' => $wpdb->insert_id,
        ] );
    }

    public function get_shortcode_default_prefs() {
        return [
            'enable_single_page' => false,
            'read_more_text'     => 'Read More',
            'gstm_custom_css'    => '',
        ];
    }

    /**
     * Returns the shortcode default settings.
     * 
     * @since  2.0.12
     * @return array The predefined default settings for each shortcode.
     */
    public function get_shortcode_default_settings() {
        $defaults = [
            'count'                        => -1,
            'theme'                        => 'grid_style1',
            'border_style'                 => 'none',
            'view_type'                    => 'grid',
            'image_style'                  => 'none',
            'desktop_columns'              => '3',
            'tablet_columns'               => '4',
            'columns_mobile'               => '6',
            'columns_small_mobile'         => '12',
            'speed'                        => 2000,
            'show_title'                   => true,
            'allow_html'                   => false,
            'show_popup_rating'            => true,
            'show_published_date'          => true,
            'show_designation'             => true,
            'ratings'                      => true,
            'image'                        => true,
            'isAutoplay'                   => true,
            'autoplay_delay'               => 2500,
            'pause_on_hover'               => true,
            'filter_all_text'              => 'All',
            'carousel_navs_enabled'        => true,
            'carousel_dots_enabled'        => true,
            'dynamic_dots_enabled'         => true,
            'carousel_navs_style'          => 'default',
            'carousel_dots_style'          => 'default',
            'carousel_dots_position'       => 'bottom',
            'gs_slider_nav_color'          => '',
            'gs_slider_nav_bg_color'       => '',
            'gs_slider_nav_hover_color'    => '',
            'gs_slider_nav_hover_bg_color' => '',
            'gs_slider_dot_color'          => '',
            'gs_slider_dot_hover_color'    => '',
            'filters_tab_style'            => 'style-one',
            'filter_color'                 => '',
            'filter_bg_color'              => '',
            'filter_border_color'          => '',
            'filter_color_active'          => '',
            'filter_bg_color_active'       => '',
            'filter_border_color_active'   => '',
            'testimonial_title_color'      => '',
            'testimonial_color'            => '',
            'read_more_color'              => '',
            'read_more_hover_color'        => '',
            'ratings_color'                => '',
            'name_color'                   => '',
            'item_bg_color'                => '',
            'designation_color'            => '',
            'company_color'                => '',
            'info_color'                   => '',
            'info_icon_color'              => '',
            'imageMode'                    => 'normal',
            'imageSize'                    => 'thumbnail',
            'orderby'                      => 'date',
            'order'                        => 'DESC',
            'filter_cat_pos'               => 'cat_center',
            'categories'                   => [],
            'gs_tm_details_contl'          => 100,
            'gs_tm_line_contl'             => 5,
            'reverse_direction'            => false,
            'company_logo'                 => true,
            'show_company'                 => false,
            'excludeCategories'            => [],
            'authors'                      => [],
            'excludeAuthors'               => [],
        ];
        return $defaults;
    }

    public function clone_shortcode() {
        if ( !check_admin_referer( '_gstm_clone_shortcode_gs_' ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
        }
        $clone_id = ( !empty( $_POST['clone_id'] ) ? absint( $_POST['clone_id'] ) : '' );
        if ( empty( $clone_id ) ) {
            wp_send_json_error( __( 'Clone Id not provided', 'gs-testimonial' ), 400 );
        }
        $clone_shortcode = $this->_get_shortcode( $clone_id, false );
        if ( empty( $clone_shortcode ) ) {
            wp_send_json_error( __( 'No shortcode found to clone.', 'gs-testimonial' ), 404 );
        }
        $shortcode_settings = $clone_shortcode['shortcode_settings'];
        $shortcode_name = $clone_shortcode['shortcode_name'] . ' ' . __( '- Cloned', 'gs-testimonial' );
        $shortcode_settings = $this->validate_shortcode_settings( $shortcode_settings );
        $wpdb = self::get_wpdb();
        $tableName = table_name();
        $data = array(
            "shortcode_name"     => $shortcode_name,
            "shortcode_settings" => json_encode( $shortcode_settings ),
            "created_at"         => current_time( 'mysql' ),
            "updated_at"         => current_time( 'mysql' ),
        );
        $wpdb->insert( $tableName, $data, $this->get_db_columns() );
        if ( $this->gstm_check_db_error() ) {
            wp_send_json_error( sprintf( __( 'Database Error: %s' ), $wpdb->last_error ), 500 );
        }
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
        // Get the cloned shortcode
        $shotcode = $this->_get_shortcode( $wpdb->insert_id, false );
        // send success response with inserted id
        wp_send_json_success( array(
            'message'   => __( 'Shortcode cloned successfully', 'gs-testimonial' ),
            'shortcode' => $shotcode,
        ) );
    }

    public function get_shortcode() {
        $shortcode_id = ( !empty( $_GET['id'] ) ? absint( $_GET['id'] ) : null );
        return $this->_get_shortcode( $shortcode_id, wp_doing_ajax() );
    }

    public function get_shortcodes() {
        return $this->fetch_shortcodes( null, wp_doing_ajax() );
    }

    public function update_shortcode( $shortcode_id = null, $nonce = null ) {
        if ( !check_admin_referer( '_gstm_update_shortcode_gs_' ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
        }
        $shortcode_id = ( !empty( $_POST['id'] ) ? absint( $_POST['id'] ) : null );
        if ( empty( $shortcode_id ) ) {
            wp_send_json_error( __( 'Shortcode ID missing', 'gs-testimonial' ), 400 );
        }
        $shortcode = $this->_get_shortcode( $shortcode_id, false );
        if ( empty( $shortcode ) ) {
            wp_send_json_error( __( 'No shortcode found to update', 'gs-testimonial' ), 404 );
        }
        $name = ( !empty( $_POST['shortcode_name'] ) ? sanitize_text_field( $_POST['shortcode_name'] ) : sanitize_text_field( $shortcode['shortcode_name'] ) );
        $settings = ( !empty( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : $shortcode['shortcode_settings'] );
        $settings = $this->validate_shortcode_settings( $settings );
        $tableName = table_name();
        $wpdb = self::get_wpdb();
        $data = array(
            'shortcode_name'     => $name,
            'shortcode_settings' => json_encode( $settings ),
            "updated_at"         => current_time( 'mysql' ),
        );
        $updateId = $wpdb->update(
            $tableName,
            $data,
            array(
                'id' => $shortcode_id,
            ),
            $this->get_db_columns()
        );
        if ( $this->gstm_check_db_error() ) {
            wp_send_json_error( sprintf( __( 'Database Error: %1$s', 'gs-testimonial' ), $wpdb->last_error ), 500 );
        }
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
        wp_cache_delete( 'gstm_shortcode' . $shortcode_id, 'gs_testimonials' );
        do_action( 'gstm_shortcode_updated', $updateId );
        do_action( 'gsp_shortcode_updated', $updateId );
        wp_send_json_success( array(
            'message'      => __( 'Shortcode updated', 'gs-testimonial' ),
            'shortcode_id' => $updateId,
        ) );
    }

    public function delete_shortcodes() {
        if ( !check_admin_referer( '_gstm_delete_shortcodes_gs_' ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
        }
        $ids = ( isset( $_POST['ids'] ) ? $_POST['ids'] : null );
        if ( empty( $ids ) ) {
            wp_send_json_error( __( 'No shortcode ids provided', 'gs-testimonial' ), 400 );
        }
        $wpdb = self::get_wpdb();
        $count = count( $ids );
        $ids = implode( ',', array_map( 'absint', $ids ) );
        $tableName = table_name();
        $wpdb->query( "DELETE FROM {$tableName} WHERE ID IN({$ids})" );
        if ( $this->gstm_check_db_error() ) {
            wp_send_json_error( sprintf( __( 'Database Error: %s' ), $wpdb->last_error ), 500 );
        }
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
        do_action( 'gstm_shortcode_deleted' );
        do_action( 'gsp_shortcode_deleted' );
        $m = _n(
            "Shortcode has been deleted",
            "Shortcodes have been deleted",
            $count,
            'gs-testimonial'
        );
        wp_send_json_success( [
            'message' => $m,
        ] );
    }

    public function temp_save_shortcode_settings() {
        if ( !check_admin_referer( '_gstm_temp_save_shortcode_settings_gs_' ) || !current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
        }
        $temp_key = ( isset( $_POST['temp_key'] ) ? $_POST['temp_key'] : null );
        $shortcode_settings = ( isset( $_POST['shortcode_settings'] ) ? $_POST['shortcode_settings'] : [] );
        if ( empty( $temp_key ) ) {
            wp_send_json_error( __( 'No temp key provided', 'gs-testimonial' ), 400 );
        }
        if ( empty( $shortcode_settings ) ) {
            wp_send_json_error( __( 'No temp settings provided', 'gs-testimonial' ), 400 );
        }
        set_transient( $temp_key, $shortcode_settings, DAY_IN_SECONDS );
        // save the transient for 1 day
        wp_send_json_success( __( 'Temp data saved', 'gs-testimonial' ) );
    }

    public function get_shortcode_pref() {
        return $this->_get_shortcode_pref( wp_doing_ajax() );
    }

    public function save_shortcode_pref( $nonce = null ) {
        if ( !$nonce ) {
            $nonce = wp_create_nonce( '_gstm_save_shortcode_pref_gs_' );
        }
        if ( empty( $_POST['prefs'] ) ) {
            wp_send_json_error( __( 'No preferences provided', 'gs-testimonial' ), 400 );
        }
        $this->_save_shortcode_pref( $nonce, $_POST['prefs'], true );
    }

    public function populate_shortcode_preview( $template ) {
        global $wp, $wp_query;
        if ( $this->is_gstm_shortcode_preview() ) {
            // Create our fake post
            $post_id = rand( 1, 99999 ) - 9999999;
            $post = new \stdClass();
            $post->ID = $post_id;
            $post->post_author = 1;
            $post->post_date = current_time( 'mysql' );
            $post->post_date_gmt = current_time( 'mysql', 1 );
            $post->post_title = __( 'Shortcode Preview', 'gs-testimonial' );
            $post->post_content = '[gs_testimonial preview="yes" id="' . esc_attr( sanitize_key( $_REQUEST['gstm_shortcode_preview'] ) ) . '"]';
            $post->post_status = 'publish';
            $post->comment_status = 'closed';
            $post->ping_status = 'closed';
            $post->post_name = 'fake-page-' . rand( 1, 99999 );
            // append random number to avoid clash
            $post->post_type = 'page';
            $post->filter = 'raw';
            // important!
            // Convert to WP_Post object
            $wp_post = new \WP_Post($post);
            // Add the fake post to the cache
            wp_cache_add( $post_id, $wp_post, 'posts' );
            // Update the main query
            $wp_query->post = $wp_post;
            $wp_query->posts = array($wp_post);
            $wp_query->queried_object = $wp_post;
            $wp_query->queried_object_id = $post_id;
            $wp_query->found_posts = 1;
            $wp_query->post_count = 1;
            $wp_query->max_num_pages = 1;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_single = false;
            $wp_query->is_attachment = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            $wp_query->is_tag = false;
            $wp_query->is_tax = false;
            $wp_query->is_author = false;
            $wp_query->is_date = false;
            $wp_query->is_year = false;
            $wp_query->is_month = false;
            $wp_query->is_day = false;
            $wp_query->is_time = false;
            $wp_query->is_search = false;
            $wp_query->is_feed = false;
            $wp_query->is_comment_feed = false;
            $wp_query->is_trackback = false;
            $wp_query->is_home = false;
            $wp_query->is_embed = false;
            $wp_query->is_404 = false;
            $wp_query->is_paged = false;
            $wp_query->is_admin = false;
            $wp_query->is_preview = false;
            $wp_query->is_robots = false;
            $wp_query->is_posts_page = false;
            $wp_query->is_post_type_archive = false;
            // Update globals
            $GLOBALS['wp_query'] = $wp_query;
            $wp->register_globals();
            include GSTM_PLUGIN_DIR . 'includes/shortcode-builder/preview.php';
            return;
        }
        return $template;
    }

    public function hide_admin_bar_from_preview( $visibility ) {
        if ( $this->is_gstm_shortcode_preview() ) {
            return false;
        }
        return $visibility;
    }

    public function is_gstm_shortcode_preview() {
        return isset( $_REQUEST['gstm_shortcode_preview'] ) && !empty( $_REQUEST['gstm_shortcode_preview'] );
    }

    public function validate_preference( $settings ) {
        $defaults = $this->get_shortcode_default_prefs();
        $settings = shortcode_atts( $defaults, $settings );
        $settings['enable_single_page'] = wp_validate_boolean( $settings['enable_single_page'] );
        $settings['read_more_text'] = sanitize_text_field( $settings['read_more_text'] );
        $settings['gstm_custom_css'] = wp_strip_all_tags( $settings['gstm_custom_css'] );
        return $settings;
    }

    public function _save_shortcode_pref( $nonce, $settings, $is_ajax ) {
        if ( !wp_verify_nonce( $nonce, '_gstm_save_shortcode_pref_gs_' ) ) {
            if ( $is_ajax ) {
                wp_send_json_error( __( 'Unauthorised Request', 'gs-testimonial' ), 401 );
            }
            return false;
        }
        $settings = $this->validate_preference( $settings );
        update_option( 'gstm_shortcode_prefs', $settings );
        do_action( 'gstm_preference_update' );
        do_action( 'gsp_preference_update' );
        delete_option( 'gs_testimonial_plugin_permalinks_flushed' );
        if ( $is_ajax ) {
            wp_send_json_success( __( 'Preference saved', 'gs-testimonial' ) );
        }
    }

    /**
     * Returns option based on the option key.
     * 
     * @since  2.0.12
     * 
     * @param string $option  The option key.
     * @param string $default The default value incase doesn't get the actual value.
     * 
     * @return mixed option value.
     */
    public function get( $option, $default = '' ) {
        $options = $this->_get_shortcode_pref( false );
        if ( isset( $options[$option] ) ) {
            return $options[$option];
        }
        return $default;
    }

    public function _get_shortcode_pref( $is_ajax ) {
        $pref = get_option( 'gstm_shortcode_prefs', [] );
        $pref = $this->validate_preference( $pref );
        if ( $is_ajax ) {
            wp_send_json_success( $pref );
        }
        return $pref;
    }

    public function view() {
        include GSTM_PLUGIN_DIR . 'includes/shortcode-builder/page.php';
    }

    /**
     * Returns default options.
     * 
     * @since  2.0.12
     * @return array Default options.
     */
    public function get_shortcode_default_options() {
        $options = [
            'view_types'             => $this->get_view_types(),
            'themes'                 => $this->_themes(),
            'image_styles'           => $this->get_image_styles(),
            'orderby'                => $this->get_order_by(),
            'order'                  => $this->_get_order(),
            'imageModes'             => $this->get_image_modes(),
            'imageSizes'             => $this->get_possible_thumbnail_sizes(),
            'columns'                => $this->get_columns_all(),
            'filter_cat_pos'         => $this->get_cat_pos(),
            'carousel_navs_style'    => $this->get_carousel_nav_styles(),
            'carousel_dots_style'    => $this->get_carousel_dot_styles(),
            'carousel_dots_position' => $this->get_carousel_dot_positions(),
            'filters_tab_style'      => $this->get_filters_tab_styles(),
            'categories'             => $this->get_testimonial_terms(),
            'authors'                => [],
        ];
        return $options;
    }

    public function get_authors_premium_only() {
        global $wpdb;
        $meta_values = $wpdb->get_results( "SELECT `meta_value` FROM {$wpdb->postmeta} WHERE `meta_key` = 'gs_t_client_name'", ARRAY_A );
        if ( empty( $meta_values ) ) {
            return [];
        }
        $client_names = wp_list_pluck( $meta_values, 'meta_value' );
        $client_names = array_unique( $client_names );
        $client_names = array_values( $client_names );
        return array_map( function ( $client_name ) {
            return [
                'label' => $client_name,
                'value' => $client_name,
            ];
        }, $client_names );
    }

    /**
     * Retrives WP registered possible thumbnail sizes.
     * 
     * @since  1.10.14
     * @return array   image sizes.
     */
    public function get_possible_thumbnail_sizes() {
        $sizes = get_intermediate_image_sizes();
        if ( empty( $sizes ) ) {
            return [];
        }
        $result = [];
        foreach ( $sizes as $size ) {
            $result[] = [
                'label' => ucwords( preg_replace( '/_|-/', ' ', $size ) ),
                'value' => $size,
            ];
        }
        return $result;
    }

    /**
     * Returns predefined columns
     * 
     * @since  2.0.12
     * @return array Predefined columns.
     */
    public function get_columns_all() {
        return array(
            array(
                'label' => __( '1 Column', 'gs-testimonial' ),
                'value' => '12',
            ),
            array(
                'label' => __( '2 Columns', 'gs-testimonial' ),
                'value' => '6',
            ),
            array(
                'label' => __( '3 Columns', 'gs-testimonial' ),
                'value' => '4',
            ),
            array(
                'label' => __( '4 Columns', 'gs-testimonial' ),
                'value' => '3',
            )
        );
    }

    /**
     * Returns theme supported transitions.
     * 
     * @since  2.0.12
     * @return array Predefined transitions.
     */
    public function get_view_types() {
        $freeViewTypes = [[
            'label' => __( 'Grid', 'gs-testimonial' ),
            'value' => 'grid',
        ], [
            'label' => __( 'Carousel', 'gs-testimonial' ),
            'value' => 'carousel',
        ]];
        $proViewTypes = [[
            'label' => __( 'Masonry', 'gs-testimonial' ),
            'value' => 'masonry',
        ], [
            'label' => __( 'Filter', 'gs-testimonial' ),
            'value' => 'filter',
        ]];
        $proViewTypes = array_map( function ( $item ) {
            $item['pro'] = true;
            return $item;
        }, $proViewTypes );
        return array_merge( $freeViewTypes, $proViewTypes );
    }

    /**
     * Returns themes options.
     * 
     * @since  2.0.12
     * @return array Themes options.
     */
    public function _themes() {
        $freeThemes = array([
            'label' => __( 'Style 1', 'gs-testimonial' ),
            'value' => 'grid_style1',
        ], [
            'label' => __( 'Style 2', 'gs-testimonial' ),
            'value' => 'grid_style2',
        ], [
            'label' => __( 'Style 3', 'gs-testimonial' ),
            'value' => 'grid_style3',
        ]);
        $proThemes = array(
            [
                'label' => __( 'Style 4', 'gs-testimonial' ),
                'value' => 'grid_style4',
            ],
            [
                'label' => __( 'Style 5', 'gs-testimonial' ),
                'value' => 'grid_style5',
            ],
            [
                'label' => __( 'Style 6', 'gs-testimonial' ),
                'value' => 'grid_style6',
            ],
            [
                'label' => __( 'Style 7', 'gs-testimonial' ),
                'value' => 'grid_style7',
            ],
            [
                'label' => __( 'Style 8', 'gs-testimonial' ),
                'value' => 'grid_style8',
            ],
            [
                'label' => __( 'Style 9', 'gs-testimonial' ),
                'value' => 'grid_style9',
            ],
            [
                'label' => __( 'Style 10', 'gs-testimonial' ),
                'value' => 'grid_style10',
            ],
            [
                'label' => __( 'Style 11', 'gs-testimonial' ),
                'value' => 'grid_style11',
            ],
            [
                'label' => __( 'Style 12', 'gs-testimonial' ),
                'value' => 'grid_style12',
            ],
            [
                'label' => __( 'Style 13', 'gs-testimonial' ),
                'value' => 'grid_style13',
            ],
            [
                'label' => __( 'Style 14', 'gs-testimonial' ),
                'value' => 'grid_style14',
            ],
            [
                'label' => __( 'Style 15', 'gs-testimonial' ),
                'value' => 'grid_style15',
            ],
            [
                'label' => __( 'Style 16', 'gs-testimonial' ),
                'value' => 'grid_style16',
            ],
            [
                'label' => __( 'Style 17', 'gs-testimonial' ),
                'value' => 'grid_style17',
            ],
            [
                'label' => __( 'Style 18', 'gs-testimonial' ),
                'value' => 'grid_style18',
            ],
            [
                'label' => __( 'Carousel Style 1', 'gs-testimonial' ),
                'value' => 'carousel_style_1',
            ]
        );
        if ( !gstm_fs()->can_use_premium_code() ) {
            $proThemes = array_map( function ( $item ) {
                $item['label'] = $item['label'] . ' (Pro)';
                $item['pro'] = true;
                return $item;
            }, $proThemes );
        }
        return array_merge( $freeThemes, $proThemes );
    }

    /**
     * Returns image styles options.
     * 
     * @since  2.0.12
     * @return array Returns predefined image styles.
     */
    public function get_image_styles() {
        $free = [[
            'label' => __( 'Default', 'gs-testimonial' ),
            'value' => 'none',
        ]];
        $pro = [
            [
                'label' => __( 'Square', 'gs-testimonial' ),
                'value' => 'gs_square',
            ],
            [
                'label' => __( 'Circle', 'gs-testimonial' ),
                'value' => 'gs_circle',
            ],
            [
                'label' => __( 'Radius', 'gs-testimonial' ),
                'value' => 'gs_radius',
            ],
            [
                'label' => __( 'Square Shadow', 'gs-testimonial' ),
                'value' => 'gs_square_shadow',
            ],
            [
                'label' => __( 'Circle Shadow', 'gs-testimonial' ),
                'value' => 'gs_circle_shadow',
            ],
            [
                'label' => __( 'Radius Shadow', 'gs-testimonial' ),
                'value' => 'gs_radius_shadow',
            ]
        ];
        if ( !gstm_fs()->can_use_premium_code() ) {
            $pro = array_map( function ( $item ) {
                $item['label'] = $item['label'] . ' (Pro)';
                $item['disabled'] = true;
                return $item;
            }, $pro );
        }
        return array_merge( $free, $pro );
    }

    /**
     * Returns orderby options.
     * 
     * @since  2.0.12
     * @return array Returns orderby options.
     */
    public function get_order_by() {
        $free = [
            [
                'label' => __( 'ID', 'gs-testimonial' ),
                'value' => 'ID',
            ],
            [
                'label' => __( 'Title', 'gs-testimonial' ),
                'value' => 'title',
            ],
            [
                'label' => __( 'Random', 'gs-testimonial' ),
                'value' => 'rand',
            ],
            [
                'label' => __( 'Date', 'gs-testimonial' ),
                'value' => 'date',
            ]
        ];
        $pro = [[
            'label' => __( 'Custom Order', 'gs-testimonial' ),
            'value' => 'menu_order',
        ]];
        if ( !gstm_fs()->can_use_premium_code() ) {
            $pro = array_map( function ( $item ) {
                $item['label'] = $item['label'] . ' (Pro)';
                $item['disabled'] = true;
                return $item;
            }, $pro );
        }
        return array_merge( $free, $pro );
    }

    /**
     * Returns order options.
     * 
     * @since  2.0.12
     * @return array Returns predefined order options.
     */
    public function _get_order() {
        return [[
            'label' => __( 'DESC', 'gs-testimonial' ),
            'value' => 'DESC',
        ], [
            'label' => __( 'ASC', 'gs-testimonial' ),
            'value' => 'ASC',
        ]];
    }

    /**
     * Returns image mode options.
     * 
     * @since  2.0.12
     * @return array Returns predefined image mode options.
     */
    public function get_image_modes() {
        $free = [[
            'label' => __( 'Normal', 'gs-testimonial' ),
            'value' => 'normal',
        ]];
        $pro = [[
            'label' => __( 'Gray to Normal', 'gs-testimonial' ),
            'value' => 'gray-to-normal',
        ], [
            'label' => __( 'Grey on Hover', 'gs-testimonial' ),
            'value' => 'gray-on-hover',
        ], [
            'label' => __( 'Always Gray', 'gs-testimonial' ),
            'value' => 'always-gray',
        ]];
        if ( !gstm_fs()->can_use_premium_code() ) {
            $pro = array_map( function ( $item ) {
                $item['label'] = $item['label'] . ' (Pro)';
                $item['disabled'] = true;
                return $item;
            }, $pro );
        }
        return array_merge( $free, $pro );
    }

    public function get_cat_pos() {
        return [[
            'label' => __( 'Left', 'gs-testimonial' ),
            'value' => 'cat_left',
        ], [
            'label' => __( 'Center', 'gs-testimonial' ),
            'value' => 'cat_center',
        ], [
            'label' => __( 'Right', 'gs-testimonial' ),
            'value' => 'cat_right',
        ]];
    }

    public function get_carousel_nav_styles() {
        $styles = [
            [
                'label' => __( 'Default', 'gs-testimonial' ),
                'value' => 'default',
            ],
            [
                'label' => __( 'Style One', 'gs-testimonial' ),
                'value' => 'style-one',
            ],
            [
                'label' => __( 'Style Two', 'gs-testimonial' ),
                'value' => 'style-two',
            ],
            [
                'label' => __( 'Style Three', 'gs-testimonial' ),
                'value' => 'style-three',
            ]
        ];
        return $styles;
    }

    public function get_carousel_dot_styles() {
        $styles = [
            [
                'label' => __( 'Default', 'gs-testimonial' ),
                'value' => 'default',
            ],
            [
                'label' => __( 'Style One', 'gs-testimonial' ),
                'value' => 'style-one',
            ],
            [
                'label' => __( 'Style Two', 'gs-testimonial' ),
                'value' => 'style-two',
            ],
            [
                'label' => __( 'Style Three', 'gs-testimonial' ),
                'value' => 'style-three',
            ]
        ];
        return $styles;
    }

    public function get_carousel_dot_positions() {
        $styles = [[
            'label' => __( 'Bottom', 'gs-testimonial' ),
            'value' => 'bottom',
        ], [
            'label' => __( 'Bottom Inside Left', 'gs-testimonial' ),
            'value' => 'bottom-inside-left',
        ], [
            'label' => __( 'Bottom Inside Right', 'gs-testimonial' ),
            'value' => 'bottom-inside-right',
        ]];
        return $styles;
    }

    public function get_filters_tab_styles() {
        $styles = [
            [
                'label' => __( 'Style One', 'gs-testimonial' ),
                'value' => 'style-one',
            ],
            [
                'label' => __( 'Style Two', 'gs-testimonial' ),
                'value' => 'style-two',
            ],
            [
                'label' => __( 'Style Three', 'gs-testimonial' ),
                'value' => 'style-three',
            ],
            [
                'label' => __( 'Style Four', 'gs-testimonial' ),
                'value' => 'style-four',
            ],
            [
                'label' => __( 'Style Five', 'gs-testimonial' ),
                'value' => 'style-five',
            ]
        ];
        return $styles;
    }

    /**
     * Returns testimonial categories.
     *
     * @since  2.0.12
     * @param  bool $idsOnly For extracting category ids only.
     * @return array        Testimonial categories.
     */
    public function get_testimonial_terms( $idsOnly = false ) {
        $terms = get_terms( 'gs_testimonial_category', array(
            'hide_empty' => false,
        ) );
        if ( empty( $terms ) ) {
            return array();
        }
        if ( $idsOnly ) {
            return wp_list_pluck( $terms, 'term_id' );
        }
        $result = array();
        foreach ( $terms as $term ) {
            $result[] = array(
                'label' => $term->name,
                'value' => $term->term_id,
            );
        }
        return $result;
    }

    public static function get_wpdb() {
        global $wpdb;
        if ( wp_doing_ajax() ) {
            $wpdb->show_errors = false;
        }
        return $wpdb;
    }

    /**
     * Checks for database errors.
     * 
     * @since  1.10.14
     * @return bool true/false based on the error status.
     */
    public function gstm_check_db_error() {
        $wpdb = self::get_wpdb();
        if ( '' === $wpdb->last_error ) {
            return false;
        }
        return true;
    }

    public function validate_shortcode_settings( $shortcode_settings ) {
        $shortcode_settings = shortcode_atts( $this->get_shortcode_default_settings(), $shortcode_settings );
        $shortcode_settings['count'] = intval( $shortcode_settings['count'] );
        $shortcode_settings['theme'] = sanitize_text_field( $shortcode_settings['theme'] );
        $shortcode_settings['image_style'] = sanitize_text_field( $shortcode_settings['image_style'] );
        $shortcode_settings['desktop_columns'] = sanitize_text_field( $shortcode_settings['desktop_columns'] );
        $shortcode_settings['tablet_columns'] = sanitize_text_field( $shortcode_settings['tablet_columns'] );
        $shortcode_settings['columns_mobile'] = sanitize_text_field( $shortcode_settings['columns_mobile'] );
        $shortcode_settings['columns_small_mobile'] = sanitize_text_field( $shortcode_settings['columns_small_mobile'] );
        $shortcode_settings['speed'] = intval( $shortcode_settings['speed'] );
        $shortcode_settings['show_title'] = wp_validate_boolean( $shortcode_settings['show_title'] );
        $shortcode_settings['allow_html'] = wp_validate_boolean( $shortcode_settings['allow_html'] );
        $shortcode_settings['show_popup_rating'] = wp_validate_boolean( $shortcode_settings['show_popup_rating'] );
        $shortcode_settings['show_published_date'] = wp_validate_boolean( $shortcode_settings['show_published_date'] );
        $shortcode_settings['show_designation'] = wp_validate_boolean( $shortcode_settings['show_designation'] );
        $shortcode_settings['ratings'] = wp_validate_boolean( $shortcode_settings['ratings'] );
        $shortcode_settings['image'] = wp_validate_boolean( $shortcode_settings['image'] );
        $shortcode_settings['isAutoplay'] = wp_validate_boolean( $shortcode_settings['isAutoplay'] );
        $shortcode_settings['autoplay_delay'] = intval( $shortcode_settings['autoplay_delay'] );
        $shortcode_settings['filter_all_text'] = sanitize_text_field( $shortcode_settings['filter_all_text'] );
        $shortcode_settings['carousel_navs_enabled'] = wp_validate_boolean( $shortcode_settings['carousel_navs_enabled'] );
        $shortcode_settings['carousel_dots_enabled'] = wp_validate_boolean( $shortcode_settings['carousel_dots_enabled'] );
        $shortcode_settings['dynamic_dots_enabled'] = wp_validate_boolean( $shortcode_settings['dynamic_dots_enabled'] );
        $shortcode_settings['carousel_navs_style'] = sanitize_text_field( $shortcode_settings['carousel_navs_style'] );
        $shortcode_settings['carousel_dots_style'] = sanitize_text_field( $shortcode_settings['carousel_dots_style'] );
        $shortcode_settings['carousel_dots_position'] = sanitize_text_field( $shortcode_settings['carousel_dots_position'] );
        $shortcode_settings['gs_slider_nav_color'] = sanitize_text_field( $shortcode_settings['gs_slider_nav_color'] );
        $shortcode_settings['gs_slider_nav_bg_color'] = sanitize_text_field( $shortcode_settings['gs_slider_nav_bg_color'] );
        $shortcode_settings['gs_slider_nav_hover_color'] = sanitize_text_field( $shortcode_settings['gs_slider_nav_hover_color'] );
        $shortcode_settings['gs_slider_nav_hover_bg_color'] = sanitize_text_field( $shortcode_settings['gs_slider_nav_hover_bg_color'] );
        $shortcode_settings['gs_slider_dot_color'] = sanitize_text_field( $shortcode_settings['gs_slider_dot_color'] );
        $shortcode_settings['gs_slider_dot_hover_color'] = sanitize_text_field( $shortcode_settings['gs_slider_dot_hover_color'] );
        $shortcode_settings['filters_tab_style'] = sanitize_text_field( $shortcode_settings['filters_tab_style'] );
        $shortcode_settings['filter_color'] = sanitize_text_field( $shortcode_settings['filter_color'] );
        $shortcode_settings['filter_bg_color'] = sanitize_text_field( $shortcode_settings['filter_bg_color'] );
        $shortcode_settings['filter_border_color'] = sanitize_text_field( $shortcode_settings['filter_border_color'] );
        $shortcode_settings['filter_color_active'] = sanitize_text_field( $shortcode_settings['filter_color_active'] );
        $shortcode_settings['filter_bg_color_active'] = sanitize_text_field( $shortcode_settings['filter_bg_color_active'] );
        $shortcode_settings['filter_border_color_active'] = sanitize_text_field( $shortcode_settings['filter_border_color_active'] );
        $shortcode_settings['testimonial_title_color'] = sanitize_text_field( $shortcode_settings['testimonial_title_color'] );
        $shortcode_settings['testimonial_color'] = sanitize_text_field( $shortcode_settings['testimonial_color'] );
        $shortcode_settings['read_more_hover_color'] = sanitize_text_field( $shortcode_settings['read_more_hover_color'] );
        $shortcode_settings['ratings_color'] = sanitize_text_field( $shortcode_settings['ratings_color'] );
        $shortcode_settings['name_color'] = sanitize_text_field( $shortcode_settings['name_color'] );
        $shortcode_settings['item_bg_color'] = sanitize_text_field( $shortcode_settings['item_bg_color'] );
        $shortcode_settings['designation_color'] = sanitize_text_field( $shortcode_settings['designation_color'] );
        $shortcode_settings['company_color'] = sanitize_text_field( $shortcode_settings['company_color'] );
        $shortcode_settings['info_color'] = sanitize_text_field( $shortcode_settings['info_color'] );
        $shortcode_settings['info_icon_color'] = sanitize_text_field( $shortcode_settings['info_icon_color'] );
        $shortcode_settings['read_more_color'] = sanitize_text_field( $shortcode_settings['read_more_color'] );
        $shortcode_settings['imageMode'] = sanitize_text_field( $shortcode_settings['imageMode'] );
        $shortcode_settings['imageSize'] = sanitize_text_field( $shortcode_settings['imageSize'] );
        $shortcode_settings['orderby'] = sanitize_text_field( $shortcode_settings['orderby'] );
        $shortcode_settings['order'] = sanitize_text_field( $shortcode_settings['order'] );
        $shortcode_settings['filter_cat_pos'] = sanitize_text_field( $shortcode_settings['filter_cat_pos'] );
        if ( gstm_fs()->is_paying_or_trial() ) {
            $shortcode_settings['company_logo'] = wp_validate_boolean( $shortcode_settings['company_logo'] );
            $shortcode_settings['reverse_direction'] = wp_validate_boolean( $shortcode_settings['reverse_direction'] );
            $shortcode_settings['show_company'] = wp_validate_boolean( $shortcode_settings['show_company'] );
            $shortcode_settings['gs_tm_details_contl'] = intval( $shortcode_settings['gs_tm_details_contl'] );
            $shortcode_settings['gs_tm_line_contl'] = intval( $shortcode_settings['gs_tm_line_contl'] );
            $validated_data['excludeCategories'] = array_map( 'intval', $shortcode_settings['excludeCategories'] );
            $validated_data['authors'] = array_map( 'sanitize_text_field', $shortcode_settings['authors'] );
            $validated_data['excludeAuthors'] = array_map( 'sanitize_text_field', $shortcode_settings['excludeAuthors'] );
        }
        return (array) $shortcode_settings;
    }

    static function maybe_create_shortcodes_table() {
        global $wpdb;
        $gstm_db_version = '1.0';
        if ( get_option( "{$wpdb->prefix}gstm_db_version" ) == $gstm_db_version ) {
            return;
        }
        $charset = get_charset();
        $sql = "CREATE TABLE {$wpdb->prefix}gstm_shortcodes (\n        id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,\n            shortcode_name TEXT NOT NULL,\n            shortcode_settings LONGTEXT NOT NULL,\n            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,\n            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,\n            PRIMARY KEY (id)\n        )" . $charset . ";";
        if ( get_option( "{$wpdb->prefix}gstm_db_version" ) < $gstm_db_version ) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
        update_option( "{$wpdb->prefix}gstm_db_version", $gstm_db_version );
    }

    public function create_dummy_shortcodes() {
        $Dummy_Data = new Dummy_Data();
        $request = wp_remote_get( GSTM_PLUGIN_URI . '/includes/demo-data/shortcodes.json', array(
            'sslverify' => false,
        ) );
        if ( is_wp_error( $request ) ) {
            return false;
        }
        $shortcodes = wp_remote_retrieve_body( $request );
        $shortcodes = json_decode( $shortcodes, true );
        $wpdb = self::get_wpdb();
        if ( !$shortcodes || !count( $shortcodes ) ) {
            return;
        }
        foreach ( $shortcodes as $shortcode ) {
            $shortcode_settings = $shortcode['shortcode_settings'];
            $shortcode_settings = ( is_string( $shortcode_settings ) ? json_decode( $shortcode_settings, true ) : $shortcode_settings );
            $shortcode_settings['gstm-demo_data'] = true;
            if ( !empty( $categories = $shortcode_settings['categories'] ) ) {
                $shortcode_settings['categories'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_testimonial_category', $categories );
            }
            if ( !empty( $excludeCategories = $shortcode_settings['excludeCategories'] ) ) {
                $shortcode_settings['excludeCategories'] = (array) $Dummy_Data->get_taxonomy_ids_by_slugs( 'gs_testimonial_category', $excludeCategories );
            }
            $data = array(
                "shortcode_name"     => $shortcode['shortcode_name'],
                "shortcode_settings" => json_encode( $shortcode_settings ),
                "created_at"         => current_time( 'mysql' ),
                "updated_at"         => current_time( 'mysql' ),
            );
            $wpdb->insert( table_name(), $data, $this->get_db_columns() );
        }
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
    }

    public function delete_dummy_shortcodes() {
        $wpdb = self::get_wpdb();
        $needle = 'gstm-demo_data';
        $table = table_name();
        $wpdb->query( "DELETE FROM {$table} WHERE shortcode_settings like '%{$needle}%'" );
        // Delete the shortcode cache
        wp_cache_delete( 'gstm_shortcodes', 'gs_testimonials' );
    }

    /**
     * Get defined database columns.
     * 
     * @since  1.10.14
     * @return array Shortcode table database columns.
     */
    public function get_db_columns() {
        return array(
            'shortcode_name'     => '%s',
            'shortcode_settings' => '%s',
            'created_at'         => '%s',
            'updated_at'         => '%s',
        );
    }

    /**
     * Returns the shortcode by given id.
     * 
     * @since  2.0.12
     * 
     * @param mixed $shortcode_id The shortcode id.
     * @param bool  $is_ajax       Ajax status.
     * 
     * @return array|JSON The shortcode.
     */
    public function _get_shortcode( $shortcode_id, $is_ajax = false ) {
        if ( empty( $shortcode_id ) ) {
            if ( $is_ajax ) {
                wp_send_json_error( __( 'Shortcode ID missing', 'gs-testimonial' ), 400 );
            }
            return false;
        }
        $shortcode = wp_cache_get( 'gstm_shortcode' . $shortcode_id, 'gs_testimonials' );
        // Return the cache if found
        if ( $shortcode !== false ) {
            if ( $is_ajax ) {
                wp_send_json_success( $shortcode );
            }
            return $shortcode;
        }
        $wpdb = self::get_wpdb();
        $tableName = table_name();
        $shortcode = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$tableName} WHERE id = %d LIMIT 1", absint( $shortcode_id ) ), ARRAY_A );
        if ( $shortcode ) {
            $shortcode["shortcode_settings"] = json_decode( $shortcode["shortcode_settings"], true );
            $shortcode["shortcode_settings"] = $this->validate_shortcode_settings( $shortcode["shortcode_settings"] );
            wp_cache_add( 'gstm_shortcode' . $shortcode_id, $shortcode, 'gs_testimonials' );
            if ( $is_ajax ) {
                wp_send_json_success( $shortcode );
            }
            return $shortcode;
        }
        if ( $is_ajax ) {
            wp_send_json_error( __( 'No shortcode found', 'gs-testimonial' ), 404 );
        }
        return false;
    }

    public function fetch_shortcodes( $shortcode_ids = [], $is_ajax = false, $minimal = false ) {
        global $wpdb;
        $wpdb = self::get_wpdb();
        $fields = ( $minimal ? 'id, shortcode_name' : '*' );
        $tableName = table_name();
        if ( empty( $shortcode_ids ) ) {
            $shortcodes = wp_cache_get( 'gstm_shortcodes', 'gs_testimonials' );
            if ( $shortcodes === false ) {
                $shortcodes = $wpdb->get_results( "SELECT {$fields} FROM {$tableName} ORDER BY id DESC", ARRAY_A );
                wp_cache_add( 'gstm_shortcodes', $shortcodes, 'gs_testimonials' );
            }
        } else {
            $how_many = count( $shortcode_ids );
            $placeholders = array_fill( 0, $how_many, '%d' );
            $format = implode( ', ', $placeholders );
            $query = "SELECT {$fields} FROM {$tableName} WHERE id IN({$format})";
            $shortcodes = $wpdb->get_results( $wpdb->prepare( $query, $shortcode_ids ), ARRAY_A );
        }
        // check for database gstm_check_db_error
        if ( $this->gstm_check_db_error() ) {
            wp_send_json_error( sprintf( __( 'Database Error: %s' ), $wpdb->last_error ) );
        }
        if ( $is_ajax ) {
            wp_send_json_success( $shortcodes );
        }
        return $shortcodes;
    }

    /**
     * Plugin dashboard strings.
     * 
     * @since  2.0.12
     * @return array Array of the plugin dashboard strings.
     */
    public function get_translation_srtings() {
        return [
            'filter_all_text'                 => __( 'Filter All Text', 'gs-testimonial' ),
            'filter_all_text__details'        => __( 'Modify the filter All text.', 'gs-testimonial' ),
            'details-control'                 => __( 'Details Control', 'gs-testimonial' ),
            'define-max-number'               => __( 'Define Max Number', 'gs-testimonial' ),
            'autoplay'                        => __( 'Enable Autoplay', 'gs-testimonial' ),
            'autoplay_delay'                  => __( 'Autoplay Delay', 'gs-testimonial' ),
            'pause_on_hover'                  => __( 'Pause On Hover', 'gs-testimonial' ),
            'pause_on_hover__details'         => __( 'Carousel autoplay will be paused when hover over on the widget', 'gs-testimonial' ),
            'reverse_direction'               => __( 'Reverse Direction', 'gs-testimonial' ),
            'reverse_direction__details'      => __( 'Reverse Direction', 'gs-testimonial' ),
            'border_thickness'                => __( 'Border Thickness', 'gs-testimonial' ),
            'border_thickness__help'          => __( 'Author image Border Thickness. Default 3 PX. Max 10 PX', 'gs-testimonial' ),
            'designation_label'               => __( 'Designation Label', 'gs-testimonial' ),
            'designation_label__help'         => __( 'Designation Label', 'gs-testimonial' ),
            'company_label'                   => __( 'Company Label', 'gs-testimonial' ),
            'company_label__help'             => __( 'Company Label', 'gs-testimonial' ),
            'image_height'                    => __( 'Image Height', 'gs-testimonial' ),
            'image_height__help'              => __( 'Author image size in height. Default 86 PX. Max 125 PX Note : Use same size height & width to display Round image ( Pro Feature )', 'gs-testimonial' ),
            'image_width'                     => __( 'Image Width', 'gs-testimonial' ),
            'image_width__help'               => __( 'Author image size in width. Default 86 PX. Max 125 PX', 'gs-testimonial' ),
            'filter_cat_pos'                  => __( 'Filter Category Position', 'gs-testimonial' ),
            'show_title'                      => __( 'Show Title', 'gs-testimonial' ),
            'show_title__details'             => __( 'Enable / Disable Title field', 'gs-testimonial' ),
            'allow_html'                      => __( 'Allow HTML', 'gs-testimonial' ),
            'allow_html__details'             => __( 'Enable / Disable Allow HTML', 'gs-testimonial' ),
            'line-control'                    => __( 'Line Control', 'gs-testimonial' ),
            'line-control__details'           => __( 'Enable / Disable Line Control', 'gs-testimonial' ),
            'show_popup_rating'               => __( 'Show Rating on Popup', 'gs-testimonial' ),
            'show_popup_rating__details'      => __( 'Enable / Disable Rating on Popup', 'gs-testimonial' ),
            'show_published_date'             => __( 'Show Published Date', 'gs-testimonial' ),
            'show_published_date__details'    => __( 'Enable / Disable Published Date', 'gs-testimonial' ),
            'show_designation'                => __( 'Show Designation', 'gs-testimonial' ),
            'show_designation__details'       => __( 'Enable/Disable Designation field', 'gs-testimonial' ),
            'show_company'                    => __( 'Show Company Name', 'gs-testimonial' ),
            'show_company__details'           => __( 'Show / Hide Company Name', 'gs-testimonial' ),
            'ratings'                         => __( 'Show Ratings', 'gs-testimonial' ),
            'ratings__details'                => __( 'Show / Hide Ratings . Default OFF', 'gs-testimonial' ),
            'image'                           => __( 'Show Author Image', 'gs-testimonial' ),
            'image__details'                  => __( 'Show / Hide Author Image . Default OFF', 'gs-testimonial' ),
            'company_logo'                    => __( 'Show Company Logo', 'gs-testimonial' ),
            'company_logo__details'           => __( 'Show / Hide Company Logo . Default OFF', 'gs-testimonial' ),
            'view_type'                       => __( 'View Type', 'gs-testimonial' ),
            'imageModes'                      => __( 'Image Mode', 'gs-testimonial' ),
            'imageSizes'                      => __( 'Image Sizes', 'gs-testimonial' ),
            'imageSizes--help'                => __( 'Use pre registered image sizes by the theme you use, it will help to optimize the images.', 'gs-testimonial' ),
            'image_style'                     => __( 'Image Style', 'gs-testimonial' ),
            'desktop_columns'                 => __( 'Desktop Columns', 'gs-testimonial' ),
            'tablet_columns'                  => __( 'Tablet Columns', 'gs-testimonial' ),
            'columns_mobile'                  => __( 'Mobile Columns', 'gs-testimonial' ),
            'columns_small_mobile'            => __( 'Small Mobile Columns', 'gs-testimonial' ),
            'theme'                           => __( 'Theme', 'gs-testimonial' ),
            'speed'                           => __( 'Carousel Speed', 'gs-testimonial' ),
            'count'                           => __( 'Count', 'gs-testimonial' ),
            'count--help'                     => __( 'Set number of testimonial to display.', 'gs-testimonial' ),
            'orderby'                         => __( 'OrderBy', 'gs-testimonial' ),
            'orderby--help'                   => __( 'Use preffered orderby attribute', 'gs-testimonial' ),
            'order'                           => __( 'Order', 'gs-testimonial' ),
            'order--help'                     => __( 'Set order attribute', 'gs-testimonial' ),
            'category'                        => __( 'Category', 'gs-testimonial' ),
            'category--help'                  => __( 'Select specific categories to show that specific categories testimonials.', 'gs-testimonial' ),
            'exclude-category'                => __( 'Exclude Category', 'gs-testimonial' ),
            'exclude-category--help'          => __( 'Select specific categories to hide that specific categories testimonials.', 'gs-testimonial' ),
            'authors'                         => __( 'Authors', 'gs-testimonial' ),
            'authors--help'                   => __( 'Select specific authors to show that specific authors testimonials.', 'gs-testimonial' ),
            'exclude-authors'                 => __( 'Exclude Authors', 'gs-testimonial' ),
            'exclude-authors--help'           => __( 'Select specific authors to hide that specific authors testimonials.', 'gs-testimonial' ),
            'preferences'                     => __( 'Preferences', 'gs-testimonial' ),
            'save-preferences'                => __( 'Save Preferences', 'gs-testimonial' ),
            'custom-css'                      => __( 'Custom CSS', 'gs-testimonial' ),
            'enable_single_page'              => __( 'Enable Single Pages', 'gs-testimonial' ),
            'read_more_text'                  => __( 'Read More Text', 'gs-testimonial' ),
            'company-label'                   => __( 'Company Label', 'gs-testimonial' ),
            'company-label--help'             => __( 'Set company label', 'gs-testimonial' ),
            'designation-label'               => __( 'Designation Label', 'gs-testimonial' ),
            'designation-label--help'         => __( 'Set designation label', 'gs-testimonial' ),
            'loadmore-button-label'           => __( 'Loadmore Button Label', 'gs-testimonial' ),
            'loadmore-button-label--help'     => __( 'Set designation label', 'gs-testimonial' ),
            'shortcodes'                      => __( 'Shortcodes', 'gs-testimonial' ),
            'shortcode'                       => __( 'Shortcode', 'gs-testimonial' ),
            'global-settings-label'           => __( 'Global settings which are going to work on the whole plugin.', 'gs-testimonial' ),
            'all-shortcodes'                  => __( 'All shortcodes', 'gs-testimonial' ),
            'create-new-shortcode'            => __( 'Create New Shortcode', 'gs-testimonial' ),
            'name'                            => __( 'Name', 'gs-testimonial' ),
            'action'                          => __( 'Action', 'gs-testimonial' ),
            'actions'                         => __( 'Actions', 'gs-testimonial' ),
            'edit'                            => __( 'Edit', 'gs-testimonial' ),
            'clone'                           => __( 'Clone', 'gs-testimonial' ),
            'delete'                          => __( 'Delete', 'gs-testimonial' ),
            'delete-all'                      => __( 'Delete All', 'gs-testimonial' ),
            'general-settings'                => __( 'General Settings', 'gs-testimonial' ),
            'style-settings'                  => __( 'Style Settings', 'gs-testimonial' ),
            'query-settings'                  => __( 'Query Settings', 'gs-testimonial' ),
            'general-settings-short'          => __( 'General', 'gs-testimonial' ),
            'style-settings-short'            => __( 'Style', 'gs-testimonial' ),
            'query-settings-short'            => __( 'Query', 'gs-testimonial' ),
            'name-of-the-shortcode'           => __( 'Name of the Shortcode', 'gs-testimonial' ),
            'save-shortcode'                  => __( 'Save Shortcode', 'gs-testimonial' ),
            'shortcode-name'                  => __( 'Shortcode Name', 'gs-testimonial' ),
            'carousel_navs_enabled'           => __( 'Enable Carousel Navs', 'gs-testimonial' ),
            'carousel_navs_enabled__details'  => __( 'Enable carousel navs for this theme, it may not available for certain theme', 'gs-testimonial' ),
            'carousel_navs_style'             => __( 'Carousel Navs Style', 'gs-testimonial' ),
            'carousel_navs_style__details'    => __( 'Select carousel navs style, this is available for certain theme', 'gs-testimonial' ),
            'gs_slider_nav_color'             => __( 'Nav Color', 'gs-testimonial' ),
            'gs_slider_nav_bg_color'          => __( 'Nav BG Color', 'gs-testimonial' ),
            'gs_slider_nav_hover_color'       => __( 'Nav Hover Color', 'gs-testimonial' ),
            'gs_slider_nav_hover_bg_color'    => __( 'Nav Hover BG Color', 'gs-testimonial' ),
            'carousel_dots_enabled'           => __( 'Enable Carousel Dots', 'gs-testimonial' ),
            'carousel_dots_enabled__details'  => __( 'Enable carousel dots for this theme, it may not available for certain theme', 'gs-testimonial' ),
            'dynamic_dots_enabled'            => __( 'Enable Dynamic Dots', 'gs-testimonial' ),
            'dynamic_dots_enabled__details'   => __( 'Enable carousel dynamic dots for this theme.', 'gs-testimonial' ),
            'carousel_dots_style'             => __( 'Dots Style', 'gs-testimonial' ),
            'carousel_dots_style__details'    => __( 'Select carousel dots style, this is available for certain theme', 'gs-testimonial' ),
            'carousel_dots_position'          => __( 'Dots Position', 'gs-testimonial' ),
            'carousel_dots_position__details' => __( 'Select carousel dots position, this is available for certain theme', 'gs-testimonial' ),
            'gs_slider_dot_color'             => __( 'Dots Color', 'gs-testimonial' ),
            'gs_slider_dot_hover_color'       => __( 'Dots Active Color', 'gs-testimonial' ),
            'filters_tab_style'               => __( 'Filter Tab Style', 'gs-testimonial' ),
            'filters_tab_style__details'      => __( 'Select filters tab style', 'gs-testimonial' ),
            'filter_color'                    => __( 'Filter Color', 'gs-testimonial' ),
            'filter_bg_color'                 => __( 'Filter BG Color', 'gs-testimonial' ),
            'filter_border_color'             => __( 'Filter Border Color', 'gs-testimonial' ),
            'filter_color_active'             => __( 'Filter Active Color', 'gs-testimonial' ),
            'filter_bg_color_active'          => __( 'Filter Active BG Color', 'gs-testimonial' ),
            'filter_border_color_active'      => __( 'Filter Active Border Color', 'gs-testimonial' ),
            'item_bg_color'                   => __( 'Box Background Color', 'gs-testimonial' ),
            'testimonial_title_color'         => __( 'Title Color', 'gs-testimonial' ),
            'testimonial_color'               => __( 'Testimonial Color', 'gs-testimonial' ),
            'read_more_color'                 => __( 'Read More Color', 'gs-testimonial' ),
            'read_more_hover_color'           => __( 'Read More Hover Color', 'gs-testimonial' ),
            'name_color'                      => __( 'Name Color', 'gs-testimonial' ),
            'ratings_color'                   => __( 'Ratings Color', 'gs-testimonial' ),
            'designation_color'               => __( 'Designation Color', 'gs-testimonial' ),
            'company_color'                   => __( 'Company Color', 'gs-testimonial' ),
            'info_color'                      => __( 'Info Color', 'gs-testimonial' ),
            'info_icon_color'                 => __( 'Info Icon Color', 'gs-testimonial' ),
        ];
    }

}
