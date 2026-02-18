<?php

namespace GSTM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

class GSTM_SEC_Shortcode_Fonts_Loader {
    
	public $fonts_to_enqueue = [];
	
    private $registered_fonts = [];

    private static $_instance = null;

    public static function get_instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new GSTM_SEC_Shortcode_Fonts_Loader();
        }

        return self::$_instance;
        
    }
    
	public function __construct() {

		// We don't need this class in admin side, but in AJAX requests.
        // if ( is_admin() && ! wp_doing_ajax() ) return;
        
        add_action( 'wp_head', [ $this, 'print_fonts_links' ], 7 );
        add_action( 'wp_footer', [ $this, 'wp_footer' ] );
        
	}

	public function wp_footer() {

        $this->print_fonts_links();
        
	}

	public function print_fonts_links() {

		$google_fonts = [
			'google' => [],
			'early' => [],
		];

		
		foreach ( $this->fonts_to_enqueue as $font => $font_sets ) {

			$font_type = GSTM_SEC_Shortcode_Fonts::get_font_type( $font );

			switch ( $font_type ) {

				case GSTM_SEC_Shortcode_Fonts::GOOGLE:
					$google_fonts['google'][$font] = $font_sets;
					break;

				case GSTM_SEC_Shortcode_Fonts::EARLYACCESS:
					$google_fonts['early'][$font] = $font_sets;
					break;

				default:
                    do_action( "elementor/fonts/print_font_links/{$font_type}", $font );
            }

        }
        
		$this->fonts_to_enqueue = [];

        $this->enqueue_google_fonts( $google_fonts );
        
	}

	private function enqueue_google_fonts( $google_fonts = [] ) {
        
		static $google_fonts_index = 0;

		$print_google_fonts = apply_filters( 'elementor/frontend/print_google_fonts', true );

		if ( ! $print_google_fonts ) return;

		// Print used fonts
		if ( ! empty( $google_fonts['google'] ) ) {

			$google_fonts_index++;

			$_google_fonts = [];
			foreach ( $google_fonts['google'] as $font => $sets ) {
				$_google_fonts[] = str_replace( ' ', '+', $font ) . ':' . implode( ',', $sets );
			}

			$fonts_url = sprintf( 'https://fonts.googleapis.com/css?family=%s', implode( rawurlencode( '|' ), $_google_fonts ) );

			$subsets = [
				'ru_RU' => 'cyrillic',
				'bg_BG' => 'cyrillic',
				'he_IL' => 'hebrew',
				'el' => 'greek',
				'vi' => 'vietnamese',
				'uk' => 'cyrillic',
				'cs_CZ' => 'latin-ext',
				'ro_RO' => 'latin-ext',
				'pl_PL' => 'latin-ext',
				'hr_HR' => 'latin-ext',
				'hu_HU' => 'latin-ext',
				'sk_SK' => 'latin-ext',
				'tr_TR' => 'latin-ext',
				'lt_LT' => 'latin-ext',
			];

			$subsets = apply_filters( 'elementor/frontend/google_font_subsets', $subsets );

			$locale = get_locale();

			if ( isset( $subsets[ $locale ] ) ) {
				$fonts_url .= '&subset=' . $subsets[ $locale ];
			}

			wp_enqueue_style( 'google-fonts-' . $google_fonts_index, $fonts_url ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
		}

		// if ( ! empty( $google_fonts['early'] ) ) {
            
        //     foreach ( $google_fonts['early'] as $current_font ) {
		// 		$google_fonts_index++;

		// 		$font_url = sprintf( 'https://fonts.googleapis.com/earlyaccess/%s.css', strtolower( str_replace( ' ', '', $current_font ) ) );

		// 		wp_enqueue_style( 'google-earlyaccess-' . $google_fonts_index, $font_url ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
        //     }
            
		// }

	}

	public function enqueue_font( $font, $weight = 400, $italic = false ) {

		if ( $weight == 'normal' ) $weight = 400;
		if ( $weight == 'bold' ) $weight = 700;

		if ( $italic ) $weight = $weight.'italic';

		if ( in_array($font, $this->registered_fonts) ) return;

		if ( isset( $this->registered_fonts[$font] ) && isset( $this->registered_fonts[$font][$weight] ) ) return;

		if ( !isset($this->fonts_to_enqueue[$font]) ) $this->fonts_to_enqueue[$font] = [];
		if ( !isset($this->registered_fonts[$font]) ) $this->registered_fonts[$font] = [];

		$this->fonts_to_enqueue[$font][$weight] = $weight;
		$this->registered_fonts[$font][$weight] = $weight;
        
	}

}