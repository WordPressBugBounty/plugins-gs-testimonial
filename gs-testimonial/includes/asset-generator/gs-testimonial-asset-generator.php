<?php

namespace GSTM;

use GSPLUGINS\GS_Asset_Generator_Base;
/**
 * Protect direct access
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}
class GS_Testimonial_Asset_Generator extends GS_Asset_Generator_Base {
    private static $instance = null;

    public static function getInstance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get_assets_key() {
        return 'gs-testimonial-showcase';
    }

    public function generateStyle(
        $selector,
        $selector_divi,
        $targets,
        $prop,
        $value
    ) {
        $selectors = [];
        if ( empty( $targets ) ) {
            return;
        }
        if ( gettype( $targets ) !== 'array' ) {
            $targets = [$targets];
        }
        if ( !empty( $selector_divi ) && (is_divi_active() || is_divi_editor()) ) {
            foreach ( $targets as $target ) {
                $selectors[] = $selector_divi . $target;
            }
        }
        foreach ( $targets as $target ) {
            $selectors[] = $selector . $target;
        }
        echo wp_strip_all_tags( sprintf(
            '%s{%s:%s}',
            join( ',', $selectors ),
            $prop,
            $value
        ) );
    }

    public function generateCustomCss( $settings, $shortCodeId ) {
        ob_start();
        $selector = '#gs_tstm_area_' . $shortCodeId;
        $selector_divi = '#et-boc .et-l div ' . $selector;
        $popupSelector = '.gstm_popup_shortcode_' . $shortCodeId;
        if ( !empty( $settings['item_bg_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                '.carousel_style_1',
                '--single-item--bg-color',
                $settings['item_bg_color']
            );
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .testimonial-box',
                '--single-item--bg-color',
                $settings['item_bg_color']
            );
        }
        if ( !empty( $settings['name_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-client-name',
                '--box-client-name',
                $settings['name_color']
            );
        }
        if ( !empty( $settings['testimonial_title_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-tm-title',
                '--box-tm-title-color',
                $settings['testimonial_title_color']
            );
        }
        if ( !empty( $settings['testimonial_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-content',
                '--box-content',
                $settings['testimonial_color']
            );
        }
        if ( !empty( $settings['read_more_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-content',
                '--read-more-color',
                $settings['read_more_color']
            );
        }
        if ( !empty( $settings['read_more_hover_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-content',
                '--read-more-hover-color',
                $settings['read_more_hover_color']
            );
        }
        if ( !empty( $settings['ratings_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gs-star-rating',
                '--star-rating',
                $settings['ratings_color']
            );
        }
        if ( !empty( $settings['ratings_color'] ) ) {
            $this->generateStyle(
                $popupSelector,
                '',
                ' .gs-star-rating',
                '--star-rating',
                $settings['ratings_color']
            );
        }
        if ( !empty( $settings['designation_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-desiginfo',
                '--box-design-name',
                $settings['designation_color']
            );
        }
        if ( !empty( $settings['company_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .box-companyinfo',
                '--box-company-name',
                $settings['company_color']
            );
        }
        if ( !empty( $settings['info_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gs_testimonial_single',
                '--info-text-color',
                $settings['info_color']
            );
        }
        if ( !empty( $settings['info_icon_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gs_testimonial_single',
                '--info-icon-color',
                $settings['info_icon_color']
            );
        }
        if ( !empty( $settings['gs_slider_dot_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .swiper-pagination-bullet',
                '--dot-bg-color',
                $settings['gs_slider_dot_color']
            );
        }
        if ( !empty( $settings['gs_slider_dot_hover_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .swiper-pagination-bullet',
                '--dot-border-color',
                $settings['gs_slider_dot_hover_color']
            );
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .swiper-pagination-bullet',
                '--dot-bg-color-active',
                $settings['gs_slider_dot_hover_color']
            );
        }
        if ( !empty( $settings['filter_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-text-color',
                $settings['filter_color']
            );
        }
        if ( !empty( $settings['filter_bg_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-bg-color',
                $settings['filter_bg_color']
            );
        }
        if ( !empty( $settings['filter_border_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-border-color',
                $settings['filter_border_color']
            );
        }
        if ( !empty( $settings['filter_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-text-active-color',
                $settings['filter_color_active']
            );
        }
        if ( !empty( $settings['filter_bg_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-bg-active-color',
                $settings['filter_bg_color_active']
            );
        }
        if ( !empty( $settings['filter_border_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' .gstm-filter-cats',
                '--filter-border-active-color',
                $settings['filter_border_color_active']
            );
        }
        return ob_get_clean();
    }

    public function generate_assets_data( array $settings ) {
        if ( empty( $settings ) || !empty( $settings['is_preview'] ) ) {
            return;
        }
        $this->add_item_in_asset_list( 'styles', 'gs-testimonial-public' );
        $this->add_item_in_asset_list( 'scripts', 'gs-testimonial-public' );
        $_swiper_enabled = false;
        $_isotope_enabled = false;
        $_ticker_enabled = false;
        if ( $settings['view_type'] === 'carousel' ) {
            $_swiper_enabled = true;
        }
        if ( $settings['view_type'] === 'filter' || $settings['view_type'] === 'masonry' ) {
            $_isotope_enabled = true;
        }
        if ( $settings['view_type'] === 'ticker' ) {
            $_ticker_enabled = true;
        }
        if ( $settings['theme'] === 'carousel_style_1' ) {
            $_swiper_enabled = true;
        }
        if ( $_swiper_enabled ) {
            $this->add_item_in_asset_list( 'scripts', 'gs-testimonial-public', ['gs-swiper'] );
            $this->add_item_in_asset_list( 'styles', 'gs-testimonial-public', ['gs-swiper'] );
        }
        // Hooked for Pro if availabel
        // do_action( 'gs_testimonial_assets_data_generated', $settings );
        if ( is_divi_active() ) {
            $this->add_item_in_asset_list( 'styles', 'gs-testimonial-public-divi', ['gs-testimonial-public'] );
        }
        $css = $this->get_shortcode_custom_css( $settings );
        if ( !empty( $css ) ) {
            $this->add_item_in_asset_list( 'styles', 'inline', minimize_css_simple( $css ) );
        }
    }

    public function is_builder_preview() {
        return plugin()->integrations->is_builder_preview();
    }

    public function enqueue_builder_preview_assets() {
        plugin()->scripts->wp_enqueue_style_all( 'public', ['gs-testimonial-public-divi'] );
        plugin()->scripts->wp_enqueue_script_all( 'public' );
        $this->enqueue_prefs_custom_css();
    }

    public function maybe_force_enqueue_assets( array $settings ) {
        $exclude = ['gs-testimonial-public-divi'];
        if ( is_divi_active() ) {
            $exclude = [];
        }
        plugin()->scripts->wp_enqueue_style_all( 'public', $exclude );
        plugin()->scripts->wp_enqueue_script_all( 'public' );
        // Shortcode Generated CSS
        $css = $this->get_shortcode_custom_css( $settings );
        $this->wp_add_inline_style( $css );
        // Prefs Custom CSS
        $this->enqueue_prefs_custom_css();
    }

    public function get_shortcode_custom_css( $settings ) {
        return $this->generateCustomCss( $settings, $settings['id'] );
    }

    public function get_prefs_custom_css() {
        $prefs = plugin()->builder->_get_shortcode_pref( false );
        if ( empty( $prefs['gstm_custom_css'] ) ) {
            return '';
        }
        return $prefs['gstm_custom_css'];
    }

    public function enqueue_prefs_custom_css() {
        $this->wp_add_inline_style( $this->get_prefs_custom_css() );
    }

    public function wp_add_inline_style( $css ) {
        if ( !empty( $css ) ) {
            $css = minimize_css_simple( $css );
        }
        if ( !empty( $css ) ) {
            wp_add_inline_style( 'gs-testimonial-public', wp_strip_all_tags( $css ) );
        }
    }

    public function enqueue_plugin_assets( $main_post_id, $assets = [] ) {
        if ( empty( $assets ) || empty( $assets['styles'] ) || empty( $assets['scripts'] ) ) {
            return;
        }
        foreach ( $assets['styles'] as $asset => $data ) {
            if ( $asset == 'inline' ) {
                $this->wp_add_inline_style( $data );
            } else {
                Scripts::add_dependency_styles( $asset, $data );
            }
        }
        foreach ( $assets['scripts'] as $asset => $data ) {
            if ( $asset == 'inline' ) {
                if ( !empty( $data ) ) {
                    wp_add_inline_script( 'gs-testimonial-public', $data );
                }
            } else {
                Scripts::add_dependency_scripts( $asset, $data );
            }
        }
        wp_enqueue_style( 'gs-testimonial-public' );
        wp_enqueue_script( 'gs-testimonial-public' );
        if ( is_divi_active() ) {
            wp_enqueue_style( 'gs-testimonial-public-divi' );
        }
        $this->enqueue_prefs_custom_css();
    }

    // public function print_google_fonts() {
    // 	$disable_google_fonts = getoption( 'disable_google_fonts', 'off' );
    // 	if ( $disable_google_fonts === 'on' ) return;
    // 	wp_enqueue_style( 'google-fonts', 'https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Spartan:wght@100;200;300;400;500;600;700;800;900&family=Work+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Comforter&display=swap', [], null );
    // }
}

function gsTestimonialAssetGenerator() {
    return GS_Testimonial_Asset_Generator::getInstance();
}

// Must inilialized for the hooks
gsTestimonialAssetGenerator();