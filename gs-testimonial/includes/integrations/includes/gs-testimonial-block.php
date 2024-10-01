<?php

class td_gs_testimonial extends \td_block {
    
    function render($atts, $content = null) {

        parent::render($atts);

        $atts = shortcode_atts([
            'gs_testimonial_shortcode' => $this->get_default_item()
        ], $atts);

        $content = $this->get_block_css();
        
        $content .= '<div class="wpb_wrapper td_gs_testimonial_block ' . $this->get_wrapper_class() . ' ' . $this->get_block_classes() . '">';
        $content .= do_shortcode( sprintf( '[gs_testimonial id=%d]', $atts['gs_testimonial_shortcode'] ) );
        $content .= '</div>';

        return $content;

    }

    protected function get_default_item() {

        $shortcodes = array_values( (array) GSTM\get_shortcodes() );

        if ( empty($shortcodes) ) return '';

        $shortcode = array_shift( $shortcodes );

        return $shortcode['id'];

    }

}