<?php

namespace GSTM;

$popup_visibility_orders = $shortcode_settings['popup_visibility_settings'] ?? [];


$get_visibility_class = function( $key ) use ( $popup_visibility_orders ) {

    if ( empty( $popup_visibility_orders[ $key ] ) ) {
        return '';
    }

    $map = $popup_visibility_orders[ $key ];

    $classes = [];

    $classes[] = ! empty( $map['desktop'] ) ? 'gstm-show-desktop' : 'gstm-hide-desktop';
    $classes[] = ! empty( $map['tablet'] ) ? 'gstm-show-tablet' : 'gstm-hide-tablet';
    $classes[] = ! empty( $map['mobile_landscape'] ) ? 'gstm-show-mobile-landscape' : 'gstm-hide-mobile-landscape';
    $classes[] = ! empty( $map['mobile'] ) ? 'gstm-show-mobile' : 'gstm-hide-mobile';

    return implode( ' ', $classes );
};


$default_field_order = array_values(
    array_keys( $popup_visibility_orders )
);


$field_positions = [];
foreach ($default_field_order as $index => $field) {
    $field_positions[$field] = $index;
}


$all_fields = [];


$get_pos = function ($key) use ($field_positions) {
    return isset($field_positions[$key]) ? (float) $field_positions[$key] : 999.0;
};


if (! empty($image_id)) {
    $logo = wp_get_attachment_image($image_id, 'medium');
    if (! is_wp_error($logo) && $logo) {
        $all_fields[] = [
            'html'     => '<div class="company-image ' . esc_attr( $get_visibility_class('gstm_logo') ) . '">' . $logo . '</div>',
            'position' => $get_pos('gstm_logo'),
        ];
    }
}


if (! empty($testi_title)) {
    $all_fields[] = [
        'html'     => '<h3 class="box-tm-title ' . esc_attr( $get_visibility_class('gstm_title') ) . '">' . esc_html($testi_title) . '</h3>',
        'position' => $get_pos('gstm_title'),
    ];
}


$all_fields[] = [
    'html'     => '<div class="box-content ' . esc_attr( $get_visibility_class('gstm_description') ) . '">' . wpautop(wp_kses_post(get_the_content())) . '</div>',
    'position' => $get_pos('gstm_description'),
];


if (! empty($rating) && ! empty($show_popup_rating)) {
    ob_start();
    $args = array(
				'rating' => $rating,
				'type'   => 'rating',
				'number' => '',
                'class'  => $get_visibility_class('gstm_ratings'),
				'echo'   => true,
				);
			gs_wp_star_rating($args, $shortcode_settings['gs_ratings_icon']);
						

    $stars = ob_get_clean();

    if ($stars) {
        $all_fields[] = [
            'html'     => $stars,
            'position' => $get_pos('gstm_ratings'),
        ];
    }
}


if (has_post_thumbnail()) {
    $size = ! empty($shortcode_settings['imageSize']) ? $shortcode_settings['imageSize'] : 'medium';

    $all_fields[] = [
        'html'     => '<div class="box-image ' . esc_attr( $get_visibility_class('gstm_reviewer_image') ) . '">' . get_the_post_thumbnail(null, $size) . '</div>',
        'position' => $get_pos('gstm_reviewer_image'),
    ];
}



if (! empty($client_name)) {
     $all_fields[] = [
        'html'     => '<h4 class="box-client-name ' . esc_attr( $get_visibility_class('gstm_reviewer_name') ) . '">' . esc_html($client_name) . '</h4>',
        'position' => $get_pos('gstm_reviewer_name'),
    ];
}

if (! empty($designatin)) {
     $all_fields[] = [
        'html'     => '<div class="box-desiginfo ' . esc_attr( $get_visibility_class('gstm_reviewer_designation') ) . '"><span class="box-design-name">' . esc_html($designatin) . '</span></div>',
        'position' => $get_pos('gstm_reviewer_designation'),
    ];
}

