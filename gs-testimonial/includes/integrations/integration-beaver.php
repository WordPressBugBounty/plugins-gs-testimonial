<?php
namespace GSTM;
use FLBuilder;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

// Integration Class
class Integration_Beaver {

    private static $_instance = null;
    
    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;            
    }

    public function __construct() {
        add_action( 'init', array( $this, 'init' ) );            
    }

    public function init() {
        if ( class_exists( '\FLBuilder' ) ) {
            require_once GSTM_PLUGIN_DIR . 'includes/integrations/beaver/beaver-widget-gstm.php';
            FLBuilder::register_module( 'GSTM\Beaver', array(
                'my-tab-1'      => array(
                    'title'         => __( 'Tab 1', 'gs-testimonial' ),
                    'sections'      => array(
                        'my-section-1'  => array(
                            'title'         => __( 'Shortcode', 'gs-testimonial' ),
                            'fields'        => array(
                                'shortcode_id' => array(
                                    'type'          => 'select',
                                    'label'         => __('Select Shortcode', 'gs-testimonial'),
                                    'options'       => $this->get_shortcode_list(),
                                    'preview'      => array(
                                        'type'         => 'none'
                                    )
                                ),
                            )
                        )
                    )
                )
            ));
        }
    }

    protected function get_shortcode_list() {

        $shortcodes = get_shortcodes();

        if ( !empty($shortcodes) ) {
            return wp_list_pluck( $shortcodes, 'shortcode_name', 'id' );
        }
        
        return [];
    }
}
