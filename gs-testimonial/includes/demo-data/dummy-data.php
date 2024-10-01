<?php

namespace GSTM;

/**
 * Protect direct access
 */
defined('ABSPATH') || exit;

final class Dummy_Data {

    private $is_pro;

    public function __construct() {

        $this->is_pro = gstm_fs()->is_paying_or_trial();

        add_action( 'admin_notices', array($this, 'gstm_dummy_data_admin_notice') );

        add_action( 'wp_ajax_gstm_dismiss_demo_data_notice', array($this, 'gstm_dismiss_demo_data_notice') );

        add_action( 'wp_ajax_gstm_import_items_data', array($this, 'import_items_data') );

        add_action( 'wp_ajax_gstm_remove_items_data', array($this, 'remove_items_data') );

        add_action( 'wp_ajax_gstm_import_shortcode_data', array($this, 'import_shortcode_data') );

        add_action( 'wp_ajax_gstm_remove_shortcode_data', array($this, 'remove_shortcode_data') );

        add_action( 'wp_ajax_gstm_import_all_data', array($this, 'import_all_data') );

        add_action( 'wp_ajax_gstm_remove_all_data', array($this, 'remove_all_data') );

        add_action( 'gs_tstm_after_shortcode_submenu', array($this, 'register_sub_menu') );

        add_action( 'admin_init', array($this, 'maybe_auto_import_all_data') );

        // Remove dummy indicator
        add_action( 'edit_post_gs_testimonial', array($this, 'remove_dummy_indicator'), 10 );

        // Import Process
        add_action( 'gstm_dummy_attachments_process_start', function() {

            // Force delete option if have any
            delete_option( 'gstm_dummy_items_data_created' );

            // Force update the process
            set_transient( 'gstm_dummy_items_data_creating', 1, 3 * MINUTE_IN_SECONDS );

        });
        
        add_action( 'gstm_dummy_attachments_process_finished', function() {

            $this->create_dummy_terms();

        });
        
        add_action( 'gstm_dummy_terms_process_finished', function() {

            $this->create_dummy_items();

        });
        
        add_action( 'gstm_dummy_items_process_finished', function() {

            // clean the record that we have started a process
            delete_transient( 'gstm_dummy_items_data_creating' );

            // Add a track so we never duplicate the process
            update_option( 'gstm_dummy_items_data_created', 1 );

        });
        
        // Shortcodes
        add_action( 'gstm_dummy_shortcodes_process_start', function() {

            // Force delete option if have any
            delete_option( 'gstm_dummy_shortcode_data_created' );

            // Force update the process
            set_transient( 'gstm_dummy_shortcode_data_creating', 1, 3 * MINUTE_IN_SECONDS );

        });

        add_action( 'gstm_dummy_shortcodes_process_finished', function() {

            // clean the record that we have started a process
            delete_transient( 'gstm_dummy_shortcode_data_creating' );

            // Add a track so we never duplicate the process
            update_option( 'gstm_dummy_shortcode_data_created', 1 );

        });
        
    }

    public function register_sub_menu() {

        add_submenu_page( 
            'edit.php?post_type=gs_testimonial', 'Install Demo', 'Install Demo', 'manage_options', 'gst-shortcodes#/demo-data', array( plugin()->builder, 'view' )
        );

    }

    public function get_taxonomy_list() {
        return ['gs_testimonial_category'];
    }

    public function remove_dummy_indicator( $post_id ) {

        if ( empty( get_post_meta($post_id, 'gstm-demo_data', true) ) ) return;
        
        $taxonomies = $this->get_taxonomy_list();

        // Remove dummy indicator from texonomies
        $dummy_terms = wp_get_post_terms( $post_id, $taxonomies, [
            'fields' => 'ids',
            'meta_key' => 'gstm-demo_data',
            'meta_value' => 1,
        ]);

        if ( !empty($dummy_terms) ) {
            foreach( $dummy_terms as $term_id ) {
                delete_term_meta( $term_id, 'gstm-demo_data', 1 );
            }
            delete_transient( 'gstm_dummy_terms' );
        }

        // Remove dummy indicator from attachments
        $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true );
        if ( !empty($thumbnail_id) ) delete_post_meta( $thumbnail_id, 'gstm-demo_data', 1 );

