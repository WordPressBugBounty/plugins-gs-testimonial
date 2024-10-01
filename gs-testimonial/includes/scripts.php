<?php

namespace GSTM;

// if direct access than exit the file.
defined( 'ABSPATH' ) || exit;
/**
 * Responsible for enqueuing plugins script.
 *
 * @since 1.2.11
 */
class Scripts {
    /**
     * Contains styles handlers and paths.
     *
     * @since 1.0.0
     */
    public $styles = [];

    /**
     * Contains scripts handlers and paths.
     *
     * @since 1.0.0
     */
    public $scripts = [];

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->add_assets();
        add_action( 'wp_enqueue_scripts', [$this, 'enqueue_frontend_scripts'], 9999 );
        add_action( 'admin_enqueue_scripts', [$this, 'admin_enqueue_scripts'], 9999 );
        add_action( 'admin_head', [$this, 'print_plugin_icon_css'] );
        return $this;
    }

    /**
     * Adding assets on the $this->styles[] array.
     *
     * @since 1.0.0
     */
    public function add_assets() {
        // Styles
        $this->add_style(
            'gs-font-awesome-5',
            GSTM_PLUGIN_URI . '/assets/libs/font-awesome/css/font-awesome.min.css',
            [],
            GSTM_VERSION
        );
        $this->add_style(
            'gs-swiper',
            GSTM_PLUGIN_URI . '/assets/libs/swiper-js/swiper.min.css',
            [],
            GSTM_VERSION
        );
        $this->add_style(
            'gs-bootstrap-grid',
            GSTM_PLUGIN_URI . '/assets/libs/bootstrap-grid/bootstrap-grid.min.css',
            [],
            GSTM_VERSION
        );
        $this->add_style(
            'gs-magnific-popup',
            GSTM_PLUGIN_URI . '/assets/libs/magnific-popup/magnific-popup.min.css',
            [],
            GSTM_VERSION
        );
        $this->add_style(
            'gs-testimonial-public',
            GSTM_PLUGIN_URI . '/assets/css/public.min.css',
            ['gs-font-awesome-5', 'gs-bootstrap-grid'],
            GSTM_VERSION
        );
        // Scripts
        $this->add_script(
            'gs-swiper',
            GSTM_PLUGIN_URI . '/assets/libs/swiper-js/swiper.min.js',
            ['jquery'],
            GSTM_VERSION,
            true
        );
        $this->add_script(
            'gs-magnific-popup',
            GSTM_PLUGIN_URI . '/assets/libs/magnific-popup/magnific-popup.min.js',
            ['jquery'],
            GSTM_VERSION,
            true
        );
        $this->add_script(
            'gs-isotope',
            GSTM_PLUGIN_URI . '/assets/libs/isotope/isotope.min.js',
            ['jquery'],
            GSTM_VERSION,
            true
        );
        $this->add_script(
            'gs-ticker',
            GSTM_PLUGIN_URI . '/assets/libs/carousel-ticker/carouselTicker.min.js',
            ['jquery'],
            GSTM_VERSION,
            true
        );
        $this->add_script(
            'gs-testimonial-public',
            GSTM_PLUGIN_URI . '/assets/js/public.min.js',
            ['jquery'],
            GSTM_VERSION,
            true
        );
    }

    /**
     * Store styles on the $this->styles[] queue.
     * 
     * @since 1.0.0
     * 
     * @param  string  $handler Name of the stylesheet.
     * @param  string  $src     Full URL of the stylesheet
     * @param  array   $deps    Array of registered stylesheet handles this stylesheet depends on.
     * @param  boolean $ver     Specifying stylesheet version number
     * @param  string  $media   The media for which this stylesheet has been defined.
     * @return void
     */
    public function add_style(
        $handler,
        $src,
        $deps = [],
        $ver = false,
        $media = 'all'
    ) {
        $this->styles[$handler] = [
            'src'   => $src,
            'deps'  => $deps,
            'ver'   => $ver,
            'media' => $media,
        ];
    }

    /**
     * Store scripts on the $this->scripts[] queue.
     * 
     * @since 1.0.0
     * 
     * @param  string  $handler  Name of the script.
     * @param  string  $src      Full URL of the script
     * @param  array   $deps      Array of registered script handles this script depends on.
     * @param  boolean $ver       Specifying script version number
     * @param  boolean $in_footer Whether to enqueue the script before </body> instead of in the <head>
     * @return void
     */
    public function add_script(
        $handler,
        $src,
        $deps = [],
        $ver = false,
        $in_footer = false
    ) {
        $this->scripts[$handler] = [
            'src'       => $src,
            'deps'      => $deps,
            'ver'       => $ver,
            'in_footer' => $in_footer,
        ];
    }

    /**
     * Return style if exits on the $this->styles[] list.
     * 
     * @since 3.0.9
     * @param string $handler The style name.
     */
    public function get_style( $handler ) {
        if ( empty( $style = $this->styles[$handler] ) ) {
            return false;
        }
        return $style;
    }

    /**
     * Return the script if exits on the $this->scripts[] list.
     * 
     * @since 3.0.9
     * @param string $handler The script name.
     */
    public function get_script( $handler ) {
        if ( empty( $script = $this->scripts[$handler] ) ) {
            return false;
        }
        return $script;
    }

    /**
     * A wrapper for registering styles.
     * 
     * @since 1.0.0
     * 
     * @param  string       $handler The name of the stylesheet.
     * @return boolean|void          If it gets the stylesheet then register it or false.
     */
    public function wp_register_style( $handler ) {
        $style = $this->get_style( $handler );
        if ( !$style ) {
            return;
        }
        $deps = (array) apply_filters( $handler . '--style', $style['deps'] );
        wp_register_style(
            $handler,
            $style['src'],
            $deps,
            $style['ver'],
            $style['media']
        );
    }

    /**
     * A wrapper for registering scripts.
     * 
     * @since 1.0.0
     * 
     * @param  string       $handler The name of the script.
     * 
     * @return boolean|void          If it gets the script then register it or false.
     */
    public function wp_register_script( $handler ) {
        $script = $this->get_script( $handler );
        if ( !$script ) {
            return;
        }
        $deps = (array) apply_filters( $handler . '--script', $script['deps'] );
        wp_register_script(
            $handler,
            $script['src'],
            $deps,
            $script['ver'],
            $script['in_footer']
        );
    }

    /**
     * Returns all publicly enqueuable stylesheets.
     * 
     * @since  1.0.0
     * 
     * @return array List of publicly enqueuable stylesheets.
     */
    public function _get_public_style_all() {
        $styles = [
            'gs-swiper',
            'gs-bootstrap-grid',
            'gs-font-awesome-5',
            'gs-testimonial-public'
        ];
        return (array) apply_filters( 'gs_testimonial_public_style_all', $styles );
    }

    /**
     * Returns all publicly enqueuable scripts.
     * 
     * @since  1.0.0
     * 
     * @return array List of publicly enqueuable scripts.
     */
    public function _get_public_script_all() {
        $scripts = ['gs-swiper', 'gs-testimonial-public'];
        return (array) apply_filters( 'gs_testimonial_public_script_all', $scripts );
    }

    public function _get_assets_all( $asset_type, $group, $excludes = [] ) {
        if ( !in_array( $asset_type, ['style', 'script'] ) || !in_array( $group, ['public'] ) ) {
            return;
        }
        $get_assets = sprintf( '_get_%s_%s_all', $group, $asset_type );
        $assets = $this->{$get_assets}();
        if ( !empty( $excludes ) ) {
            $assets = array_diff( $assets, $excludes );
        }
        return (array) apply_filters( sprintf( 'gs_testimonial_%s__%s_all', $group, $asset_type ), $assets );
    }

    public function _wp_load_assets_all(
        $function,
        $asset_type,
        $group,
        $excludes = []
    ) {
        if ( !in_array( $function, ['enqueue', 'register'] ) || !in_array( $asset_type, ['style', 'script'] ) ) {
            return;
        }
        $assets = $this->_get_assets_all( $asset_type, $group, $excludes );
        $function = sprintf( 'wp_%s_%s', $function, $asset_type );
        foreach ( $assets as $asset ) {
            $this->{$function}( $asset );
        }
    }

    public function wp_register_style_all( $group, $excludes = [] ) {
        $this->_wp_load_assets_all(
            'register',
            'style',
            $group,
            $excludes
        );
    }

    public function wp_enqueue_style_all( $group, $excludes = [] ) {
        $this->_wp_load_assets_all(
            'enqueue',
            'style',
            $group,
            $excludes
        );
    }

    public function wp_register_script_all( $group, $excludes = [] ) {
        $this->_wp_load_assets_all(
            'register',
            'script',
            $group,
            $excludes
        );
    }

    public function wp_enqueue_script_all( $group, $excludes = [] ) {
        $this->_wp_load_assets_all(
            'enqueue',
            'script',
            $group,
            $excludes
        );
    }

    // Use to direct enqueue
    public function wp_enqueue_style( $handler ) {
        $style = $this->get_style( $handler );
        if ( !$style ) {
            return;
        }
        $deps = (array) apply_filters( $handler . '--style-enqueue', $style['deps'] );
        wp_enqueue_style(
            $handler,
            $style['src'],
            $deps,
            $style['ver'],
            $style['media']
        );
    }

    public function wp_enqueue_script( $handler ) {
        $script = $this->get_script( $handler );
        if ( !$script ) {
            return;
        }
        $deps = (array) apply_filters( $handler . '--script-enqueue', $script['deps'] );
        wp_enqueue_script(
            $handler,
            $script['src'],
            $deps,
            $script['ver'],
            $script['in_footer']
        );
    }

    public function print_plugin_icon_css() {
        echo '<style>#adminmenu .toplevel_page_gs-testimonial .wp-menu-image img,#adminmenu .menu-icon-gs_testimonial .wp-menu-image img{padding-top:7px;width:20px;opacity:.8;height:auto}#menu-posts-gs_testimonial li{clear:both}#menu-posts-gs_testimonial li a[href^="edit.php?post_type=gs_testimonial&page=gs-testimonial-plugins-help"]:after,#menu-posts-gs_testimonial li:nth-last-child(3) a:after{border-bottom:1px solid hsla(0,0%,100%,.2);display:block;float:left;margin:13px -15px 8px;content:"";width:calc(100% + 26px)}</style>';
    }

    /**
     * Enqueue assets for the plugin based on all dep checks and only 
     * if current page contains the shortcode.
     * 
     * @since  3.0.9
     * 
     * @return void
     */
    public function enqueue_frontend_scripts() {
        // Register Styles
        $this->wp_register_style_all( 'public' );
        // Register Scripts
        $this->wp_register_script_all( 'public' );
        // Enqueue for Single & Archive Pages
        if ( is_singular( 'gs_testimonial' ) || is_post_type_archive( 'gs_testimonial' ) || is_tax( ['gs_testimonial_category'] ) ) {
            wp_enqueue_style( 'gs-testimonial-public' );
        }
        // Maybe enqueue assets
        gsTestimonialAssetGenerator()->enqueue( gsTestimonialAssetGenerator()->get_current_page_id() );
        do_action( 'gs_testimonial_assets_loaded' );
    }

    public function admin_enqueue_scripts( $hook ) {
        global $post;
        $load_script = false;
        if ( $hook == 'gs_testimonial_page_sort_gs_testimonial' ) {
            $load_script = true;
        }
        if ( $hook == 'gs_testimonial_page_sort_group_gs_testimonial' ) {
            $load_script = true;
        }
        if ( $hook == 'gs_testimonial_page_gst-shortcodes' ) {
            $load_script = true;
        }
        // Allow scripts loading in new gs_testimonial member page
        if ( $hook == 'post-new.php' && $post->post_type == 'gs_testimonial' ) {
            $load_script = true;
        }
        // Allow scripts loading in gs_testimonial member edit page
        if ( $hook == 'post.php' && $post->post_type == 'gs_testimonial' ) {
            $load_script = true;
        }
        // Allow scripts loading in gs_testimonial member edit page
        if ( $hook == 'edit-tags.php' && $_GET['taxonomy'] == 'gs_testimonial_category' ) {
            $load_script = true;
        }
        if ( $hook == 'term.php' && $_GET['taxonomy'] == 'gs_testimonial_category' ) {
            $load_script = true;
        }
        // Abort load script if not allowed
        if ( !$load_script ) {
            return;
        }
        wp_enqueue_script(
            'gstm-star-rating',
            GSTM_PLUGIN_URI . '/assets/rateit-js/jquery.rateit.min.js',
            array('jquery'),
            GSTM_VERSION,
            true
        );
        wp_enqueue_style(
            'gstm-star-rating',
            GSTM_PLUGIN_URI . '/assets/rateit-js/rateit.css',
            [],
            GSTM_VERSION
        );
        wp_enqueue_style(
            'gs-font-awesome-5',
            GSTM_PLUGIN_URI . '/assets/libs/font-awesome/css/font-awesome.min.css',
            [],
            GSTM_VERSION
        );
        wp_enqueue_style(
            'gstm-metabox',
            GSTM_PLUGIN_URI . '/assets/css/gstm-metabox.css',
            [],
            GSTM_VERSION
        );
        wp_enqueue_style(
            'gstm-form',
            GSTM_PLUGIN_URI . '/assets/form/index.css',
            [],
            GSTM_VERSION
        );
    }

    public static function add_dependency_scripts( $handle, $scripts ) {
        add_action( 'wp_footer', function () use($handle, $scripts) {
            global $wp_scripts;
            if ( empty( $scripts ) || empty( $handle ) ) {
                return;
            }
            if ( !isset( $wp_scripts->registered[$handle] ) ) {
                return;
            }
            $wp_scripts->registered[$handle]->deps = array_unique( array_merge( $wp_scripts->registered[$handle]->deps, $scripts ) );
        } );
    }

    public static function add_dependency_styles( $handle, $styles ) {
        global $wp_styles;
        if ( empty( $styles ) || empty( $handle ) ) {
            return;
        }
        if ( !isset( $wp_styles->registered[$handle] ) ) {
            return;
        }
        $wp_styles->registered[$handle]->deps = array_unique( array_merge( $wp_styles->registered[$handle]->deps, $styles ) );
    }

}
