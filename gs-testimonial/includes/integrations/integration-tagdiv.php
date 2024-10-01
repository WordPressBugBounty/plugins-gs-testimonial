<?php
namespace GSTM;

/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;

class Integration_TagDiv {

	private static $_instance = null;
        
    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
        
    }

    public function __construct() {
        add_action( 'td_global_after', [ $this, 'register_block' ] );
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action( 'admin_enqueue_scripts', [$this, 'block_css'] );
    }

    public function block_css() {
        $icon = GSTM_PLUGIN_URI . '/assets/img/icon.svg';
        $css = sprintf( ".tdc-element-ico.tdc-ico-td_gs_testimonial{background-image:url('%s');background-position:center center;background-size:cover}", esc_url_raw($icon) );
        wp_add_inline_style('td_composer_edit', $css);
    }

    public function enqueue_scripts() {

        global $load_in_composer_iframe;

        if ( $load_in_composer_iframe ) {
		
            // Register Styles
            plugin()->scripts->wp_enqueue_style_all( 'public', ['gs-testimonial-public-divi'] );
            
            // Register Scripts
            plugin()->scripts->wp_enqueue_script_all( 'public' );

            wp_add_inline_script( 'gs-testimonial-public', "setInterval(function(){jQuery(document).trigger('gstm:scripts:reprocess');},500);" );

        }

    }

    public function register_block() {
        
        $file = plugin_dir_path( __FILE__ ) . 'includes/gs-testimonial-block.php';

        \td_api_block::add( 'td_gs_testimonial', array(

            'map_in_visual_composer' => false,
            'map_in_td_composer'     => true,
            "name"                   => 'Solid Testimonials',
            "base"                   => 'td_gs_testimonial',
            "class"                  => 'td_gs_testimonial',
            "controls"               => "full",
            "category"               => 'Content',
            'tdc_category'           => 'Blocks',
            'file'                   => $file,

            "params"                 => array(
                array(
					"param_name" => "gs_testimonial_shortcode",
					"type" => "dropdown",
					"value" => $this->get_shortcode_list(),
					"heading" => __( 'Testimonial Shortcode', 'gs-testimonial' )
                ),
            )
        ));

    }

    protected function get_shortcode_list() {

        $shortcodes = get_shortcodes();

        if ( !empty($shortcodes) ) {
            return wp_list_pluck( $shortcodes, 'id', 'shortcode_name' );
        }
        
        return [];

    }

}