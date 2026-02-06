<?php
namespace GSTM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

function is_divi_active() {
    if (!defined('ET_BUILDER_PLUGIN_ACTIVE') || !ET_BUILDER_PLUGIN_ACTIVE) return false;
    return et_core_is_builder_used_on_current_request();
}

function is_divi_editor() {
    if ( !empty($_POST['action']) && $_POST['action'] == 'et_pb_process_computed_property' && !empty($_POST['module_type']) && $_POST['module_type'] == 'gs_testimonial_slider' ) return true;
}

function is_pro_active() {
    return gstm_fs()->is_paying_or_trial();
}

function table_name() {
    global $wpdb;
    return $wpdb->prefix . 'gstm_shortcodes';
}

function gs_wp_kses($content) {

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function echo_return($content, $echo = false) {
    if ($echo) {
        echo gs_wp_kses($content);
    } else {
        return $content;
    }
}

function get_query($atts) {

    $args = shortcode_atts([
        'order'                => 'DESC',
        'orderby'            => 'date',
        'posts_per_page'    => -1,
        'paged'             => 1,
        'tax_query'         => [],
    ], $atts);

    $args['post_type'] = 'gs_testimonial';

    return new \WP_Query(apply_filters('gs_testimonial_wp_query_args', $args));
}

function terms_hierarchically(array &$cats, array &$into, $parentId = 0, $exclude_group = []) {

    foreach ($cats as $i => $cat) {
        if (in_array($cat->term_id, $exclude_group)) continue;
        if ($cat->parent == $parentId) {
            $into[$cat->term_id] = $cat;
            unset($cats[$i]);
        }
    }

    foreach ($into as $topCat) {
        $topCat->children = array();
        terms_hierarchically($cats, $topCat->children, $topCat->term_id, $exclude_group);
    }
}

/**
 * Retrives featured image.
 * 
 * @since  1.0.0
 * @param  int    $postId The current post id.
 * @return string         Image url.
 */
function get_featured_image( $postId ) {
    $thumbnailId = get_post_thumbnail_id( $postId );

    if ( $thumbnailId ) {
        $image = wp_get_attachment_image_src( $thumbnailId );
        return isset( $image[0] ) ? $image[0] : '';
    }
}

function get_description( $max_length = 100, $is_popup_enabled = false, $shortcode_id = null ) {

    $description = get_the_content();
    $description = sanitize_text_field( $description );

    // if ( gstm_fs()->can_use_premium_code__premium_only() ) {
        // Reduce the description length
        if ( $max_length > 0 && strlen($description) > $max_length ) {

            $description    = substr( $description, 0, $max_length );
            $gstm_read_more = plugin()->builder->get( 'read_more_text' );
            if ( $is_popup_enabled && $shortcode_id ) {
                $description .= sprintf( '...<a class="gstm-popup--link" data-mfp-src="#gstm_popup_%s_%s" aria-label="Testimonial Details Link" href="#">%s</a>', get_the_ID(), $shortcode_id, esc_html($gstm_read_more) );
            }
        }
    // }

    return $description;
}

// function get_description_with_html( $is_popup_enabled = false, $shortcode_id = null ) {

//     $description = get_the_content();
//     $description = wp_kses_post( $description );

//     return $description;
// }

function gstm_read_more( $is_popup_enabled = false, $shortcode_id = null ) {

    $gstm_read_more = plugin()->builder->get( 'read_more_text' );

    if ( $is_popup_enabled && $shortcode_id ) {
        return sprintf( '<a class="gstm-popup--link" data-mfp-src="#gstm_popup_%s_%s" aria-label="Testimonial Details Link" href="#">%s</a>', get_the_ID(), $shortcode_id, esc_html($gstm_read_more) );
    }
}

function wp_star_rating( $args = array() ) {
    $defaults    = array(
        'rating' => 0,
        'type'   => 'rating',
        'number' => 0,
        'echo'   => true,
    );
    $parsed_args = wp_parse_args( $args, $defaults );

    // Non-English decimal places when the $rating is coming from a string.
    $rating = (float) str_replace( ',', '.', $parsed_args['rating'] );

    // Convert percentage to star rating, 0..5 in .5 increments.
    if ( 'percent' === $parsed_args['type'] ) {
        $rating = round( $rating / 10, 0 ) / 2;
    }

    // Calculate the number of each type of star needed.
    $full_stars  = floor( $rating );
    $half_stars  = ceil( $rating - $full_stars );
    $empty_stars = 5 - $full_stars - $half_stars;

    if ( $parsed_args['number'] ) {
        /* translators: Hidden accessibility text. 1: The rating, 2: The number of ratings. */
        $format = _n( '%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number'] );
        $title  = sprintf( $format, number_format_i18n( $rating, 1 ), number_format_i18n( $parsed_args['number'] ) );
    } else {
        /* translators: Hidden accessibility text. %s: The rating. */
        $title = sprintf( __( '%s rating' ), number_format_i18n( $rating, 1 ) );
    }

    $output  = '<div class="gs-star-rating">';
    $output .= '<span class="screen-reader-text">' . $title . '</span>';
    $output .= str_repeat( '<i class="gs-star fas fa-star"></i>', $full_stars );
    $output .= str_repeat( '<i class="gs-star fas fa-star-half-alt"></i>', $half_stars );
    $output .= str_repeat( '<i class="gs-star far fa-star"></i>', $empty_stars );
    $output .= '</div>';

    if ( $parsed_args['echo'] ) {
        echo $output;
    }

    return $output;
}

