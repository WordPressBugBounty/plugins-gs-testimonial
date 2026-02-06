<?php
namespace GSTM;

$popup_visibility_orders = $popup_visibility_orders ?? [];

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



$social_links = (array) get_post_meta( get_the_ID(), 'gs_t_social_profiles', true );

$social_links = array_filter( $social_links );

if ( !empty($social_links) ): ?>

    <ul class="gs-tai-socials-icon">

    <?php foreach ( $social_links as $icon => $url ):
        
        $linkclass = str_replace( ['fa-', 'fab', 'fas', 'far'], '', $icon );
        $linkclass = trim($linkclass);

        if ( str_contains( $icon, 'envelope' ) ) {
            $url = !empty( $url ) ? 'mailto:' . $url : '#';
        } else {
            $url = !empty( $url ) ? $url : '#';
        } ?>

        <li>
            <?php printf( '<a class="%s" href="%s" target="_blank" itemprop="sameAs"><i class="%s"></i></a>', esc_attr( $linkclass ), esc_url( $url ), esc_attr( $icon ) ); ?>
        </li>

    <?php endforeach; ?>
        
    </ul>

<?php endif; ?>