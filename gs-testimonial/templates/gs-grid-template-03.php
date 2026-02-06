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
        $classes = ["gs_testimonial_single gs-col-lg-{$desktop_columns} gs-col-md-{$tablet_columns} gs-col-sm-{$columns_mobile} gs-col-xs-{$columns_small_mobile}"];
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
        if ( $view_type == 'filter' ) {
            $classes[] = get_terms_slugs();
        }
        ?>
				
				<div class="<?php 
        echo esc_attr( implode( ' ', $classes ) );
        ?>">

					<div class="testimonial-box">
						
						<!-- Company Image -->
						<?php 
        ?>

						<!-- Testimonial Title -->
						<?php 
        if ( $testi_title ) {
            ?>
							<h3 class="box-tm-title <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_title', $card_visibility_settings ) );
            ?>"><?php 
            echo esc_html( $testi_title );
            ?></h3>
						<?php 
        }
        ?>

						<!-- Content -->
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
            wp_star_rating( [
                'rating' => $rating,
                'type'   => 'rating',
                'echo'   => true,
            ] );
            ?>
							</div>
						<?php 
        }
        ?>

						<div class="testimonial-author-info">

							<!-- Reviewer Image -->
							<?php 
        if ( has_post_thumbnail() ) {
            ?>
								<div class="box-image <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_image', $card_visibility_settings ) );
            ?>">
									<?php 
            the_post_thumbnail( $shortcode_settings['imageSize'] );
            ?>
								</div>
							<?php 
        }
        ?>

							<div class="gs-tai-client">

								<!-- Testimonial Name -->
								<h4 class="box-client-name <?php 
        echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_name', $card_visibility_settings ) );
        ?>">
									<?php 
        echo esc_html( $client_name );
        ?>
								</h4>
								
								<!-- Client Designation -->
								<?php 
        if ( $designatin ) {
            ?>
									<div class="box-desiginfo <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_reviewer_designation', $card_visibility_settings ) );
            ?>">
										<span class="box-design-name"><?php 
            echo esc_html( $designatin );
            ?></span>
									</div>
								<?php 
        }
        ?>

								<!-- Client Company -->
								<?php 
        if ( gstm_fs()->can_use_premium_code__premium_only() && $company ) {
            ?>
									<div class="box-companyinfo <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_company_name', $card_visibility_settings ) );
            ?>">
										<span class="box-company-name"><?php 
            echo esc_html( $company );
            ?></span>
									</div>
								<?php 
        }
        ?>
							
								<?php 
        if ( !empty( $client_email ) ) {
            ?>
									<div class="gs-tai-email <?php 
            echo esc_attr( gstm_get_visibility_class( 'gstm_card_email', $card_visibility_settings ) );
            ?>">
										<a href="mailto:<?php 
            echo esc_attr( $client_email );
            ?>"><?php 
            echo esc_html( $client_email );
            ?></a>
									</div>
								<?php 
        }
        ?>

							</div>

						</div> <!-- End of testimonial-author-info -->
						
					</div> <!-- End of testimonial-box -->

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