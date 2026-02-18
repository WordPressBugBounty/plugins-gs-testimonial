<?php

namespace GSTM;

/**
 * Protect direct access
 */
if (!defined('ABSPATH')) exit;

function is_divi_active()
{
    if (!defined('ET_BUILDER_PLUGIN_ACTIVE') || !ET_BUILDER_PLUGIN_ACTIVE) return false;
    return et_core_is_builder_used_on_current_request();
}

function is_divi_editor()
{
    if (!empty($_POST['action']) && $_POST['action'] == 'et_pb_process_computed_property' && !empty($_POST['module_type']) && $_POST['module_type'] == 'gs_testimonial_slider') return true;
}

function is_pro_active()
{
    return gstm_fs()->is_paying_or_trial();
}

function table_name()
{
    global $wpdb;
    return $wpdb->prefix . 'gstm_shortcodes';
}

function gs_wp_kses($content)
{

    $allowed_tags = wp_kses_allowed_html('post');

    $input_common_atts = ['class' => true, 'id' => true, 'style' => true, 'novalidate' => true, 'name' => true, 'width' => true, 'height' => true, 'data' => true, 'title' => true, 'placeholder' => true, 'value' => true];

    $allowed_tags = array_merge_recursive($allowed_tags, [
        'select' => $input_common_atts,
        'input' => array_merge($input_common_atts, ['type' => true, 'checked' => true]),
        'option' => ['class' => true, 'id' => true, 'selected' => true, 'data' => true, 'value' => true]
    ]);

    return wp_kses(stripslashes_deep($content), $allowed_tags);
}

function echo_return($content, $echo = false)
{
    if ($echo) {
        echo gs_wp_kses($content);
    } else {
        return $content;
    }
}