function is_preview() {
    return isset( $_REQUEST['gstm_shortcode_preview'] ) && ! empty( $_REQUEST['gstm_shortcode_preview'] );
}

/**
 * Retrives option value.
 * 
 * @since  1.0.0
 * @param  string $option  The option name.
 * @param  string $section Options section name.
 * @return mixed           Retrived option value or the default value.
 */
function getOption( $option, $section, $default = '' ) {
    $options = get_option( $section );
    return isset( $options[ $option ] ) ? $options[$option] : $default;
}

/**
 * Retrieve Carousel Settings
 *
 * @since 1.0.0
 *
 * @param null
 *
 * @return void
 */
function get_carousel_settings( $settings ) {
    
    $getDatas                           = [];
    $getDatas['speed']                  = intval($settings['speed']);
    $getDatas['isAutoplay']             = wp_validate_boolean($settings['isAutoplay']);
    $getDatas['autoplay_delay']         = intval($settings['autoplay_delay']);
    $getDatas['reverseDirection']       = false;
    $getDatas['pause_on_hover']         = wp_validate_boolean($settings['pause_on_hover']);
    $getDatas['navs']                   = wp_validate_boolean($settings['carousel_navs_enabled']);
    $getDatas['dots']                   = wp_validate_boolean($settings['carousel_dots_enabled']);
    $getDatas['dynamicBullets']         = wp_validate_boolean($settings['dynamic_dots_enabled']);
    $getDatas['desktop_columns']        = intval($settings['desktop_columns']);
    $getDatas['tablet_columns']         = intval($settings['tablet_columns']);
    $getDatas['mobile_columns']         = intval($settings['columns_mobile']);
    $getDatas['columns_small_mobile']   = intval($settings['columns_small_mobile']);
    $getDatas['carousel_navs_style']    = sanitize_key($settings['carousel_navs_style']);
    $getDatas['carousel_dots_style']    = sanitize_key($settings['carousel_dots_style']);
    $getDatas['carousel_dots_position'] = sanitize_key($settings['carousel_dots_position']);

    if ( is_pro_active() ) {
        $getDatas['reverseDirection'] = wp_validate_boolean($settings['reverse_direction']);

        if( $settings['allow_html'] ) {
            $getDatas['gs_tm_line_contl'] = intval($settings['gs_tm_line_contl']);
        }
    }

    return $getDatas;        
}

function minimize_css_simple($css) {
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

function gs_get_terms( $term_name, $idsOnly = false ) {

    $_terms = get_terms( $term_name, [
        'hide_empty' => false,
    ]);

    if ( empty($_terms) ) return [];
    
    if ( $idsOnly ) return wp_list_pluck( $_terms, 'term_id' );

    $terms = [];

    foreach ( $_terms as $term ) {
        $terms[] = [
            'label' => $term->name,
            'value' => $term->term_id
        ];
    }

    return $terms;

}

function get_charset() {
    global $wpdb;
    return $wpdb->get_charset_collate();
}

function get_terms_slugs( $term_name = 'gs_testimonial_category', $separator = ' ' ) {

    global $post;

    $terms = get_the_terms( $post->ID, $term_name );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        $terms = implode( $separator, wp_list_pluck( $terms, 'slug' ) );
        return $terms;
    }

}

function isPreview() {
    return isset( $_REQUEST['gstm_shortcode_preview'] ) && ! empty( $_REQUEST['gstm_shortcode_preview'] );
}

function get_shortcodes() {
    return plugin()->builder->fetch_shortcodes(null, false, true);
}


function gstm_get_visibility_class( $key, $card_visibility_settings ) {
    
    if ( empty( $card_visibility_settings[ $key ] ) ) {
        return '';
    }

    $map = $card_visibility_settings[ $key ];

    $classes = [];

    $classes[] = ! empty( $map['desktop'] ) ? 'gstm-show-desktop' : 'gstm-hide-desktop';
    $classes[] = ! empty( $map['tablet'] ) ? 'gstm-show-tablet' : 'gstm-hide-tablet';
    $classes[] = ! empty( $map['mobile_landscape'] ) ? 'gstm-show-mobile-landscape' : 'gstm-hide-mobile-landscape';
    $classes[] = ! empty( $map['mobile'] ) ? 'gstm-show-mobile' : 'gstm-hide-mobile';

    return implode( ' ', $classes );
}