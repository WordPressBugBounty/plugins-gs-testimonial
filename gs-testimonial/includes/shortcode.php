<?php

namespace GSTM;

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Handles plugin shortcode.
 *
 * @since 1.0.0
 */
class Shortcode {
    /**
     * Class constructor
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_shortcode( 'gs_testimonial', [$this, 'render'] );
    }

    /**
     * Render the shortcode.
     *
     * @since 1.0.0
     *
     * @param  array        $atts Shortcode attributes.
     * 
     * @return string       Shortcode output.
     */
    public function render( $atts ) {
        if ( !is_array( $atts ) ) {
            $atts = [];
        }
        if ( empty( $atts['id'] ) ) {
            return __( 'No shortcode ID found', 'gs-testimonial' );
        }
        $shortcode_id = sanitize_key( $atts['id'] );
        $is_preview = !empty( $atts['preview'] );
        $settings = (array) $this->get_shortcode_settings( $shortcode_id, $is_preview );
        // By default force mode
        $force_asset_load = true;
        if ( !$is_preview ) {
            // For Asset Generator
            $main_post_id = gsTestimonialAssetGenerator()->get_current_page_id();
            $asset_data = gsTestimonialAssetGenerator()->get_assets_data( $main_post_id );
            if ( empty( $asset_data ) ) {
                // Saved assets not found
                // Force load the assets for first time load
                // Generate the assets for later use
                gsTestimonialAssetGenerator()->generate( $main_post_id, $settings );
            } else {
                // Saved assets found
                // Stop force loading the assets
                // Leave the job for Asset Loader
                $force_asset_load = false;
            }
        }
        // Validate Settings
        $shortcode_settings = plugin()->builder->validate_shortcode_settings( $settings );
        $queryArgs = [
            'post_type'      => 'gs_testimonial',
            'order'          => $shortcode_settings['order'],
            'orderby'        => $shortcode_settings['orderby'],
            'posts_per_page' => $shortcode_settings['count'],
            'tax_query'      => [],
            'meta_query'     => [],
        ];
        $categories = (array) $shortcode_settings['categories'];
        $categories = array_filter( $categories );
        if ( !empty( $categories ) ) {
            $queryArgs['tax_query'][] = [
                'taxonomy' => 'gs_testimonial_category',
                'field'    => 'term_id',
                'terms'    => $categories,
            ];
        }
        $gs_t_loop = new \WP_Query($queryArgs);
        require_once ABSPATH . 'wp-admin/includes/template.php';
        ob_start();
        extract( $shortcode_settings );
        if ( !gstm_fs()->can_use_premium_code() ) {
            if ( !in_array( $theme, ['grid_style1', 'grid_style2', 'grid_style3'] ) ) {
                $theme = 'grid_style1';
            }
            if ( !in_array( $view_type, ['grid', 'carousel'] ) ) {
                $view_type = 'grid';
            }
        }
        $is_popup_enabled = true;
        $container_classes = [
            'gs_testimonial_container',
            $theme,
            'image-mode-' . $imageMode,
            'view-type-' . $view_type,
            'image-style-' . $image_style
        ];
        $gs_row_classes = ['gs-roow'];
        $container_classes[] = 'gstm-has-popup';
        if ( $theme == 'grid_style7' ) {
            $container_classes[] = 'gstm-has-video-popup';
        }
        if ( gstm_fs()->can_use_premium_code__premium_only() && $view_type != 'masonry' ) {
            // $is_popup_enabled = true;
            // if ( $is_popup_enabled ) {
            // 	$container_classes[] = 'gstm-has-popup';
            // }
            // if ( $theme == 'grid_style7' ) {
            // 	$container_classes[] = 'gstm-has-video-popup';
            // }
            if ( $theme == 'grid_style4' || $theme == 'grid_style11' ) {
                $container_classes[] = 'gstm-multi-bg-color';
            }
        } else {
            // $is_popup_enabled = true;
        }
        if ( $view_type == 'carousel' ) {
            $container_classes[] = 'gstm-has-carousel-swiper';
            $gs_row_classes[] = 'gs_carousel_swiper';
            if ( $carousel_navs_enabled ) {
                $container_classes[] = 'carousel-has-navs';
                $container_classes[] = 'carousel-navs--' . $carousel_navs_style;
            }
            if ( $carousel_dots_enabled ) {
                $container_classes[] = 'carousel-has-dots';
                $container_classes[] = 'carousel-dots--' . $carousel_dots_style;
                $container_classes[] = 'carousel-dots--' . $carousel_dots_position;
            }
        }
        if ( $view_type === 'masonry' ) {
            $gs_tm_details_contl = '';
        }
        ?>
		
		<div
			id="gs_tstm_area_<?php 
        echo esc_attr( $shortcode_id );
        ?>"
			class="<?php 
        echo esc_attr( join( ' ', $container_classes ) );
        ?>"
			data-carousel-settings='<?php 
        echo json_encode( get_carousel_settings( $shortcode_settings ) );
        ?>'
		>

		<?php 
        if ( 'grid_style1' === $theme ) {
            include Template_Loader::locate_template( 'gs-grid-template-01.php' );
        } else {
            if ( 'grid_style2' === $theme ) {
                include Template_Loader::locate_template( 'gs-grid-template-02.php' );
            } else {
                if ( 'grid_style3' === $theme ) {
                    include Template_Loader::locate_template( 'gs-grid-template-03.php' );
                }
            }
        }
        ?></div>
		
		<?php 
        if ( $this->should_custom_script_render() || $force_asset_load ) {
            gsTestimonialAssetGenerator()->force_enqueue_assets( $settings );
            wp_add_inline_script( 'gs-testimonial-public', "jQuery(document).trigger( 'gstm:scripts:reprocess' );jQuery(function() { jQuery(document).trigger( 'gstm:scripts:reprocess' ) })" );
            $css = gsTestimonialAssetGenerator()->generateCustomCss( $settings, $settings['id'], gstm_fs()->can_use_premium_code() );
            if ( !empty( $css ) ) {
                minimize_css_simple( $css );
                echo "<style>" . $css . "</style>";
            }
            ?>
		
		<?php 
        }
        return ob_get_clean();
    }

    public function get_shortcode_settings( $id, $is_preview = false ) {
        $default_settings = array_merge( [
            'id'         => $id,
            'is_preview' => $is_preview,
        ], plugin()->builder->get_shortcode_default_settings() );
        if ( $is_preview ) {
            return shortcode_atts( $default_settings, get_transient( $id ) );
        }
        $shortcode = plugin()->builder->_get_shortcode( $id );
        return shortcode_atts( $default_settings, (array) $shortcode['shortcode_settings'] );
    }

    public function should_custom_script_render() {
        $render = false;
        // For VC
        if ( !empty( $_GET['vc_editable'] ) ) {
            return true;
        }
        // For Elementor
        if ( !empty( $_GET['action'] ) && $_GET['action'] == 'elementor' || !empty( $_POST['action'] ) && $_POST['action'] == 'elementor_ajax' ) {
            return true;
        }
        // For gutenberg
        if ( !empty( $_GET['context'] ) && $_GET['context'] == 'edit' ) {
            return true;
        }
        // Beaver Builder
        if ( isset( $_GET['fl_builder_ui_iframe'] ) || !empty( $_POST['fl_builder_data'] ) ) {
            return true;
        }
        // Oxygen Builder
        if ( !empty( $_GET['action'] ) && $_GET['action'] == 'oxy_render_oxy-solid-testimonial' ) {
            return true;
        }
        return $render;
    }

}
