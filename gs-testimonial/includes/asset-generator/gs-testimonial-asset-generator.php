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
                ' ',
                '--filter-text-color',
                $settings['filter_color']
            );
        }
        if ( !empty( $settings['filter_bg_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' ',
                '--filter-bg-color',
                $settings['filter_bg_color']
            );
        }
        if ( !empty( $settings['filter_border_color'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' ',
                '--filter-border-color',
                $settings['filter_border_color']
            );
        }
        if ( !empty( $settings['filter_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' ',
                '--filter-text-active-color',
                $settings['filter_color_active']
            );
        }
        if ( !empty( $settings['filter_bg_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' ',
                '--filter-bg-active-color',
                $settings['filter_bg_color_active']
            );
        }
        if ( !empty( $settings['filter_border_color_active'] ) ) {
            $this->generateStyle(
                $selector,
                $selector_divi,
                ' ',
                '--filter-border-active-color',
                $settings['filter_border_color_active']
            );
        }
        $gradient_1 = $settings['gs_box_gradient_bg_1'];
        $gradient_2 = $settings['gs_box_gradient_bg_2'];
        $gradient_3 = $settings['gs_box_gradient_bg_3'];
        if ( is_pro_active() ) {
            $gradient_1 = $settings['gs_box_gradient_bg_1'];
            $gradient_2 = $settings['gs_box_gradient_bg_2'];
            $gradient_3 = $settings['gs_box_gradient_bg_3'];
            if ( $gradient_1 ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gstm-box-gradient-bg-color',
                    '--gstm-gradient-color-1',
                    $gradient_1 ?? ''
                );
            }
            if ( $gradient_2 ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gstm-box-gradient-bg-color',
                    '--gstm-gradient-color-2',
                    $gradient_2 ?? ''
                );
            }
            if ( $gradient_3 ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gstm-box-gradient-bg-color',
                    '--gstm-gradient-color-3',
                    $gradient_3 ?? ''
                );
            }
            if ( $settings['linear_range'] ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gstm-box-gradient-bg-color',
                    '--gstm-gradient-linear-range',
                    ( $settings['linear_range'] ? $settings['linear_range'] . 'deg' : '' )
                );
            }
        }
        $setting_title = (array) $settings['typography_title'];
        $setting_testimonial = (array) $settings['typography_testimonial'];
        $setting_reviewer_name = (array) $settings['typography_reviewer_name'];
        $setting_designation = (array) $settings['typography_designation'];
        $setting_read_more = (array) $settings['typography_read_more'];
        $setting_company_name = (array) $settings['typography_company_name'];
        $setting_company_email = (array) $settings['typography_company_email'];
        $setting_company_phone = (array) $settings['typography_company_phone'];
        if ( !empty( $setting_title ) ) {
            if ( !empty( $setting_title['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-color',
                    $setting_title['color']
                );
            }
            if ( !empty( $setting_title['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-hover-color',
                    $setting_title['hoverColor']
                );
            }
            if ( !empty( $setting_title['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-style',
                    $setting_title['style']
                );
            }
            if ( !empty( $setting_title['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-text-decoration',
                    $setting_title['decoration']
                );
            }
            if ( !empty( $setting_title['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-lineHeight',
                    $setting_title['lineHeight']
                );
            }
            if ( !empty( $setting_title['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-letterSpacing',
                    $setting_title['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_title['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-font-family',
                    $setting_title['fontFamily']
                );
            }
            if ( !empty( $setting_title['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-font-weight',
                    $setting_title['weight']
                );
            }
            if ( !empty( $setting_title['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-text-transform',
                    $setting_title['transform']
                );
            }
            if ( !empty( $setting_title['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-title-font-size',
                    $setting_title['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_testimonial ) ) {
            if ( !empty( $setting_testimonial['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-color',
                    $setting_testimonial['color']
                );
            }
            if ( !empty( $setting_testimonial['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-hover-color',
                    $setting_testimonial['hoverColor']
                );
            }
            if ( !empty( $setting_testimonial['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-style',
                    $setting_testimonial['style']
                );
            }
            if ( !empty( $setting_testimonial['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-text-decoration',
                    $setting_testimonial['decoration']
                );
            }
            if ( !empty( $setting_testimonial['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-lineHeight',
                    $setting_testimonial['lineHeight']
                );
            }
            if ( !empty( $setting_testimonial['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-letterSpacing',
                    $setting_testimonial['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_testimonial['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-font-family',
                    $setting_testimonial['fontFamily']
                );
            }
            if ( !empty( $setting_testimonial['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-font-weight',
                    $setting_testimonial['weight']
                );
            }
            if ( !empty( $setting_testimonial['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-text-transform',
                    $setting_testimonial['transform']
                );
            }
            if ( !empty( $setting_testimonial['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-content-font-size',
                    $setting_testimonial['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_company_name ) ) {
            if ( !empty( $setting_company_name['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-color',
                    $setting_company_name['color']
                );
            }
            if ( !empty( $setting_company_name['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-hover-color',
                    $setting_company_name['hoverColor']
                );
            }
            if ( !empty( $setting_company_name['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-style',
                    $setting_company_name['style']
                );
            }
            if ( !empty( $setting_company_name['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-text-decoration',
                    $setting_company_name['decoration']
                );
            }
            if ( !empty( $setting_company_name['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-lineHeight',
                    $setting_company_name['lineHeight']
                );
            }
            if ( !empty( $setting_company_name['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-letterSpacing',
                    $setting_company_name['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_company_name['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-font-family',
                    $setting_company_name['fontFamily']
                );
            }
            if ( !empty( $setting_company_name['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-font-weight',
                    $setting_company_name['weight']
                );
            }
            if ( !empty( $setting_company_name['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-text-transform',
                    $setting_company_name['transform']
                );
            }
            if ( !empty( $setting_company_name['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .gs-tai-client',
                    '--gstm-reviewer-company-name-font-size',
                    $setting_company_name['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_company_email ) ) {
            if ( !empty( $setting_company_email['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-color',
                    $setting_company_email['color']
                );
            }
            if ( !empty( $setting_company_email['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    '',
                    '--gstm-reviewer-company-email-hover-color',
                    $setting_company_email['hoverColor']
                );
            }
            if ( !empty( $setting_company_email['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-style',
                    $setting_company_email['style']
                );
            }
            if ( !empty( $setting_company_email['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-text-decoration',
                    $setting_company_email['decoration']
                );
            }
            if ( !empty( $setting_company_email['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-lineHeight',
                    $setting_company_email['lineHeight']
                );
            }
            if ( !empty( $setting_company_email['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-letterSpacing',
                    $setting_company_email['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_company_email['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-font-family',
                    $setting_company_email['fontFamily']
                );
            }
            if ( !empty( $setting_company_email['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-font-weight',
                    $setting_company_email['weight']
                );
            }
            if ( !empty( $setting_company_email['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-email-text-transform',
                    $setting_company_email['transform']
                );
            }
            if ( !empty( $setting_company_email['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    '  ',
                    '--gstm-reviewer-company-email-font-size',
                    $setting_company_email['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_company_phone ) ) {
            if ( !empty( $setting_company_phone['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    '.gs-tai-contact',
                    '--gstm-reviewer-company-phone-color',
                    $setting_company_phone['color']
                );
            }
            if ( !empty( $setting_company_phone['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-hover-color',
                    $setting_company_phone['hoverColor']
                );
            }
            if ( !empty( $setting_company_phone['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-style',
                    $setting_company_phone['style']
                );
            }
            if ( !empty( $setting_company_phone['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-text-decoration',
                    $setting_company_phone['decoration']
                );
            }
            if ( !empty( $setting_company_phone['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-lineHeight',
                    $setting_company_phone['lineHeight']
                );
            }
            if ( !empty( $setting_company_phone['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-letterSpacing',
                    $setting_company_phone['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_company_phone['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-font-family',
                    $setting_company_phone['fontFamily']
                );
            }
            if ( !empty( $setting_company_phone['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-font-weight',
                    $setting_company_phone['weight']
                );
            }
            if ( !empty( $setting_company_phone['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-text-transform',
                    $setting_company_phone['transform']
                );
            }
            if ( !empty( $setting_company_phone['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' ',
                    '--gstm-reviewer-company-phone-font-size',
                    $setting_company_phone['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_reviewer_name ) ) {
            if ( !empty( $setting_reviewer_name['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-color',
                    $setting_reviewer_name['color']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-color',
                    $setting_reviewer_name['color']
                );
            }
            if ( !empty( $setting_reviewer_name['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-hover-color',
                    $setting_reviewer_name['hoverColor']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-hover-color',
                    $setting_reviewer_name['hoverColor']
                );
            }
            if ( !empty( $setting_reviewer_name['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-style',
                    $setting_reviewer_name['style']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-style',
                    $setting_reviewer_name['style']
                );
            }
            if ( !empty( $setting_reviewer_name['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-text-decoration',
                    $setting_reviewer_name['decoration']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-text-decoration',
                    $setting_reviewer_name['decoration']
                );
            }
            if ( !empty( $setting_reviewer_name['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-lineHeight',
                    $setting_reviewer_name['lineHeight']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-lineHeight',
                    $setting_reviewer_name['lineHeight']
                );
            }
            if ( !empty( $setting_reviewer_name['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-letterSpacing',
                    $setting_reviewer_name['letterSpacing'] . 'px'
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-letterSpacing',
                    $setting_reviewer_name['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_reviewer_name['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-font-family',
                    $setting_reviewer_name['fontFamily']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-font-family',
                    $setting_reviewer_name['fontFamily']
                );
            }
            if ( !empty( $setting_reviewer_name['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-font-weight',
                    $setting_reviewer_name['weight']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-font-weight',
                    $setting_reviewer_name['weight']
                );
            }
            if ( !empty( $setting_reviewer_name['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-text-transform',
                    $setting_reviewer_name['transform']
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-text-transform',
                    $setting_reviewer_name['transform']
                );
            }
            if ( !empty( $setting_reviewer_name['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-box',
                    '--gstm-reviewer-name-font-size',
                    $setting_reviewer_name['size'] . 'px'
                );
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .testimonial-author-info',
                    '--gstm-reviewer-name-font-size',
                    $setting_reviewer_name['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_designation ) ) {
            if ( !empty( $setting_designation['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-color',
                    $setting_designation['color']
                );
            }
            if ( !empty( $setting_designation['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-hover-color',
                    $setting_designation['hoverColor']
                );
            }
            if ( !empty( $setting_designation['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-style',
                    $setting_designation['style']
                );
            }
            if ( !empty( $setting_designation['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-text-decoration',
                    $setting_designation['decoration']
                );
            }
            if ( !empty( $setting_designation['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-lineHeight',
                    $setting_designation['lineHeight']
                );
            }
            if ( !empty( $setting_designation['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-letterSpacing',
                    $setting_designation['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_designation['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-font-family',
                    $setting_designation['fontFamily']
                );
            }
            if ( !empty( $setting_designation['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-font-weight',
                    $setting_designation['weight']
                );
            }
            if ( !empty( $setting_designation['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-text-transform',
                    $setting_designation['transform']
                );
            }
            if ( !empty( $setting_designation['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-desiginfo',
                    '--gstm-reviewer-designation-font-size',
                    $setting_designation['size'] . 'px'
                );
            }
        }
        if ( !empty( $setting_read_more ) ) {
            if ( !empty( $setting_read_more['color'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-color',
                    $setting_read_more['color']
                );
            }
            if ( !empty( $setting_read_more['hoverColor'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-hover-color',
                    $setting_read_more['hoverColor']
                );
            }
            if ( !empty( $setting_read_more['style'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-style',
                    $setting_read_more['style']
                );
            }
            if ( !empty( $setting_read_more['decoration'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-text-decoration',
                    $setting_read_more['decoration']
                );
            }
            if ( !empty( $setting_read_more['lineHeight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-lineHeight',
                    $setting_read_more['lineHeight']
                );
            }
            if ( !empty( $setting_read_more['letterSpacing'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-letterSpacing',
                    $setting_read_more['letterSpacing'] . 'px'
                );
            }
            if ( !empty( $setting_read_more['fontFamily'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-font-family',
                    $setting_read_more['fontFamily']
                );
            }
            if ( !empty( $setting_read_more['weight'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-font-weight',
                    $setting_read_more['weight']
                );
            }
            if ( !empty( $setting_read_more['transform'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-text-transform',
                    $setting_read_more['transform']
                );
            }
            if ( !empty( $setting_read_more['size'] ) ) {
                $this->generateStyle(
                    $selector,
                    $selector_divi,
                    ' .box-content',
                    '--gstm-content-rm-btn-font-size',
                    $setting_read_more['size'] . 'px'
                );
            }
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
        $fonts = $this->get_fonts_from_settings( $settings );
        if ( !empty( $fonts ) ) {
            $this->add_item_in_asset_list( 'fonts', 'google-fonts', $fonts );
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
        $setting_title = (array) $settings['typography_title'];
        $setting_testimonial = (array) $settings['typography_testimonial'];
        plugin()->scripts->wp_enqueue_style_all( 'public', $exclude );
        plugin()->scripts->wp_enqueue_script_all( 'public' );
        $fonts = $this->get_fonts_from_settings( $settings );
        $this->load_google_fonts( $fonts );
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
        $this->load_fonts( $assets['fonts'] );
        wp_enqueue_style( 'gs-testimonial-public' );
        wp_enqueue_script( 'gs-testimonial-public' );
        if ( is_divi_active() ) {
            wp_enqueue_style( 'gs-testimonial-public-divi' );
        }
        $this->enqueue_prefs_custom_css();
    }

    public function get_fonts_from_settings( $settings ) {
        $typography_keys = [
            'typography_title',
            'typography_testimonial',
            'typography_reviewer_name',
            'typography_designation',
            'typography_read_more',
            'typography_company_name',
            'typography_company_email',
            'typography_company_phone'
        ];
        $fonts = [];
        foreach ( $typography_keys as $key ) {
            $setting = (array) $settings[$key];
            if ( !empty( $setting['fontFamily'] ) ) {
                $fonts[] = $setting['fontFamily'];
            }
        }
        return array_unique( $fonts );
    }

    public function load_fonts( $asset_fonts ) {
        if ( !empty( $asset_fonts ) && is_array( $asset_fonts ) ) {
            foreach ( $asset_fonts as $font_type => $fonts ) {
                $encoded_fonts = array_map( function ( $font ) {
                    return str_replace( ' ', '+', $font );
                }, $fonts );
                $google_fonts_url = 'https://fonts.googleapis.com/css2?' . implode( '&', array_map( fn( $f ) => "family={$f}", $encoded_fonts ) ) . '&display=swap';
                wp_enqueue_style(
                    'gs-testimonial-google-fonts-' . md5( $google_fonts_url ),
                    $google_fonts_url,
                    [],
                    null
                );
            }
        }
    }

    public function load_google_fonts( $google_fonts ) {
        $encoded_fonts = [];
        foreach ( $google_fonts as $font ) {
            $encoded_fonts[] = str_replace( ' ', '+', $font );
        }
        $google_fonts_url = 'https://fonts.googleapis.com/css2?' . implode( '&', array_map( fn( $f ) => "family={$f}", $encoded_fonts ) ) . '&display=swap';
        wp_enqueue_style(
            'gs-testimonial-google-fonts-' . md5( $google_fonts_url ),
            $google_fonts_url,
            [],
            null
        );
    }

    public function print_google_fonts() {
        $disable_google_fonts = getoption( 'disable_google_fonts', 'off' );
        if ( $disable_google_fonts === 'on' ) {
            return;
        }
        wp_enqueue_style(
            'google-fonts',
            'https://fonts.googleapis.com/css2?family=Barlow:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400;1,500;1,700&family=Jost:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Montserrat:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Nunito:ital,wght@0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Playfair+Display:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,500;1,600;1,700;1,800;1,900&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Rubik:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Spartan:wght@100;200;300;400;500;600;700;800;900&family=Work+Sans:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Comforter&display=swap',
            [],
            null
        );
    }

}

function gsTestimonialAssetGenerator() {
    return GS_Testimonial_Asset_Generator::getInstance();
}

// Must inilialized for the hooks
gsTestimonialAssetGenerator();