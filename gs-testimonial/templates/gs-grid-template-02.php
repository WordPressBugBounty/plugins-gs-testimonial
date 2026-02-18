<?php

namespace GSTM;

?>
<div class="gs-containeer">

	<?php 
?>

	<?php 
if ( $gs_t_loop->have_posts() ) {
    ?>
		<div class="<?php 
    echo esc_attr( implode( ' ', $gs_row_classes ) );
    ?>">
			<?php 
    while ( $gs_t_loop->have_posts() ) {
        $gs_t_loop->the_post();
        $classes = ["gs_testimonial_single gs-col-lg-{$desktop_columns} gs-col-md-{$tablet_columns} gs-col-sm-{$columns_mobile} gs-col-xs-{$columns_small_mobile} {$box_bg_color}"];
        $image_id = get_post_meta( get_the_ID(), 'rudr_img', true );
        $rating = get_post_meta( get_the_ID(), 'gs_t_rating', true );
        $testi_title = get_the_title();
        $designatin = get_post_meta( get_the_ID(), 'gs_t_client_design', true );
        $client_name = get_post_meta( get_the_ID(), 'gs_t_client_name', true );
        $testi_date = get_the_date( get_option( 'date_format' ) );
        $company = get_post_meta( get_the_ID(), 'gs_t_client_company', true );
        $client_phone = get_post_meta( get_the_ID(), 'gs_t_client_phone', true );
        $client_email = get_post_meta( get_the_ID(), 'gs_t_client_email_address', true );
        $client_address = get_post_meta( get_the_ID(), 'gs_t_client_location', true );
        $client_website = get_post_meta( get_the_ID(), 'gs_t_website_url', true );
        $filter_rating = ( intval( $rating ) == $rating ? (string) intval( $rating ) : str_replace( '.', '-', (string) $rating ) );
        if ( $view_type == 'filter' && $gs_filter_by == 'tags' ) {
            $classes[] = get_terms_slugs( 'gs_testimonial_tag' );
        }
        if ( $view_type == 'filter' && $gs_filter_by == 'cats' ) {
            $classes[] = get_terms_slugs( 'gs_testimonial_category' );
        }
        if ( $view_type == 'filter' && $gs_filter_by == 'rats' ) {
            $classes[] = 'gs-star-' . $filter_rating;
        }
        ?>
				
				<div class="<?php 
        echo esc_attr( implode( ' ', $classes ) );
        ?>">

					<div class="testimonial-box has-shadow">

						<!-- Testimonial Title -->
							<h3 class="box-tm-title  <?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_title', $card_visibility_settings ) );
        ?>"><?php 
        echo esc_html( $testi_title );
        ?></h3>

						<!-- Testimonial Content -->
						<div class="<?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_description', $card_visibility_settings ) );
        ?>">
							<?php 
        include Template_Loader::locate_template( 'partials/gs-layout-content.php' );
        ?>
						</div>


						<!-- Rating -->
						<?php 
        if ( !empty( $rating ) ) {
            ?>
							<div class="<?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_ratings', $card_visibility_settings ) );
            ?>">
							<?php 
            $args = array(
                'rating' => $rating,
                'type'   => 'rating',
                'number' => '',
                'echo'   => true,
            );
            gs_wp_star_rating( $args, $shortcode_settings['gs_ratings_icon'] );
            ?>
							</div>
					<?php 
        }
        ?>

						<!-- Testimonial Author info -->
						
					</div> <!-- End of testimonial-box -->

					<div class="testimonial-author-info">

						<!-- Testimonial Image -->
						<?php 
        if ( has_post_thumbnail() ) {
            ?>
							<div class="box-image <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_image', $card_visibility_settings ) );
            ?>"><?php 
            the_post_thumbnail( $shortcode_settings['imageSize'] );
            ?></div>
						<?php 
        }
        ?>

						<div class="gs-tai-client">

							<!-- Testimonial Name -->
							<h4 class="box-client-name <?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_name', $card_visibility_settings ) );
        ?>"><?php 
        echo esc_html( $client_name );
        ?></h4>

							<!-- Client Designation -->
							<?php 
        if ( !empty( $designation ) ) {
            ?>
								<div class="box-desiginfo">
									<span class="box-design-name <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_designation', $card_visibility_settings ) );
            ?>"><?php 
            echo esc_html( $designation );
            ?></span>
								</div>
							<?php 
        }
        ?>

							<!-- Client Company -->
							<?php 
        ?>

							<!-- Client Phone -->
							<?php 
        if ( !empty( $client_phone ) ) {
            ?>
								<div class="gs-tai-contact">
									<a class="gstm-phone" href="tel:<?php 
            echo esc_attr( $client_phone );
            ?>"><i class="fas fa-phone"></i><?php 
            echo esc_html( $client_phone );
            ?></a>
								</div>
							<?php 
        }
        ?>

						</div>

					</div> <!-- End of testimonial-author-info -->

					<?php 
        // if ( gstm_fs()->can_use_premium_code__premium_only() ) {
        include Template_Loader::locate_template( 'partials/gs-popup-content.php' );
        // }
        ?>

				</div> <!-- End of gs_testimonial_single -->

			<?php 
    }
    wp_reset_postdata();
    ?>
		</div>
	<?php 
} else {
    echo esc_html__( 'No Testimonial Added!', 'gs-testimonial' );
}
wp_reset_query();
?>
</div><?php 