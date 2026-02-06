<?php

namespace GSTM;

defined('ABSPATH') || exit;

class Plugin {

    // Instance
    public static $instance = null;

    public $cpt;
    public $shortcode;
    public $template_loader;
    public $widget;
    public $scripts;
    public $sortable;
    public $builder;
    public $integrations;

    public static function get_instance() {
        
        if ( ! self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initiate Autoloader for Class Load
     *
     * @since 1.0.0
     */
    public function __construct() {

        $this->cpt               = new Cpt();
        $this->shortcode         = new Shortcode();
        $this->template_loader   = new Template_Loader();
        $this->scripts           = new Scripts();
        $this->sortable          = new Sortable();
        $this->builder           = new Builder();
        $this->integrations      = new Integrations();

        new Import_Export();
        new Columns();
        new Meta_Fields();
        new Dummy_Data();
        new Hooks();
        
        require_once GSTM_PLUGIN_DIR . 'includes/functions.php';
        require_once GSTM_PLUGIN_DIR . 'includes/asset-generator/gs-load-assets-generator.php';
        require_once GSTM_PLUGIN_DIR . 'includes/gs-common-pages/gs-testimonial-common-pages.php';
    }

    function getoption( $option, $default = '' ) {

        $options = get_option('gstm_shortcode_prefs');

        if (isset($options[$option])) {
            return apply_filters('gs_tm_prefs_' . $option, $options[$option]);
        }

        return apply_filters('gs_tm_prefs_' . $option, $default);
    }
}

function plugin() {
    return Plugin::get_instance();
}
plugin();