function get_query($atts)
{

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

function terms_hierarchically(array &$cats, array &$into, $parentId = 0, $exclude_group = [])
{

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
function get_featured_image($postId)
{
    $thumbnailId = get_post_thumbnail_id($postId);

    if ($thumbnailId) {
        $image = wp_get_attachment_image_src($thumbnailId);
        return isset($image[0]) ? $image[0] : '';
    }
}

function get_description($max_length = 100, $is_popup_enabled = false, $shortcode_id = null)
{

    $description = get_the_content();
    $description = sanitize_text_field($description);

    // if ( gstm_fs()->can_use_premium_code__premium_only() ) {
    // Reduce the description length
    if ($max_length > 0 && strlen($description) > $max_length) {

        $description    = substr($description, 0, $max_length);
        $gstm_read_more = plugin()->builder->get('read_more_text');
        if ($is_popup_enabled && $shortcode_id) {
            $description .= sprintf('...<a class="gstm-popup--link" data-mfp-src="#gstm_popup_%s_%s" aria-label="Testimonial Details Link" href="#">%s</a>', get_the_ID(), $shortcode_id, esc_html($gstm_read_more));
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

function gstm_read_more($is_popup_enabled = false, $shortcode_id = null)
{

    $gstm_read_more = plugin()->builder->get('read_more_text');

    if ($is_popup_enabled && $shortcode_id) {
        return sprintf('<a class="gstm-popup--link" data-mfp-src="#gstm_popup_%s_%s" aria-label="Testimonial Details Link" href="#">%s</a>', get_the_ID(), $shortcode_id, esc_html($gstm_read_more));
    }
}

function wp_star_rating($args = array())
{
    $defaults    = array(
        'rating' => 0,
        'type'   => 'rating',
        'number' => 0,
        'echo'   => true,
    );
    $parsed_args = wp_parse_args($args, $defaults);

    // Non-English decimal places when the $rating is coming from a string.
    $rating = (float) str_replace(',', '.', $parsed_args['rating']);

    // Convert percentage to star rating, 0..5 in .5 increments.
    if ('percent' === $parsed_args['type']) {
        $rating = round($rating / 10, 0) / 2;
    }

    // Calculate the number of each type of star needed.
    $full_stars  = floor($rating);
    $half_stars  = ceil($rating - $full_stars);
    $empty_stars = 5 - $full_stars - $half_stars;

    if ($parsed_args['number']) {
        /* translators: Hidden accessibility text. 1: The rating, 2: The number of ratings. */
        $format = _n('%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number']);
        $title  = sprintf($format, number_format_i18n($rating, 1), number_format_i18n($parsed_args['number']));
    } else {
        /* translators: Hidden accessibility text. %s: The rating. */
        $title = sprintf(__('%s rating'), number_format_i18n($rating, 1));
    }

    $output  = '<div class="gs-star-rating">';
    $output .= '<span class="screen-reader-text">' . $title . '</span>';
    $output .= str_repeat('<i class="gs-star fas fa-star"></i>', $full_stars);
    $output .= str_repeat('<i class="gs-star fas fa-star-half-alt"></i>', $half_stars);
    $output .= str_repeat('<i class="gs-star far fa-star"></i>', $empty_stars);
    $output .= '</div>';

    if ($parsed_args['echo']) {
        echo $output;
    }

    return $output;
}

function is_preview()
{
    return isset($_REQUEST['gstm_shortcode_preview']) && ! empty($_REQUEST['gstm_shortcode_preview']);
}

/**
 * Retrives option value.
 * 
 * @since  1.0.0
 * @param  string $option  The option name.
 * @param  string $section Options section name.
 * @return mixed           Retrived option value or the default value.
 */
function getOption($option, $section, $default = '')
{
    $options = get_option($section);
    return isset($options[$option]) ? $options[$option] : $default;
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
function get_carousel_settings($settings)
{

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

    if (is_pro_active()) {
        $getDatas['reverseDirection'] = wp_validate_boolean($settings['reverse_direction']);

        if ($settings['allow_html']) {
            $getDatas['gs_tm_line_contl'] = intval($settings['gs_tm_line_contl']);
        }
    }

    return $getDatas;
}

function minimize_css_simple($css)
{
    // https://datayze.com/howto/minify-css-with-php
    $css = preg_replace('/\/\*((?!\*\/).)*\*\//', '', $css); // negative look ahead
    $css = preg_replace('/\s{2,}/', ' ', $css);
    $css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
    $css = preg_replace('/;}/', '}', $css);
    return $css;
}

function gs_get_terms($term_name, $idsOnly = false)
{

    $_terms = get_terms($term_name, [
        'hide_empty' => false,
    ]);

    if (empty($_terms)) return [];

    if ($idsOnly) return wp_list_pluck($_terms, 'term_id');

    $terms = [];

    foreach ($_terms as $term) {
        $terms[] = [
            'label' => $term->name,
            'value' => $term->term_id
        ];
    }

    return $terms;
}

function get_charset()
{
    global $wpdb;
    return $wpdb->get_charset_collate();
}

function get_terms_slugs($term_name = 'gs_testimonial_category', $separator = ' ')
{

    global $post;

    $terms = get_the_terms($post->ID, $term_name);

    if (! empty($terms) && ! is_wp_error($terms)) {
        $terms = implode($separator, wp_list_pluck($terms, 'slug'));
        return $terms;
    }
}

function isPreview()
{
    return isset($_REQUEST['gstm_shortcode_preview']) && ! empty($_REQUEST['gstm_shortcode_preview']);
}

function get_shortcodes()
{
    return plugin()->builder->fetch_shortcodes(null, false, true);
}


function gstm_get_fonts_css($data, $device = '')
{

    if (empty($data)) return '';

    if (! empty($device)) $device = '_' . $device;

    $css = '';

    if (!empty($data['family' . $device])) $css .= "font-family: {$data['family' .$device]};";
    if (!empty($data['size' . $device])) $css .= "font-size: {$data['size' .$device]}px;";
    if (!empty($data['color' . $device])) $css .= "color: {$data['color' .$device]};";
    if (!empty($data['weight' . $device])) $css .= "font-weight: {$data['weight' .$device]};";
    if (!empty($data['transform' . $device])) $css .= "text-transform: {$data['transform' .$device]};";
    if (!empty($data['style' . $device])) $css .= "font-style: {$data['style' .$device]};";
    if (!empty($data['decoration' . $device])) $css .= "text-decoration: {$data['decoration' .$device]};";
    if (!empty($data['lineHeight' . $device])) $css .= "line-height: {$data['lineHeight' .$device]};";
    if (!empty($data['letterSpacing' . $device])) $css .= "letter-spacing: {$data['letterSpacing' .$device]}px;";

    return $css;
}


function gstm_get_dimension_css($data, $device)
{

    if (empty($data)) return '';

    $data = shortcode_atts(array(
        'top' => 120,
        'right' => 0,
        'bottom' => 120,
        'left' => 0,
        'top_tablet' => null,
        'right_tablet' => null,
        'bottom_tablet' => null,
        'left_tablet' => null,
        'top_mobile' => null,
        'right_mobile' => null,
        'bottom_mobile' => null,
        'left_mobile' => null,
        'unit' => 'px',
    ), $data);

    $unit = $data['unit'];

    $top = (int) $data['top'];
    $right = (int) $data['right'];
    $bottom = (int) $data['bottom'];
    $left = (int) $data['left'];

    if ($device == 'tablet') {
        if (is_numeric($data['top_tablet'])) $top = (int) $data['top_tablet'];
        if (is_numeric($data['right_tablet'])) $right = (int) $data['right_tablet'];
        if (is_numeric($data['bottom_tablet'])) $bottom = (int) $data['bottom_tablet'];
        if (is_numeric($data['left_tablet'])) $left = (int) $data['left_tablet'];
    }

    if ($device == 'mobile') {
        if (is_numeric($data['top_tablet'])) $top = (int) $data['top_tablet'];
        if (is_numeric($data['right_tablet'])) $right = (int) $data['right_tablet'];
        if (is_numeric($data['bottom_tablet'])) $bottom = (int) $data['bottom_tablet'];
        if (is_numeric($data['left_tablet'])) $left = (int) $data['left_tablet'];

        if (is_numeric($data['top_mobile'])) $top = (int) $data['top_mobile'];
        if (is_numeric($data['right_mobile'])) $right = (int) $data['right_mobile'];
        if (is_numeric($data['bottom_mobile'])) $bottom = (int) $data['bottom_mobile'];
        if (is_numeric($data['left_mobile'])) $left = (int) $data['left_mobile'];
    }

    return "{$top}{$unit} {$right}{$unit} {$bottom}{$unit} {$left}{$unit}";
}


function gstm_get_color_css($data)
{

    $data = shortcode_atts(array(
        'type' => 'classic',
        'color1' => '',
        'color1_loc' => 0,
        'color2' => '',
        'color2_loc' => 100,
        'angle' => 0,
    ), $data);

    extract($data);

    if (empty($color1)) return '';

    if ($type == 'classic' || empty($color2)) return "background-color: {$color1};";

    return "background: $color1; background-image:-webkit-linear-gradient({$angle}deg, $color1 {$color1_loc}%, $color2 {$color2_loc}%); background-image: linear-gradient({$angle}deg, $color1 {$color1_loc}%, $color2 {$color2_loc}%);";
}

function gstm_get_style_for_main_div($data, $device = '')
{

    $css = '';

    if (!empty($data['section_margin'])) {
        $section_margin = gstm_get_dimension_css($data['section_margin'], $device);
        $css .= "margin: {$section_margin};";
    }

    if (!empty($data['section_padding'])) {
        $section_padding = gstm_get_dimension_css($data['section_padding'], $device);
        $css .= "padding: {$section_padding};";
    }

    if (empty($device) && !empty($data['section_bg_color']) && !empty($data['section_bg_color']['type'])) {

        $css .= gstm_get_color_css($data['section_bg_color']);

        if (empty($data['section_image_parallax']) || !wp_validate_boolean($data['section_image_parallax'])) {
            $css .= gstm_get_bg_img_css($data);
        }
    }

    if (empty($css)) return '';

    return $css;
}


function gstm_get_style_for_overlay($data)
{

    extract($data);

    if (empty($section_overlay) || empty($section_overlay['type'])) return "";

    $css = gstm_get_color_css($section_overlay);

    if (empty($css)) return "";

    return $css;
}


function gstm_get_bg_img_css($data)
{

    extract($data);
    extract($data['section_bg_image']);

    $css = '';

    if ($section_bg_color['type'] !== 'gradient' && !empty($bg_image['url'])) {

        $css .= "background-image: url({$bg_image['url']});";

        if (!empty($bg_position)) {
            $css .= "background-position: {$bg_position};";
        }

        if (!empty($bg_attachment)) {
            $css .= "background-attachment: {$bg_attachment};";
        }

        if (!empty($bg_repeat)) {
            $css .= "background-repeat: {$bg_repeat};";
        }

        if (!empty($bg_size)) {
            $css .= "background-size: {$bg_size};";
        }
    }

    return $css;
}


function gstm_get_custom_css($data, $id)
{

    $css = $t_css = $m_css = '';

    if (!empty($_css = gstm_get_style_for_main_div($data))) $css .= sprintf('.gs-smart-section.gs-smart-section-%s {%s}', $id, $_css);
    if (!empty($_css = gstm_get_style_for_main_div($data, 'tablet'))) $t_css .= sprintf('.gs-smart-section.gs-smart-section-%s {%s}', $id, $_css);
    if (!empty($_css = gstm_get_style_for_main_div($data, 'mobile'))) $m_css .= sprintf('.gs-smart-section.gs-smart-section-%s {%s}', $id, $_css);

    if (!empty($_css = gstm_get_style_for_overlay($data))) $css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--overlay {%s}', $id, $_css);


    if (!empty($data['section_image_parallax']) && wp_validate_boolean($data['section_image_parallax'])) {
        if (!empty($_css = gstm_get_bg_img_css($data))) {
            $css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--parallax .gs-smart-section--parallax-image {%s}', $id, $_css);
        }
    }

    if (!empty($data['section_title']) && !empty($data['typography_title'])) {

        if (!empty($_css = gstm_get_fonts_css($data['typography_title']))) {
            $css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--title {%s}', $id, $_css);
        }

        if (!empty($_css = gstm_get_fonts_css($data['typography_title'], 'tablet'))) {
            $t_css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--title {%s}', $id, $_css);
        }

        if (!empty($_css = gstm_get_fonts_css($data['typography_title'], 'mobile'))) {
            $m_css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--title {%s}', $id, $_css);
        }
    }

    if (!empty($data['section_subtitle']) && !empty($data['section_subtitle_typography'])) {

        if (!empty($_css = gstm_get_fonts_css($data['section_subtitle_typography']))) {
            $css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--subtitle {%s}', $id, $_css);
        }

        if (!empty($_css = gstm_get_fonts_css($data['section_subtitle_typography'], 'tablet'))) {
            $t_css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--subtitle {%s}', $id, $_css);
        }

        if (!empty($_css = gstm_get_fonts_css($data['section_subtitle_typography'], 'mobile'))) {
            $m_css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--subtitle {%s}', $id, $_css);
        }
    }

    if (!empty($data['section_elements'])) {
        $css .= gstm_get_style_for_elements($data['section_elements'], $id);
        $t_css .= gstm_get_style_for_elements($data['section_elements'], $id, 'tablet');
        $m_css .= gstm_get_style_for_elements($data['section_elements'], $id, 'mobile');
    }

    if (!empty($t_css)) $t_css = sprintf('@media screen and (max-width: 1024px) {%s}', $t_css);

    if (!empty($m_css)) $m_css = sprintf('@media screen and (max-width: 767px) {%s}', $m_css);

    return $css . $t_css . $m_css;
}


function gstm_get_style_for_elements($elements, $section_id, $device = '')
{

    $css = '';

    if (!empty($device)) $device = '_' . $device;

    foreach ($elements as $element) {

        // Desktop
        $position_x = $element['position']['x'];
        $position_y = $element['position']['y'];
        $rotate = $element['rotate'];
        $size = $element['size'];
        $color = $element['color'];

        // Tablet
        if ($device == '_tablet') {
            if (!empty($element['position']['x_tablet'])) $position_x = $element['position']['x_tablet'];
            if (!empty($element['position']['y_tablet'])) $position_y = $element['position']['y_tablet'];
            if (!empty($element['rotate_tablet'])) $rotate = $element['rotate_tablet'];
            if (!empty($element['size_tablet'])) $size = $element['size_tablet'];
        }

        // Mobile
        if ($device == '_mobile') {

            if (!empty($element['position']['x_tablet'])) $position_x = $element['position']['x_tablet'];
            if (!empty($element['position']['y_tablet'])) $position_y = $element['position']['y_tablet'];
            if (!empty($element['rotate_tablet'])) $rotate = $element['rotate_tablet'];
            if (!empty($element['size_tablet'])) $size = $element['size_tablet'];

            if (!empty($element['position']['x_mobile'])) $position_x = $element['position']['x_mobile'];
            if (!empty($element['position']['y_mobile'])) $position_y = $element['position']['y_mobile'];
            if (!empty($element['rotate_mobile'])) $rotate = $element['rotate_mobile'];
            if (!empty($element['size_mobile'])) $size = $element['size_mobile'];
        }

        $style = sprintf(
            '-webkit-transform: translate(%1$dpx, %2$dpx) rotate(%3$ddeg); -ms-transform: translate(%1$dpx, %2$dpx) rotate(%3$ddeg); transform: translate(%1$dpx, %2$dpx) rotate(%3$ddeg); width: %4$dpx;',
            $position_x,
            $position_y,
            $rotate,
            $size
        );

        $info = pathinfo($element['media']['url']);

        if ($info['extension'] == 'svg' && empty($device)) {
            $style .= "fill: $color;";
        }

        if (!empty($style)) $css .= sprintf('.gs-smart-section.gs-smart-section-%s .gs-smart-section--elements #gs-element--%s {%s}', $section_id, $element['_id'], $style);
    }

    return $css;
}


function gstm_get_visibility_class($key, $card_visibility_settings)
{

    if (empty($card_visibility_settings[$key])) {
        return '';
    }

    $map = $card_visibility_settings[$key];

    $classes = [];

    $classes[] = ! empty($map['desktop']) ? 'gstm-show-desktop' : 'gstm-hide-desktop';
    $classes[] = ! empty($map['tablet']) ? 'gstm-show-tablet' : 'gstm-hide-tablet';
    $classes[] = ! empty($map['mobile_landscape']) ? 'gstm-show-mobile-landscape' : 'gstm-hide-mobile-landscape';
    $classes[] = ! empty($map['mobile']) ? 'gstm-show-mobile' : 'gstm-hide-mobile';

    return implode(' ', $classes);
}


function gs_wp_star_rating($args = array(), $icon_name = 'star')
{ ?>
    <style>
        .column-gs_t_client_rating .gs-star-rating {
            width: 50%;
            display: flex;
        }
    </style>
<?php $defaults    = array(
        'rating' => 0,
        'type'   => 'rating',
        'number' => 0,
        'class'  => '',
        'echo'   => true,
    );
    $parsed_args = wp_parse_args($args, $defaults);

    // Non-English decimal places when the $rating is coming from a string.
    $rating = (float) str_replace(',', '.', $parsed_args['rating']);

    // Convert percentage to star rating, 0..5 in .5 increments.
    if ('percent' === $parsed_args['type']) {
        $rating = round($rating / 10, 0) / 2;
    }

    // Calculate the number of each type of star needed.
    $full_stars  = floor($rating);
    $half_stars  = ceil($rating - $full_stars);
    // $empty_stars = 5 - $full_stars - $half_stars;
    $empty_stars = max(0, 5 - $full_stars - $half_stars);


    if ($parsed_args['number']) {
        /* translators: Hidden accessibility text. 1: The rating, 2: The number of ratings. */
        $format = _n('%1$s rating based on %2$s rating', '%1$s rating based on %2$s ratings', $parsed_args['number'], 'gs-testimonial');
        $title  = sprintf($format, number_format_i18n($rating, 1), number_format_i18n($parsed_args['number']));
    } else {
        /* translators: Hidden accessibility text. %s: The rating. */
        $title = sprintf(__('%s rating', 'gs-testimonial'), number_format_i18n($rating, 1));
    }

    $icons = gs_ratings_svg_icons($icon_name);

    $output  = '<div class="gs-star-rating ' . esc_attr($parsed_args['class']) . '">';
    $output .= '<span class="screen-reader-text">' . $title . '</span>';
    $output .= str_repeat($icons['full'], $full_stars);
    $output .= str_repeat($icons['half'], $half_stars);
    $output .= str_repeat($icons['empty'], $empty_stars);
    $output .= '</div>';


    if ($parsed_args['echo']) {
        echo $output;
    }

    return $output;
}


function gs_ratings_svg_icons($icon_name)
{

    $icons = [

        [
            'diamond' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="100%" height="100%" fill="currentColor">
                                <path d="M32 4L2 24l30 36 30-36L32 4z"/>
                                <path d="M32 4L20 24h24L32 4z" fill="black" opacity="0.2"/>
                                <path d="M2 24h18l12 36L2 24z" fill="black" opacity="0.2"/>
                                <path d="M62 24H44L32 60l30-36z" fill="black" opacity="0.2"/>
                            </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="100%" height="100%">
                                <defs>
                                  <clipPath id="half-faceted-diamond">
                                    <rect x="0" y="0" width="32" height="64" />
                                  </clipPath>
                                </defs>

                                <polygon points="32 4 2 24 32 60 62 24 32 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>

                                <line x1="32" y1="4" x2="32" y2="60" stroke="currentColor" stroke-width="2"/>
                                <line x1="2" y1="24" x2="62" y2="24" stroke="currentColor" stroke-width="2"/>
                                <line x1="12" y1="24" x2="32" y2="60" stroke="currentColor" stroke-width="2"/>
                                <line x1="52" y1="24" x2="32" y2="60" stroke="currentColor" stroke-width="2"/>
                                <line x1="32" y1="4" x2="12" y2="24" stroke="currentColor" stroke-width="2"/>
                                <line x1="32" y1="4" x2="52" y2="24" stroke="currentColor" stroke-width="2"/>

                                <polygon points="32 4 2 24 32 60 62 24 32 4" fill="currentColor" clip-path="url(#half-faceted-diamond)" />
                            </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round">
                                <polygon points="32 4 2 24 32 60 62 24 32 4"/>
                                <line x1="32" y1="4" x2="32" y2="60"/>
                                <line x1="2" y1="24" x2="62" y2="24"/>
                                <line x1="12" y1="24" x2="32" y2="60"/>
                                <line x1="52" y1="24" x2="32" y2="60"/>
                                <line x1="32" y1="4" x2="12" y2="24"/>
                                <line x1="32" y1="4" x2="52" y2="24"/>
                            </svg>',
            ]
        ],

        [
            'square' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                             <rect x="4" y="4" width="16" height="16" rx="2" ry="2" />
                            </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                              <clipPath id="half-square-left">
                                <rect x="4" y="4" width="8" height="16" />
                              </clipPath>
                            </defs>

                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                            <rect x="4" y="4" width="16" height="16" rx="2" ry="2"
                                  fill="currentColor" clip-path="url(#half-square-left)" />
                         </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="4" width="16" height="16" rx="2" ry="2" />
                            </svg>',
            ]
        ],

        [
            'thumb' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                            <path d="M2 10h4v12H2V10zm6 0V5.8c0-1.6 1.3-2.8 2.8-2.8.6 0 1.2.2 1.7.6l1 1c.6.6.9 1.4.8 2.2L13.6 10H20c1.1 0 2 .9 2 2v.2l-2 6.1c-.3.9-1.1 1.7-2.1 1.7H8V10z"/>
                          </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                              <clipPath id="half-thumb-improved">
                                <rect x="0" y="0" width="12" height="24" />
                              </clipPath>
                            </defs>

                            <path d="M2 10h4v12H2V10zM8 10V5.8c0-1.6 1.3-2.8 2.8-2.8.6 0 1.2.2 1.7.6l1 1c.6.6.9 1.4.8 2.2L13.6 10H20c1.1 0 2 .9 2 2v.2l-2 6.1c-.3.9-1.1 1.7-2.1 1.7H8V10z"
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

                            <path d="M2 10h4v12H2V10zM8 10V5.8c0-1.6 1.3-2.8 2.8-2.8.6 0 1.2.2 1.7.6l1 1c.6.6.9 1.4.8 2.2L13.6 10H20c1.1 0 2 .9 2 2v.2l-2 6.1c-.3.9-1.1 1.7-2.1 1.7H8V10z"
                                fill="currentColor" clip-path="url(#half-thumb-improved)" />
                            </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M2 10h4v12H2V10zM8 10V5.8c0-1.6 1.3-2.8 2.8-2.8.6 0 1.2.2 1.7.6l1 1c.6.6.9 1.4.8 2.2L13.6 10H20c1.1 0 2 .9 2 2v.2l-2 6.1c-.3.9-1.1 1.7-2.1 1.7H8V10z"/>
                            </svg>',
            ]
        ],

        [
            'hourglass' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                            <path d="M6 2v2c0 2 .7 4 2 5l2 1-2 1c-1.3 1-2 3-2 5v2h12v-2c0-2-.7-4-2-5l-2-1 2-1c1.3-1 2-3 2-5V2H6z"/>
                            </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                             <clipPath id="half-hourglass-fill">
                               <rect x="0" y="0" width="12" height="24"/>
                             </clipPath>
                            </defs>

                            <path d="M6 2v2c0 2 .7 4 2 5l2 1-2 1c-1.3 1-2 3-2 5v2h12v-2c0-2-.7-4-2-5l-2-1 2-1c1.3-1 2-3 2-5V2H6z"
                            fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                            <path d="M6 2v2c0 2 .7 4 2 5l2 1-2 1c-1.3 1-2 3-2 5v2h12v-2c0-2-.7-4-2-5l-2-1 2-1c1.3-1 2-3 2-5V2H6z"
                            fill="currentColor" clip-path="url(#half-hourglass-fill)" />
                        </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 2v2c0 2 .7 4 2 5l2 1-2 1c-1.3 1-2 3-2 5v2h12v-2c0-2-.7-4-2-5l-2-1 2-1c1.3-1 2-3 2-5V2H6z"/>
                            </svg>',
            ]
        ],

        [
            'comment' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                            <path d="M4 4h16a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H7l-4 4V6a2 2 0 0 1 2-2z"/>
                        </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                              <clipPath id="half-comment-fill">
                               <rect x="0" y="0" width="12" height="24"/>
                             </clipPath>
                            </defs>
                          <path d="M21 6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2V6z"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round" />
                            <path d="M21 6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14l4-4h12a2 2 0 0 0 2-2V6z"
                            fill="currentColor"
                            clip-path="url(#half-comment-fill)" />
                        </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v14l4-4h13a2 2 0 0 0 2-2V6z"/>
                            </svg>',
            ]
        ],

        [
            'love' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%" fill="currentColor">
                          <path d="M462.3 62.7c-54.5-46.4-136-38.3-186.4 15.8L256 96.6l-19.9-18.1C186 24.3 104.5 16.2 50 62.7-17 123.1-10.6 221 43 275.6l175.3 178.7c10 10.2 23.4 15.7 37.7 15.7s27.7-5.6 37.7-15.7L469 275.6c53.6-54.6 60-152.5-6.7-212.9z"/>
                      </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%">
                        <defs>
                         <clipPath id="half-fill">
                           <rect x="0" y="0" width="256" height="512"/>
                         </clipPath>
                        </defs>
                        <path d="M462.3 62.7c-54.5-46.4-136-38.3-186.4 15.8L256 96.6l-19.9-18.1C186 24.3 104.5 16.2 50 62.7-17 123.1-10.6 221 43 275.6l175.3 178.7c10 10.2 23.4 15.7 37.7 15.7s27.7-5.6 37.7-15.7L469 275.6c53.6-54.6 60-152.5-6.7-212.9z"
                              fill="none" stroke="currentColor" stroke-width="40"/>
  
                        <path d="M462.3 62.7c-54.5-46.4-136-38.3-186.4 15.8L256 96.6l-19.9-18.1C186 24.3 104.5 16.2 50 62.7-17 123.1-10.6 221 43 275.6l175.3 178.7c10 10.2 23.4 15.7 37.7 15.7s27.7-5.6 37.7-15.7L469 275.6c53.6-54.6 60-152.5-6.7-212.9z"
                             fill="currentColor" clip-path="url(#half-fill)"/>
                        </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="100%" height="100%" fill="currentColor">
                            <path d="M462.3 62.7c-54.5-46.4-136-38.3-186.4 15.8L256 96.6l-19.9-18.1C186 24.3 104.5 16.2 50 62.7-17 123.1-10.6 221 43 275.6l175.3 178.7c10 10.2 23.4 15.7 37.7 15.7s27.7-5.6 37.7-15.7L469 275.6c53.6-54.6 60-152.5-6.7-212.9zM256 439.6L80.7 264.3C36.6 220.6 31 150.2 76.1 109.1c38.6-35.1 98.3-29.4 135.6 8.8l44.3 44.3 44.3-44.3c37.2-38.2 97-43.9 135.6-8.8 45.1 41.1 39.5 111.5-4.6 155.2L256 439.6z"/>
                        </svg>',
            ]
        ],

        [
            'grin-stars' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="100%" height="100%" fill="currentColor">
                          <path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm88.7 142.4 7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0zm-169.6 0 7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0zM248 432c-52.9 0-99.8-25.3-130-64.3-8.7-11.4 3.8-26.3 17.1-20.6 35.5 15.4 74.2 24.9 112.9 24.9s77.4-9.5 112.9-24.9c13.3-5.8 25.9 9.2 17.1 20.6-30.2 39-77.1 64.3-130 64.3z"/>
                        </svg>',
                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="100%" height="100%">
                        <defs>
                          <clipPath id="left-half">
                            <rect x="0" y="0" width="248" height="512"/>
                          </clipPath>
                        </defs>
                        <path fill="none" stroke="currentColor" stroke-width="32" d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8z"/>
                        <path fill="none" stroke="currentColor" stroke-width="20"
                          d="M336.7 150.4l7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0z
                            M167.1 150.4l7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0z
                             M118 367.7c30.2 39 77.1 64.3 130 64.3s99.8-25.3 130-64.3c8.7-11.4-3.8-26.3-17.1-20.6-35.5 15.4-74.2 24.9-112.9 24.9s-77.4-9.5-112.9-24.9c-13.3-5.7-25.9 9.2-17.1 20.6z"/>
                        <g clip-path="url(#left-half)">
                          <path fill="currentColor"
                            d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8zm-80.9 142.4c6.2-17.8 30.7-17.8 36.9 0l7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5z
                               M118 367.7c30.2 39 77.1 64.3 130 64.3v-40c-38.8 0-77.4-9.5-112.9-24.9-13.2-5.8-25.8 9.1-17.1 20.6z"/>
                         </g>
                        </svg>',
                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="32">
                        <path d="M248 8C111 8 0 119 0 256s111 248 248 248 248-111 248-248S385 8 248 8z" />
                        <path d="M336.7 150.4l7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0z" />
                        <path d="M167.1 150.4l7.1 20.5 20.5 7.1c17.8 6.2 17.8 30.7 0 36.9l-20.5 7.1-7.1 20.5c-6.2 17.8-30.7 17.8-36.9 0l-7.1-20.5-20.5-7.1c-17.8-6.2-17.8-30.7 0-36.9l20.5-7.1 7.1-20.5c6.2-17.8 30.7-17.8 36.9 0z" />
                        <path d="M118 367.7c30.2 39 77.1 64.3 130 64.3s99.8-25.3 130-64.3c8.7-11.4-3.8-26.3-17.1-20.6-35.5 15.4-74.2 24.9-112.9 24.9s-77.4-9.5-112.9-24.9c-13.3-5.7-25.9 9.2-17.1 20.6z" />
                        </svg>',
            ]
        ],

        [
            'star' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                        <path d="M12 2l2.9 5.9 6.1.9-4.4 4.3 1 6.1-5.6-2.9-5.6 2.9 1-6.1-4.4-4.3 6.1-.9L12 2z"/>
                      </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                        <defs>
                          <clipPath id="half-star">
                            <rect x="0" y="0" width="12" height="24" />
                          </clipPath>
                        </defs>
                        <path d="M12 2l2.9 5.9 6.1.9-4.4 4.3 1 6.1-5.6-2.9-5.6 2.9 1-6.1-4.4-4.3 6.1-.9L12 2z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M12 2l2.9 5.9 6.1.9-4.4 4.3 1 6.1-5.6-2.9-5.6 2.9 1-6.1-4.4-4.3 6.1-.9L12 2z" fill="currentColor" clip-path="url(#half-star)" />
                    </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polygon points="12 2 15 8.1 22 9.2 17 14.1 18.2 21 12 17.7 5.8 21 7 14.1 2 9.2 9 8.1 12 2"/>
                        </svg>',
            ]
        ],

        [
            'trophy' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                            <path d="M17 3V2a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v1H3v3a5 5 0 0 0 4.2 4.9 7.1 7.1 0 0 0 3.3 4.3V18H7v2h10v-2h-3.5v-2.8a7.1 7.1 0 0 0 3.3-4.3A5 5 0 0 0 21 6V3h-4zm-2 0v1H9V3h6z"/>
                       </svg>',
                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                              <clipPath id="half-trophy-fill">
                                <rect x="0" y="0" width="12" height="24" />
                              </clipPath>
                            </defs>
  
                            <path d="M17 3V2a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v1H3v3a5 5 0 0 0 4 4.9 6.9 6.9 0 0 0 3 4.3V18H7v2h10v-2h-3v-2.8a6.9 6.9 0 0 0 3-4.3A5 5 0 0 0 21 6V3h-4zm-2 0v1H9V3h6z" 
                                  fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />

                            <path d="M17 3V2a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v1H3v3a5 5 0 0 0 4 4.9 6.9 6.9 0 0 0 3 4.3V18H7v2h10v-2h-3v-2.8a6.9 6.9 0 0 0 3-4.3A5 5 0 0 0 21 6V3h-4z" 
                                  fill="currentColor" clip-path="url(#half-trophy-fill)" />
                        </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 3V2a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v1H3v3a5 5 0 0 0 4.2 4.9 7.1 7.1 0 0 0 3.3 4.3V18H7v2h10v-2h-3.5v-2.8a7.1 7.1 0 0 0 3.3-4.3A5 5 0 0 0 21 6V3h-4zM15 3v1H9V3h6z"/>
                        </svg>',
            ]
        ],

        [
            'circle' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                          <circle cx="12" cy="12" r="10"/>
                      </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                            <defs>
                              <clipPath id="half-left">
                                <rect x="0" y="0" width="12" height="24"/>
                              </clipPath>
                            </defs>
                             <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none"/>
                             <circle cx="12" cy="12" r="10" fill="currentColor" clip-path="url(#half-left)"/>
                        </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/>
                        </svg>',
            ]
        ],

        [
            'moon' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="currentColor">
                    <path d="M21 15A9 9 0 0 1 9 3a9 9 0 1 0 12 12z"/>
                  </svg>',

                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%">
                    <defs>
                      <clipPath id="moon-half-fill">
                        <rect x="0" y="0" width="12" height="24" />
                      </clipPath>
                    </defs>
                    <!-- Moon shape outline -->
                    <path d="M21 15A9 9 0 0 1 9 3a9 9 0 1 0 12 12z" fill="none" stroke="currentColor" stroke-width="2"/>
  
                    <!-- Left half fill -->
                    <path d="M21 15A9 9 0 0 1 9 3a9 9 0 1 0 12 12z" fill="currentColor" clip-path="url(#moon-half-fill)"/>
                    </svg>',

                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="2">
                     <path d="M21 15A9 9 0 0 1 9 3a9 9 0 1 0 12 12z"/>
                   </svg>',
            ]
        ],

        [
            'hand-holding-heart' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="100%" height="100%" fill="currentColor">
        <!-- Heart (fully filled) -->
        <path d="M280 250c-5 4-13 4-18 0L120 110C80 75 110 15 160 40l30 25 30-25c50-25 80 35 40 70L280 250z"/>
        <!-- Hand (filled) -->
        <path d="M560 330c-10-20-30-30-50-20L420 330H320c-10 0-20 10-20 20s10 20 20 20h100c10 0 20 10 20 20s-10 20-20 20H280c-10 0-20-10-20-20v-50H120c-10 0-20 10-20 20s10 20 20 20h70c10 0 20 10 20 20s-10 20-20 20h-70c-40 0-70-30-70-70s30-70 70-70h160l130-50c40-20 80 0 100 40 5 10 0 25-10 30s-25 0-30-10z"/>
    </svg>',
                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="100%" height="100%">
        <defs>
            <!-- Clip exactly the left half of the heart -->
            <clipPath id="half-heart">
                <rect x="0" y="0" width="288" height="512"/>
            </clipPath>
        </defs>
        <!-- Heart left half (filled) -->
        <path d="M280 250c-5 4-13 4-18 0L120 110C80 75 110 15 160 40l30 25 30-25c50-25 80 35 40 70L280 250z" 
              fill="currentColor" clip-path="url(#half-heart)"/>
        <!-- Heart outline -->
        <path d="M280 250c-5 4-13 4-18 0L120 110C80 75 110 15 160 40l30 25 30-25c50-25 80 35 40 70L280 250z" 
              fill="none" stroke="currentColor" stroke-width="10"/>
        <!-- Hand outline -->
        <path d="M560 330c-10-20-30-30-50-20L420 330H320c-10 0-20 10-20 20s10 20 20 20h100c10 0 20 10 20 20s-10 20-20 20H280c-10 0-20-10-20-20v-50H120c-10 0-20 10-20 20s10 20 20 20h70c10 0 20 10 20 20s-10 20-20 20h-70c-40 0-70-30-70-70s30-70 70-70h160l130-50c40-20 80 0 100 40 5 10 0 25-10 30s-25 0-30-10z" 
              fill="none" stroke="currentColor" stroke-width="10"/>
    </svg>',
                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="10">
        <path d="M280 250c-5 4-13 4-18 0L120 110C80 75 110 15 160 40l30 25 30-25c50-25 80 35 40 70L280 250zM560 330c-10-20-30-30-50-20L420 330H320c-10 0-20 10-20 20s10 20 20 20h100c10 0 20 10 20 20s-10 20-20 20H280c-10 0-20-10-20-20v-50H120c-10 0-20 10-20 20s10 20 20 20h70c10 0 20 10 20 20s-10 20-20 20h-70c-40 0-70-30-70-70s30-70 70-70h160l130-50c40-20 80 0 100 40 5 10 0 25-10 30s-25 0-30-10z"/>
    </svg>'
            ],
        ],

        [

            'hand-paper' => [
                'full' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="100%" height="100%" fill="currentColor">
        <path d="M408 144c-22 0-40 18-40 40v88c0 13-11 24-24 24s-24-11-24-24V72c0-48-39-88-88-88-41 0-72 28-84 64-3 9-12 16-22 16H88C39 144 0 183 0 232v160c0 48 39 88 88 88h312c48 0 88-39 88-88V184c0-22-18-40-40-40z"/>
    </svg>',
                'half' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="100%" height="100%">
        <defs>
            <clipPath id="half-hand">
                <rect x="0" y="0" width="224" height="512"/>
            </clipPath>
        </defs>
        <path d="M408 144c-22 0-40 18-40 40v88c0 13-11 24-24 24s-24-11-24-24V72c0-48-39-88-88-88-41 0-72 28-84 64-3 9-12 16-22 16H88C39 144 0 183 0 232v160c0 48 39 88 88 88h312c48 0 88-39 88-88V184c0-22-18-40-40-40z" 
              fill="currentColor" clip-path="url(#half-hand)"/>
        <path d="M408 144c-22 0-40 18-40 40v88c0 13-11 24-24 24s-24-11-24-24V72c0-48-39-88-88-88-41 0-72 28-84 64-3 9-12 16-22 16H88C39 144 0 183 0 232v160c0 48 39 88 88 88h312c48 0 88-39 88-88V184c0-22-18-40-40-40z" 
              fill="none" stroke="currentColor" stroke-width="16"/>
    </svg>',
                'empty' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="100%" height="100%" fill="none" stroke="currentColor" stroke-width="16">
        <path d="M408 144c-22 0-40 18-40 40v88c0 13-11 24-24 24s-24-11-24-24V72c0-48-39-88-88-88-41 0-72 28-84 64-3 9-12 16-22 16H88C39 144 0 183 0 232v160c0 48 39 88 88 88h312c48 0 88-39 88-88V184c0-22-18-40-40-40z"/>
    </svg>'
            ],

        ]
    ];


    if (!empty($icon_name)) {
        $type = $icon_name;

        foreach ($icons as $entry) {
            if (is_array($entry) && isset($entry[$type])) {
                return  $entry[$type];
            }
        }
    }

    return false;
}
