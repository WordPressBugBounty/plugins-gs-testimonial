<?php

namespace GSTM;
/**
 * Protect direct access
 */
if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'Template_Loader' ) ) {

    final class Template_Loader {

        private static $template_path = '';

        private static $theme_path = '';

        private static $child_theme_path = '';
        
        public function __construct() {

            self::$template_path = GSTM_PLUGIN_DIR . 'templates/';

            add_action( 'init', [$this, 'set_theme_template_path'] );

        }

        public function set_theme_template_path() {

            $dir = apply_filters( 'gs_testimonial_templates_folder', 'solid-testimonials' );

            if ( $dir ) {
                $dir = '/' . trailingslashit( ltrim( $dir, '/\\' ) );
                self::$theme_path = get_template_directory() . $dir;

                if ( is_child_theme() ) {
                    self::$child_theme_path = get_stylesheet_directory() . $dir;
                }
            }

        }

        public static function locate_template( $template_file ) {

            // Default path
            $path = self::$template_path;

            // Check requested file exist
            if ( ! file_exists( $path . $template_file ) ) return new \WP_Error( 'gs_testimonial_template_not_found', __( 'Template file not found - GS Plugins', 'gs_testimonial' ) );

            // Override default template if exist from theme
            if ( file_exists( self::$theme_path . $template_file ) ) $path = self::$theme_path;

            if ( is_child_theme() ) {
                // Override default template if exist from child theme
                if ( file_exists( self::$child_theme_path . $template_file ) ) $path = self::$child_theme_path;
            }

            // Return template path, it can be default or overridden by theme
            return $path . $template_file;

        }
    }
}