        $company_id = get_post_meta( $post_id, 'rudr_img', true );
        if ( !empty($company_id) ) delete_post_meta( $company_id, 'gstm-demo_data', 1 );
        
        delete_transient( 'gstm_dummy_attachments' );
        
        // Remove dummy indicator from post
        delete_post_meta( $post_id, 'gstm-demo_data', 1 );
        delete_transient( 'gstm_dummy_items' );

    }

    public function maybe_auto_import_all_data() {

        if ( get_option('gs_testimonial_autoimport_done', false) == true ) return;

        $testimonials = get_posts([
            'numberposts' => -1,
            'post_type' => 'gs_testimonial',
            'fields' => 'ids'
        ]);

        $shortcodes = plugin()->builder->fetch_shortcodes();

        if ( empty($testimonials) && empty($shortcodes) ) {

            $this->_import_items_data( false );
            $this->_import_shortcode_data( false );
        }
        
        update_option( 'gs_testimonial_autoimport_done', true );
    }

    public function import_all_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        $response = [
            'items'     => $this->_import_items_data( false ),
            'shortcode' => $this->_import_shortcode_data( false )
        ];

        if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

        return $response;

    }

    public function remove_all_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        $response = [
            'items' => $this->_remove_items_data( false ),
            'shortcode' => $this->_remove_shortcode_data( false )
        ];

        if ( wp_doing_ajax() ) wp_send_json_success( $response, 200 );

        return $response;

    }

    public function import_items_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        // Start importing
        $this->_import_items_data();

    }

    public function remove_items_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        // Remove data
        $this->_remove_items_data();

    }

    public function import_shortcode_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        // Start importing
        $this->_import_shortcode_data();

    }

    public function remove_shortcode_data() {

        // Validate nonce && check permission
        if ( !check_admin_referer('_gstm_simport_gstm_demo_gs_') || !current_user_can('manage_options') ) wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );

        // Hide the notice
        update_option( 'gstm_dismiss_demo_data_notice', 1 );

        // Remove data
        $this->_remove_shortcode_data();

    }

    public function _import_items_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        // Data already imported
        if ( get_option('gstm_dummy_items_data_created') !== false || get_transient('gstm_dummy_items_data_creating') !== false ) {

            $message_202 = __( 'Dummy Testimonials already imported', 'gs-testimonial' );

            if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
            
            return [
                'status' => 202,
                'message' => $message_202
            ];

        }
        
        // Importing demo data
        $this->create_dummy_attachments();

        $message = __( 'Dummy Testimonials imported', 'gs-testimonial' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    public function _remove_items_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        $this->delete_dummy_attachments();
        $this->delete_dummy_terms();
        $this->delete_dummy_items();

        delete_option( 'gstm_dummy_items_data_created' );
        delete_transient( 'gstm_dummy_items_data_creating' );

        $message = __( 'Dummy Testimonials deleted', 'gs-testimonial' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    public function _import_shortcode_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        // Data already imported
        if ( get_option('gstm_dummy_shortcode_data_created') !== false || get_transient('gstm_dummy_shortcode_data_creating') !== false ) {

            $message_202 = __( 'Dummy Shortcodes already imported', 'gs-testimonial' );

            if ( $is_ajax ) wp_send_json_success( $message_202, 202 );
            
            return [
                'status' => 202,
                'message' => $message_202
            ];

        }
        
        // Importing demo shortcodes
        $this->create_dummy_shortcodes();

        $message = __( 'Dummy Shortcodes imported', 'gs-testimonial' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    public function _remove_shortcode_data( $is_ajax = null ) {

        if ( $is_ajax === null ) $is_ajax = wp_doing_ajax();

        $this->delete_dummy_shortcodes();

        delete_option( 'gstm_dummy_shortcode_data_created' );
        delete_transient( 'gstm_dummy_shortcode_data_creating' );

        $message = __( 'Dummy Shortcodes deleted', 'gs-testimonial' );

        if ( $is_ajax ) wp_send_json_success( $message, 200 );

        return [
            'status' => 200,
            'message' => $message
        ];

    }

    public function get_taxonomy_ids_by_slugs( $taxonomy_group, $taxonomy_slugs = [] ) {

        $_terms = $this->get_dummy_terms();

        if ( empty($_terms) ) return [];
        
        $_terms = wp_filter_object_list( $_terms, [ 'taxonomy' => $taxonomy_group ] );
        $_terms = array_values( $_terms );      // reset the keys
        
        if ( empty($_terms) ) return [];
        
        $term_ids = [];
        
        foreach ( $taxonomy_slugs as $slug ) {
            $key = array_search( $slug, array_column($_terms, 'slug') );
            if ( $key !== false ) $term_ids[] = $_terms[$key]['term_id'];
        }

        return $term_ids;

    }

    public function get_attachment_id_by_filename( $filename ) {

        $attachments = $this->get_dummy_attachments();
        
        if ( empty($attachments) ) return '';
        
        $attachments = wp_filter_object_list( $attachments, [ 'post_name' => $filename ] );
        if ( empty($attachments) ) return '';
        
        $attachments = array_values( $attachments );
        
        return $attachments[0]->ID;

    }

    public function get_tax_inputs( $tax_inputs = [] ) {

        if ( empty($tax_inputs) ) return $tax_inputs;

        foreach( $tax_inputs as $tax_input => $tax_params ) {
            $tax_inputs[$tax_input] = $this->get_taxonomy_ids_by_slugs( $tax_input, $tax_params );
        }

        return $tax_inputs;

    }

    public function get_meta_inputs( $meta_inputs = [] ) {

        // if ( isset($meta_inputs['gs_t_client_name']) ) unset( $meta_inputs['gs_t_client_name'] );
        // if ( isset($meta_inputs['gs_t_client_design']) ) unset( $meta_inputs['gs_t_client_design'] );
        // if ( isset($meta_inputs['gs_t_rating']) ) unset( $meta_inputs['gs_t_rating'] );

        if ( ! $this->is_pro ) {

            if ( isset($meta_inputs['gs_t_client_email_address']) ) unset( $meta_inputs['gs_t_client_email_address'] );
            if ( isset($meta_inputs['gs_t_client_location']) ) unset( $meta_inputs['gs_t_client_location'] );
            if ( isset($meta_inputs['gs_t_client_phone']) ) unset( $meta_inputs['gs_t_client_phone'] );
            if ( isset($meta_inputs['gs_t_website_url']) ) unset( $meta_inputs['gs_t_website_url'] );
            if ( isset($meta_inputs['gs_t_client_company']) ) unset( $meta_inputs['gs_t_client_company'] );
            if ( isset($meta_inputs['gs_t_video_url']) ) unset( $meta_inputs['gs_t_video_url'] );
            if ( isset($meta_inputs['gs_t_social_profiles']) ) unset( $meta_inputs['gs_t_social_profiles'] );
            if ( isset($meta_inputs['rudr_img']) ) unset( $meta_inputs['rudr_img'] );

        }

        $meta_inputs['_thumbnail_id'] = $this->get_attachment_id_by_filename( $meta_inputs['_thumbnail_id'] );
        
        if ( $this->is_pro ) {
            $meta_inputs['rudr_img'] = $this->get_attachment_id_by_filename( $meta_inputs['rudr_img'] );
        }

        return $meta_inputs;

    }

    // Items
    public function create_dummy_items() {

        do_action( 'gstm_dummy_items_process_start' );

        $post_status = 'publish';
        $post_type = 'gs_testimonial';

        $items = [];

        $items[] = array(
            'post_title'    => 'Making Good Great',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-one', 'group-three']
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-1',
                'rudr_img' => 'gstm-client-company-1',

                'gs_t_client_name' => 'Haley Bennet',
                'gs_t_client_design' => 'Designer',
                'gs_t_rating' => '4.5',

                'gs_t_client_email_address' => 'haleybennet@yelp.com',
                'gs_t_client_location' => '8490 Beverly Blvd, Los Angeles, California, US',
                'gs_t_client_phone' => '(860) 073-7135',
                'gs_t_website_url' => 'https://www.yelp.com',
                'gs_t_client_company' => 'Yelp',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        $items[] = array(
            'post_title'    => 'Dedication and Expertise',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-one', 'group-two'],
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-2',
                'rudr_img' => 'gstm-client-company-2',

                'gs_t_client_name' => 'Janus Azra',
                'gs_t_client_design' => 'Business Manager',
                'gs_t_rating' => '5',

                'gs_t_client_email_address' => 'janusazra@paypal.com',
                'gs_t_client_location' => '610 Kildeer Drive, Virginia Beach, Virginia',
                'gs_t_client_phone' => '(252) 258-3799',
                'gs_t_website_url' => 'https://www.paypal.com',
                'gs_t_client_company' => 'PayPal',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        $items[] = array(
            'post_title'    => 'Trusted Plugin',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-three'],
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-3',
                'rudr_img' => 'gstm-client-company-3',

                'gs_t_client_name' => 'Effie Diana',
                'gs_t_client_design' => 'UX Designer',
                'gs_t_rating' => '4.5',

                'gs_t_client_email_address' => 'haleybennet@yelp.com',
                'gs_t_client_location' => '8490 Beverly Blvd, Los Angeles, California, US',
                'gs_t_client_phone' => '(860) 073-7135',
                'gs_t_website_url' => 'https://www.airbnb.com',
                'gs_t_client_company' => 'Airbnb',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        $items[] = array(
            'post_title'    => 'Inspired Performance',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-two']
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-4',
                'rudr_img' => 'gstm-client-company-4',

                'gs_t_client_name' => 'Damjan Aparna',
                'gs_t_client_design' => 'Web Developer',
                'gs_t_rating' => '5',

                'gs_t_client_email_address' => 'haleybennet@yelp.com',
                'gs_t_client_location' => '8490 Beverly Blvd, Los Angeles, California, US',
                'gs_t_client_phone' => '(860) 073-7135',
                'gs_t_website_url' => 'https://www.webflow.com',
                'gs_t_client_company' => 'Webflow',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        $items[] = array(
            'post_title'    => 'Amazing Plugin',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-two', 'group-three']
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-5',
                'rudr_img' => 'gstm-client-company-5',

                'gs_t_client_name' => 'Thando Tudur',
                'gs_t_client_design' => 'Business Manager',
                'gs_t_rating' => '4.5',

                'gs_t_client_email_address' => 'haleybennet@yelp.com',
                'gs_t_client_location' => '8490 Beverly Blvd, Los Angeles, California, US',
                'gs_t_client_phone' => '(860) 073-7135',
                'gs_t_website_url' => 'https://www.payoneer.com',
                'gs_t_client_company' => 'Payoneer',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        $items[] = array(
            'post_title'    => 'Fascinating and speedy',
            'post_content'  => 'Yelp’s user-friendly interface and intuitive search functionality have made finding the perfect restaurant, salon, or service provider an absolute breeze. With just a few clicks, I can access a wealth of information, including business hours, contact details, menus, pricing, and most importantly, authentic reviews from fellow customers. This wealth of information empowers me to make well-informed decisions about where to spend my time and money.',
            'post_status'   => $post_status,
            'post_type' => $post_type,
            'post_date' => '2020-08-10 07:01:44',
            'tax_input' => $this->get_tax_inputs([
                'gs_testimonial_category' => ['group-three']
            ]),
            'meta_input' => $this->get_meta_inputs([

                '_thumbnail_id' => 'gstm-client-6',
                'rudr_img' => 'gstm-client-company-6',

                'gs_t_client_name' => 'Julia Roberts',
                'gs_t_client_design' => 'Content Creator',
                'gs_t_rating' => '5',

                'gs_t_client_email_address' => 'haleybennet@yelp.com',
                'gs_t_client_location' => '8490 Beverly Blvd, Los Angeles, California, US',
                'gs_t_client_phone' => '(860) 073-7135',
                'gs_t_website_url' => 'https://www.lightspeed.com',
                'gs_t_client_company' => 'Lightspeed',
                'gs_t_video_url' => 'https://www.youtube.com/watch?v=v5XrZFp0Yf8&ab_channel=GSPluginsTutorials',

                'gs_t_social_profiles' => [
                    'fab fa-x-twitter' => 'https://twitter.com/WilliamMDean',
                    'fab fa-google-plus-g' => 'https://google.com/WilliamMDean',
                    'fab fa-facebook-f' => 'https://facebook.com/WilliamMDean',
                    'fab fa-linkedin-in' => 'https://linkedin.com/WilliamMDean',
                ],
            ])
        );

        foreach ( $items as $item ) {
            // Insert the post into the database
            $post_id = wp_insert_post( $item );
            // Add meta value for demo
            if ( $post_id ) add_post_meta( $post_id, 'gstm-demo_data', 1 );
        }

        do_action( 'gstm_dummy_items_process_finished' );

    }

    public function delete_dummy_items() {
        
        $items = $this->get_dummy_items();

        if ( empty($items) ) return;

        foreach ($items as $item) {
            wp_delete_post( $item->ID, true );
        }

        delete_transient( 'gstm_dummy_items' );

    }

    public function get_dummy_items() {

        $items = get_transient( 'gstm_dummy_items' );

        if ( false !== $items ) return $items;

        $items = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'gs_testimonial',
            'meta_key' => 'gstm-demo_data',
            'meta_value' => 1,
        ));
        
        if ( is_wp_error($items) || empty($items) ) {
            delete_transient( 'gstm_dummy_items' );
            return [];
        }
        
        set_transient( 'gstm_dummy_items', $items, 3 * MINUTE_IN_SECONDS );

        return $items;

    }

    public function http_request_args( $args ) {
        
        $args['sslverify'] = false;

        return $args;

    }

    // Attachments
    public function create_dummy_attachments() {

        do_action( 'gstm_dummy_attachments_process_start' );

        require_once( ABSPATH . 'wp-admin/includes/image.php' );

        $attachment_files = [
            'gstm-client-1.jpg',
            'gstm-client-2.jpg',
            'gstm-client-3.jpg',
            'gstm-client-4.jpg',
            'gstm-client-5.jpg',
            'gstm-client-6.jpg'
        ];

        if ( $this->is_pro ) {

            $attachment_files = array_merge( $attachment_files, [
                'gstm-client-company-1.png',
                'gstm-client-company-2.png',
                'gstm-client-company-3.png',
                'gstm-client-company-4.png',
                'gstm-client-company-5.png',
                'gstm-client-company-6.png'
            ]);

        }

        add_filter( 'http_request_args', [ $this, 'http_request_args' ] );

        wp_raise_memory_limit( 'image' );

        foreach ( $attachment_files as $file ) {

            $file = GSTM_PLUGIN_URI . '/assets/img/dummy-data/' . $file;

            $filename = basename($file);

            $get = wp_remote_get( $file );
            $type = wp_remote_retrieve_header( $get, 'content-type' );
            $mirror = wp_upload_bits( $filename, null, wp_remote_retrieve_body( $get ) );
            
            // Prepare an array of post data for the attachment.
            $attachment = array(
                'guid'           => $mirror['url'],
                'post_mime_type' => $type,
                'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );
            
            // Insert the attachment.
            $attach_id = wp_insert_attachment( $attachment, $mirror['file'] );
            
            // Generate the metadata for the attachment, and update the database record.
            $attach_data = wp_generate_attachment_metadata( $attach_id, $mirror['file'] );
            wp_update_attachment_metadata( $attach_id, $attach_data );

            add_post_meta( $attach_id, 'gstm-demo_data', 1 );

        }

        remove_filter( 'http_request_args', [ $this, 'http_request_args' ] );

        do_action( 'gstm_dummy_attachments_process_finished' );

    }

    public function delete_dummy_attachments() {
        
        $attachments = $this->get_dummy_attachments();

        if ( empty($attachments) ) return;

        foreach ($attachments as $attachment) {
            wp_delete_attachment( $attachment->ID, true );
        }

        delete_transient( 'gstm_dummy_attachments' );

    }

    public function get_dummy_attachments() {

        $attachments = get_transient( 'gstm_dummy_attachments' );

        if ( false !== $attachments ) return $attachments;

        $attachments = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'attachment',
            'post_status' => 'inherit',
            'meta_key' => 'gstm-demo_data',
            'meta_value' => 1,
        ));
        
        if ( is_wp_error($attachments) || empty($attachments) ) {
            delete_transient( 'gstm_dummy_attachments' );
            return [];
        }
        
        set_transient( 'gstm_dummy_attachments', $attachments, 3 * MINUTE_IN_SECONDS );

        return $attachments;

    }
    
    // Terms
    public function create_dummy_terms() {

        do_action( 'gstm_dummy_terms_process_start' );
        
        $terms = [
            // 3 Groups
            [
                'name' => 'Group One',
                'slug' => 'group-one',
                'group' => 'gs_testimonial_category'
            ],
            [
                'name' => 'Group Two',
                'slug' => 'group-two',
                'group' => 'gs_testimonial_category'
            ],
            [
                'name' => 'Group Three',
                'slug' => 'group-three',
                'group' => 'gs_testimonial_category'
            ]
        ];

        foreach( $terms as $term ) {

            $response = wp_insert_term( $term['name'], $term['group'], array('slug' => $term['slug']) );

            if ( ! is_wp_error($response) ) {
                add_term_meta( $response['term_id'], 'gstm-demo_data', 1 );
            }

        }

        do_action( 'gstm_dummy_terms_process_finished' );

    }
    
    public function delete_dummy_terms() {
        
        $terms = $this->get_dummy_terms();

        if ( empty($terms) ) return;

        foreach ( $terms as $term ) {
            wp_delete_term( $term['term_id'], $term['taxonomy'] );
        }

        delete_transient( 'gstm_dummy_terms' );

    }

    public function get_dummy_terms() {

        $terms = get_transient( 'gstm_dummy_terms' );

        if ( false !== $terms ) return $terms;

        $taxonomies = $this->get_taxonomy_list();

        $terms = get_terms( array(
            'taxonomy' => $taxonomies,
            'hide_empty' => false,
            'meta_key' => 'gstm-demo_data',
            'meta_value' => 1,
        ));

        $terms = json_decode( json_encode( $terms ), true ); // Object to Array
        
        if ( is_wp_error($terms) || empty($terms) ) {
            delete_transient( 'gstm_dummy_terms' );
            return [];
        }

        set_transient( 'gstm_dummy_terms', $terms, 3 * MINUTE_IN_SECONDS );

        return $terms;

    }

    // Shortcode
    public function create_dummy_shortcodes() {

        do_action( 'gstm_dummy_shortcodes_process_start' );

        plugin()->builder->create_dummy_shortcodes();

        do_action( 'gstm_dummy_shortcodes_process_finished' );

    }

    public function delete_dummy_shortcodes() {
        
        plugin()->builder->delete_dummy_shortcodes();

    }

    // Notice
    function gstm_dummy_data_admin_notice() {

        // delete_option('gstm_dismiss_demo_data_notice');

        if ( get_option('gstm_dismiss_demo_data_notice') ) return;

        if ( get_current_screen()->id == 'gs_testimonial_page_gst-shortcode' ) return;

        ?>
        <div id="gstm-dummy-data-install--notice" class="notice notice-success is-dismissible">

            <h3>Solid Testimonials - Install Demo Data!</h3>

            <p><b>Solid Testimonials</b> plugin offers to install <b>demo data</b> with just one click.</p>
            <p>You can remove the data anytime if you want by another click.</p>

            <p style="margin-top: 16px; margin-bottom: 18px;">

                <a href="<?php echo admin_url( 'edit.php?post_type=gs_testimonial&page=gst-shortcodes#/demo-data' ); ?>" class="button button-primary" style="margin-right: 10px;">Install Demo Data</a>

                <a href="javascript:void(0)" onclick="jQuery('#gstm-dummy-data-install--notice').slideUp(); jQuery.post(ajaxurl, {action: 'gstm_dismiss_demo_data_notice', nonce: '<?php echo wp_create_nonce('_gstm_dismiss_demo_data_notice_gs_'); ?>' });">
                    <?php _e( "Don't show this message again", 'gs-testimonial'); ?>
                </a>

            </p>

        </div>
        <?php

    }

    function gstm_dismiss_demo_data_notice() {

        $nonce = isset($_REQUEST['nonce']) ? $_REQUEST['nonce'] : null;

        if ( ! wp_verify_nonce( $nonce, '_gstm_dismiss_demo_data_notice_gs_') ) {
            wp_send_json_error( __('Unauthorised Request', 'gs-testimonial'), 401 );
        }

        update_option( 'gstm_dismiss_demo_data_notice', 1 );

    }

}