if (! empty($company)) {
    $all_fields[] = [
        'html'     => '<div class="box-companyinfo ' . esc_attr( $get_visibility_class('gstm_company_name') ) . '"><span class="box-company-name">' . esc_html($company) . '</span></div>',
        'position' => $get_pos('gstm_company_name'),
    ];
}


if (! empty($client_address)) {
    $all_fields[] = [
        'html'     => '<div class="gs-tai-contact ' . esc_attr( $get_visibility_class('gstm_address') ) . '"><i class="fas fa-map-marker-alt"></i>' . esc_html($client_address) . '</div>',
        'position' => $get_pos('gstm_address'),
    ];
}


if (! empty($client_phone)) {
    $all_fields[] = [
        'html'     => '<div class="gs-tai-contact ' . esc_attr( $get_visibility_class('gstm_mobile') ) . '"><a href="tel:' . esc_attr($client_phone) . '"><i class="fas fa-phone"></i>' . esc_html($client_phone) . '</a></div>',
        'position' => $get_pos('gstm_mobile'),
    ];
}


if (! empty($client_email)) {
    $all_fields[] = [
        'html'     => '<div class="gs-tai-contact ' . esc_attr( $get_visibility_class('gstm_email') ) . '"><a href="mailto:' . esc_attr($client_email) . '"><i class="fas fa-envelope"></i>' . esc_html($client_email) . '</a></div>',
        'position' => $get_pos('gstm_email'),
    ];
}


if (! empty($client_website)) {
    $all_fields[] = [
        'html'     => '<div class="gs-tai-contact ' . esc_attr( $get_visibility_class('gstm_website') ) . '"><a href="' . esc_url($client_website) . '" target="_blank" rel="noopener noreferrer nofollow"><i class="fas fa-globe"></i>' . esc_html($client_website) . '</a></div>',
        'position' => $get_pos('gstm_website'),
    ];
}


if (! empty($show_published_date) && ! empty($testi_date)) {
    $all_fields[] = [
        'html'     => '<div class="gs-review-date ' . esc_attr( $get_visibility_class('gstm_publish_date') ) . '">' . esc_html($testi_date) . '</div>',
        'position' => $get_pos('gstm_publish_date'),
    ];
} 


$social_path = Template_Loader::locate_template('partials/gs-layout-social-links.php');

if (! is_wp_error($social_path) && file_exists($social_path)) {
    ob_start();
    include $social_path;
    $social = ob_get_clean();

    if ($social) {
        $all_fields[] = [
            'html'     => '<div class="'.esc_attr( $get_visibility_class('gstm_social_icons') ).'">'.$social.'</div>',
            'position' => $get_pos('gstm_social_icons'),
        ];
    }
}

/**
 * Sort by position
 */
usort(
    $all_fields,
    function ($a, $b) {
        return $a['position'] <=> $b['position'];
    }
);
?>

<div id="gstm_popup_<?php echo get_the_ID(); ?>_<?php echo esc_attr($shortcode_id); ?>" class="gstm_popup_shortcode_<?php echo esc_attr($shortcode_id); ?> white-popup mfp-hide mfp-with-anim gstm_popup gstm_popup_details">
    <div class="mfp-content--container">
        <div class="gs-containeer" itemscope="" itemtype="http://schema.org/Person">
            <div class="gstm-popup--content-area">

                <?php
                foreach ($all_fields as $field) {
                    echo $field['html'];
                }
                ?>

            </div>
        </div>
    </div>
</div>


<style>
    /* Desktop */
@media (min-width: 1025px) {
  .gstm-hide-desktop { display: none !important; }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1024px) {
  .gstm-hide-tablet { display: none !important; }
}

/* Mobile Landscape */
@media (min-width: 576px) and (max-width: 767px) {
  .gstm-hide-mobile-landscape { display: none !important; }
}

/* Mobile */
@media (max-width: 575px) {
  .gstm-hide-mobile { display: none !important; }
}
</style>